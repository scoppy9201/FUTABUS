<?php

declare(strict_types=1);

namespace Tests\Feature\RouteManagement;

use FuteBus\RouteManagement\Services\RouteService;
use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RouteManagementTest extends TestCase
{
    use RefreshDatabase;

    private int $companyId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyId = DB::table('bus_companies')->insertGetId(['name' => 'FUTA Bus Lines', 'code' => 'FUTA']);
        $admin = $this->createTestUser();
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $admin->id]);
        $this->actingAs($admin);
    }

    public function test_route_and_stop_screens_render_shared_panels_and_only_owned_routes(): void
    {
        $id = $this->readyRoute();
        $other = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $foreign = DB::table('routes')->insertGetId($this->payload('FOREIGN') + ['name' => 'Foreign', 'bus_company_id' => $other]);

        $this->get(route('route-management.index'))->assertOk()->assertSee('HCM-DL')->assertDontSee('FOREIGN')->assertSee('route-form-panel');
        $this->get(route('route-management.stops.index', ['route_id' => $id]))->assertOk()->assertSee('stop-form-panel')->assertSee('Departure');
        $this->get(route('route-management.stops.index', ['route_id' => $foreign]))->assertNotFound();
        $this->put(route('route-management.update', $foreign), $this->payload('FOREIGN'))->assertNotFound();

        $this->actingAs($this->createTestUser())->get(route('route-management.index'))->assertForbidden();
        $this->post(route('route-management.store'), $this->payload())->assertForbidden();
    }

    public function test_operator_can_create_route_stops_and_schedule_then_sync_arrival(): void
    {
        $this->post(route('route-management.store'), $this->payload('hcm-dl'))->assertRedirect();
        $id = (int) DB::table('routes')->where('code', 'HCM-DL')->value('id');
        $this->assertDatabaseHas('routes', ['id' => $id, 'is_public_schedule' => false]);
        foreach ([$this->stopData('Departure', 1, 0, 'pickup'), $this->stopData('Arrival', 2, 240, 'dropoff')] as $data) {
            $this->post(route('route-management.stops.store', $id), $data)->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertDatabaseHas('routes', ['id' => $id, 'duration_minutes' => 240, 'is_public_schedule' => true,
            'origin_station'                     => 'Departure', 'destination_station' => 'Arrival']);
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertRedirect()->assertSessionHasNoErrors();
        $trip = DB::table('trips')->where('route_id', $id)->sole();
        $this->assertSame(today()->addDay()->setTime(12, 0)->toDateTimeString(), $trip->arrival_time);
        $stop = DB::table('route_stops')->where('route_id', $id)->where('stop_order', 2)->sole();
        $this->put(route('route-management.stops.update', [$id, $stop->id]), $this->stopData('Arrival', 2, 300, 'dropoff'))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('trip_schedules', ['route_id' => $id, 'duration_minutes' => 300]);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'arrival_time' => today()->addDay()->setTime(13, 0)->toDateTimeString()]);
        $this->assertSame(0, app(TripGenerator::class)->generateAll());
        $this->assertSame(1, DB::table('trips')->where('route_id', $id)->count());
    }

    public function test_vehicle_overlap_rolls_back_stops_route_and_arrival_changes(): void
    {
        $id = $this->readyRoute();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasNoErrors();
        $busId = DB::table('buses')->insertGetId(['bus_company_id' => $this->companyId, 'license_plate' => '51H-123.45', 'capacity' => 34]);
        $trip = DB::table('trips')->where('route_id', $id)->sole();
        DB::table('trips')->where('id', $trip->id)->update(['bus_id' => $busId, 'status' => 'scheduled']);
        DB::table('trips')->insert([
            'route_id'       => $id, 'bus_company_id' => $this->companyId, 'bus_id' => $busId, 'price' => 300000, 'status' => 'scheduled',
            'departure_time' => today()->addDay()->setTime(12, 30), 'arrival_time' => today()->addDay()->setTime(16, 30),
        ]);
        $stop = DB::table('route_stops')->where('route_id', $id)->where('stop_order', 2)->sole();
        $this->put(route('route-management.stops.update', [$id, $stop->id]), $this->stopData('Arrival', 2, 300, 'dropoff'))
            ->assertSessionHasErrors('leg_minutes');
        $this->assertDatabaseHas('route_stops', ['id' => $stop->id, 'leg_minutes' => 240]);
        $this->assertDatabaseHas('routes', ['id' => $id, 'duration_minutes' => 240]);
        $this->assertDatabaseHas('trip_schedules', ['route_id' => $id, 'duration_minutes' => 240]);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'arrival_time' => $trip->arrival_time]);
    }

    public function test_secondary_payment_leg_prevents_itinerary_mutation(): void
    {
        $firstId = $this->readyRoute();
        $secondId = $this->readyRoute('DL-HCM');
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($firstId))->assertSessionHasNoErrors();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($secondId))->assertSessionHasNoErrors();
        DB::table('sepay_payment_intents')->insert([
            'token'             => (string) Str::uuid(), 'code' => 'TESTINTENT1',
            'trip_id'           => DB::table('trips')->where('route_id', $firstId)->value('id'),
            'secondary_trip_id' => DB::table('trips')->where('route_id', $secondId)->value('id'),
            'seat_ids'          => '[]', 'snapshot' => '{}', 'amount' => 600000, 'status' => 'pending', 'expires_at' => now()->addMinutes(15),
        ]);
        $stop = DB::table('route_stops')->where('route_id', $secondId)->where('stop_order', 2)->sole();
        $this->put(route('route-management.stops.update', [$secondId, $stop->id]), $this->stopData('Changed', 2, 300, 'dropoff'))
            ->assertSessionHasErrors('route');
        $this->assertDatabaseHas('route_stops', ['id' => $stop->id, 'name' => 'Arrival', 'leg_minutes' => 240]);
    }

    public function test_cannot_remove_minimum_stops_deactivate_or_delete_an_operating_route(): void
    {
        $id = $this->readyRoute();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasNoErrors();
        $stop = DB::table('route_stops')->where('route_id', $id)->where('stop_order', 2)->sole();
        $this->delete(route('route-management.stops.destroy', [$id, $stop->id]))->assertSessionHasErrors('stop');
        $this->put(route('route-management.update', $id), $this->payload())
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->put(route('route-management.update', $id), array_replace($this->payload(), ['is_active' => false]))->assertSessionHasErrors('route');
        $this->delete(route('route-management.destroy', $id))->assertSessionHasErrors('route');
        $this->assertDatabaseHas('routes', ['id' => $id, 'is_active' => true]);
    }

    public function test_stop_validation_order_and_nested_ownership(): void
    {
        $id = $this->readyRoute();
        $otherId = $this->readyRoute('OTHER');
        $otherStop = DB::table('route_stops')->where('route_id', $otherId)->first();
        $this->put(route('route-management.stops.update', [$id, $otherStop->id]), $this->stopData('Wrong', 1, 0, 'pickup'))->assertNotFound();
        $this->post(route('route-management.stops.store', $id), $this->stopData('Invalid', 3, 0, 'both'))->assertSessionHasErrors('leg_minutes');
        $this->post(route('route-management.stops.store', $id), $this->stopData('Invalid', 3, 30, 'pickup'))->assertSessionHasErrors('stop_type');
        $this->post(route('route-management.stops.store', $id), $this->stopData('Intermediate', 2, 60, 'both'))->assertSessionHasNoErrors();
        $inserted = DB::table('route_stops')->where('route_id', $id)->where('name', 'Intermediate')->sole();
        $this->assertDatabaseHas('route_stops', ['id' => $inserted->id, 'stop_order' => 2, 'offset_minutes' => 60]);
        $this->assertDatabaseHas('route_stops', ['route_id' => $id, 'name' => 'Arrival', 'stop_order' => 3, 'offset_minutes' => 300]);
        $this->delete(route('route-management.stops.destroy', [$id, $inserted->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('route_stops', ['route_id' => $id, 'name' => 'Arrival', 'stop_order' => 2, 'offset_minutes' => 240]);
        $this->assertDatabaseHas('routes', ['id' => $id, 'duration_minutes' => 240]);
    }

    public function test_inactive_and_foreign_routes_cannot_generate_or_receive_new_schedules(): void
    {
        $id = $this->readyRoute();
        $this->put(route('route-management.update', $id), array_replace($this->payload(), ['is_active' => false]))->assertSessionHasNoErrors();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasErrors('route_id');
        $this->assertSame(0, app(TripGenerator::class)->generate((object) ($this->scheduleData($id) + ['id' => 100, 'status' => 'active'])));
        $this->delete(route('route-management.destroy', $id))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('route_stops', ['route_id' => $id]);
        $foreignId = $this->readyRoute('FOREIGN');
        $otherCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        DB::table('routes')->where('id', $foreignId)->update(['bus_company_id' => $otherCompany]);
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($foreignId))->assertSessionHasErrors('route_id');
        $this->assertSame(0, app(TripGenerator::class)->generate((object) ($this->scheduleData($foreignId) + ['id' => 100, 'status' => 'active'])));
    }

    public function test_booked_itinerary_is_immutable_and_base_fare_does_not_reprice_existing_trips(): void
    {
        $id = $this->readyRoute();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasNoErrors();
        $tripId = DB::table('trips')->where('route_id', $id)->value('id');
        $customerId = DB::table('customers')->insertGetId(['full_name' => 'Test customer', 'phone' => '0901234567']);
        DB::table('bookings')->insert([
            'booking_code' => 'ROUTEBOOK1', 'trip_id' => $tripId, 'customer_id' => $customerId,
            'seat_count'   => 1, 'total_amount' => 300000, 'status' => 'confirmed',
        ]);
        $stop = DB::table('route_stops')->where('route_id', $id)->where('stop_order', 2)->sole();
        $this->put(route('route-management.stops.update', [$id, $stop->id]), $this->stopData('Changed', 2, 300, 'dropoff'))
            ->assertSessionHasErrors('route');
        $this->put(route('route-management.update', $id), array_replace($this->payload(), ['destination_city' => 'Nha Trang']))
            ->assertSessionHasErrors('route');
        $this->put(route('route-management.update', $id), array_replace($this->payload(), ['base_price' => 350000]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('trips', ['id' => $tripId, 'price' => 300000]);
        $this->assertDatabaseHas('bookings', ['trip_id' => $tripId, 'total_amount' => 300000]);
        $this->assertDatabaseHas('route_stops', ['id' => $stop->id, 'name' => 'Arrival']);
    }

    public function test_admin_edits_are_reflected_in_the_existing_customer_schedule_page_and_api(): void
    {
        $id = $this->readyRoute();
        $this->put(route('route-management.update', $id), array_replace($this->payload(), [
            'origin_city' => 'TP. Hồ Chí Minh', 'distance_km' => 320,
        ]))->assertSessionHasNoErrors();
        $stop = DB::table('route_stops')->where('route_id', $id)->where('stop_order', 2)->sole();
        $this->put(route('route-management.stops.update', [$id, $stop->id]), $this->stopData('Arrival', 2, 300, 'dropoff'))
            ->assertSessionHasNoErrors();
        $this->getJson(route('api.v1.schedules.index'))->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.origin', 'TP. Hồ Chí Minh')
            ->assertJsonPath('data.0.distance_km', 320)->assertJsonPath('data.0.duration_minutes', 300);
        $page = $this->get(route('schedules'))->assertOk();
        $catalog = $page->viewData('scheduleGroups')->flatten(1)->sole();
        $this->assertSame('TP. Hồ Chí Minh', $catalog['from']);
        $this->assertSame(320, $catalog['distance']);
        $this->assertSame(5.0, $catalog['hours']);
        $this->assertSame(1, DB::table('routes')->count());

        $foreignCompany = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        DB::table('routes')->insert($this->payload('FOREIGN') + [
            'bus_company_id' => $foreignCompany, 'name' => 'Foreign', 'is_public_schedule' => true,
        ]);
        $this->getJson(route('api.v1.schedules.index'))->assertJsonCount(1, 'data');
        $this->put(route('route-management.update', $id), array_replace($this->payload(), ['is_active' => false]))
            ->assertSessionHasNoErrors();
        $this->getJson(route('api.v1.schedules.index'))->assertJsonPath('data', []);
    }

    public function test_new_first_stop_requires_an_explicit_travel_time_to_the_previous_origin(): void
    {
        $id = $this->readyRoute();
        $this->post(route('route-management.stops.store', $id), $this->stopData('New origin', 1, 0, 'pickup'))
            ->assertSessionHasErrors('next_leg_minutes');
        $this->post(route('route-management.stops.store', $id), $this->stopData('New origin', 1, 0, 'pickup') + ['next_leg_minutes' => 30])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('route_stops', ['route_id' => $id, 'name' => 'Departure', 'stop_order' => 2, 'leg_minutes' => 30, 'offset_minutes' => 30]);
        $this->assertDatabaseHas('route_stops', ['route_id' => $id, 'name' => 'Arrival', 'stop_order' => 3, 'offset_minutes' => 270]);
        $this->assertDatabaseHas('routes', ['id' => $id, 'origin_station' => 'New origin', 'duration_minutes' => 270]);
        $this->post(route('route-management.stops.store', $id), $this->stopData('Invalid order', 99, 60, 'both'))
            ->assertSessionHasErrors('position');
        $this->assertSame(3, DB::table('route_stops')->where('route_id', $id)->count());
    }

    public function test_schedule_time_change_cannot_overlap_an_assigned_vehicle(): void
    {
        $id = $this->readyRoute();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasNoErrors();
        $scheduleId = DB::table('trip_schedules')->where('route_id', $id)->value('id');
        $trip = DB::table('trips')->where('trip_schedule_id', $scheduleId)->sole();
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $this->companyId, 'license_plate' => '51H-123.45', 'capacity' => 34, 'bus_type' => 'limousine',
        ]);
        DB::table('trips')->where('id', $trip->id)->update(['bus_id' => $busId, 'status' => 'scheduled']);
        DB::table('trips')->insert([
            'route_id'       => $id, 'bus_company_id' => $this->companyId, 'bus_id' => $busId, 'price' => 300000, 'status' => 'scheduled',
            'departure_time' => today()->addDay()->setTime(12, 30), 'arrival_time' => today()->addDay()->setTime(16, 30),
        ]);
        $this->put(route('trip-management.schedules.update', $scheduleId), array_replace($this->scheduleData($id), ['departure_time' => '09:00']))
            ->assertSessionHasErrors('schedule');
        $this->assertDatabaseHas('trip_schedules', ['id' => $scheduleId, 'departure_time' => '08:00']);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'departure_time' => $trip->departure_time, 'arrival_time' => $trip->arrival_time]);
    }

    public function test_regeneration_does_not_create_an_extra_departure_on_a_protected_booking_date(): void
    {
        $id = $this->readyRoute();
        $this->post(route('trip-management.schedules.store'), $this->scheduleData($id))->assertSessionHasNoErrors();
        $scheduleId = DB::table('trip_schedules')->where('route_id', $id)->value('id');
        $trip = DB::table('trips')->where('trip_schedule_id', $scheduleId)->sole();
        $customerId = DB::table('customers')->insertGetId(['full_name' => 'Test customer', 'phone' => '0901234567']);
        DB::table('bookings')->insert([
            'booking_code' => 'PROTECTED1', 'trip_id' => $trip->id, 'customer_id' => $customerId,
            'seat_count'   => 1, 'total_amount' => 300000, 'status' => 'confirmed',
        ]);
        $this->put(route('trip-management.schedules.update', $scheduleId), array_replace($this->scheduleData($id), ['departure_time' => '09:00']))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('trips')->where('trip_schedule_id', $scheduleId)->count());
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'departure_time' => $trip->departure_time]);
        $this->assertSame(0, app(TripGenerator::class)->generateAll());
    }

    private function readyRoute(string $code = 'HCM-DL'): int
    {
        $service = app(RouteService::class);
        $id = $service->save($this->payload($code));
        $service->saveStop($id, $this->stopData('Departure', 1, 0, 'pickup'));
        $service->saveStop($id, $this->stopData('Arrival', 2, 240, 'dropoff'));

        return $id;
    }

    private function payload(string $code = 'HCM-DL'): array
    {
        return ['code'    => $code, 'origin_city' => 'Hồ Chí Minh', 'destination_city' => 'Đà Lạt',
            'distance_km' => 310, 'base_price' => 300000, 'vehicle_type' => 'Limousine', 'is_active' => true];
    }

    private function stopData(string $name, int $position, int $minutes, string $type): array
    {
        return ['name' => $name, 'address' => '01 Tô Hiến Thành, Đà Lạt',
            'position' => $position, 'leg_minutes' => $minutes, 'stop_type' => $type];
    }

    private function scheduleData(int $id): array
    {
        return ['route_id' => $id, 'departure_time' => '08:00', 'days_of_week' => range(1, 7),
            'start_date'   => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString(), 'price' => 300000];
    }
}

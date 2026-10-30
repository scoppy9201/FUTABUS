<?php

namespace Tests\Feature\BusManagement;

use App\Models\User;
use FuteBus\BusManagement\Models\Bus;
use FuteBus\BusManagement\Services\BusPresentationService;
use FuteBus\TripSearch\Services\TripSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusPresentationTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $admin = User::factory()->create(['is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $admin->id]);
        $company = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        $type = DB::table('vehicle_types')->insertGetId([
            'name' => 'Limousine 34', 'vehicle_class' => 'limousine_sleeper', 'floor_count' => 2, 'default_capacity' => 34,
        ]);
        $bus = Bus::create(['bus_company_id' => $company, 'vehicle_type_id' => $type, 'license_plate' => '51H-331.23',
            'capacity'                       => 34, 'bus_type' => 'limousine', 'brand' => 'THACO', 'color' => 'red', 'manufacture_year' => 2025,
            'chassis_number'                 => 'PRIVATE-CHASSIS', 'engine_number' => 'PRIVATE-ENGINE']);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => 'HCM-DL', 'name' => 'HCM - DL',
            'origin_city'                                           => 'Hồ Chí Minh', 'destination_city' => 'Đà Lạt', 'base_price' => 5000, 'is_active' => true]);
        $departure = now()->addDays(2)->setTime(22, 0);
        $trip = DB::table('trips')->insertGetId(['bus_id' => $bus->id, 'route_id' => $route, 'bus_company_id' => $company,
            'departure_time'                              => $departure, 'arrival_time' => $departure->copy()->addHours(8), 'status' => 'scheduled', 'price' => 5000]);
        DB::table('seat_layouts')->insert(['bus_id' => $bus->id, 'seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1,
            'deck'                                  => 'lower', 'is_available' => true]);
        $criteria = ['departure' => 'Hồ Chí Minh', 'destination' => 'Đà Lạt', 'departure_date' => $departure->toDateString(),
            'trip_type'          => 'one_way', 'quantity' => 1];

        return [$admin, $bus, $trip, $criteria];
    }

    public function test_admin_updates_assigned_bus_amenities_and_customer_reads_that_bus_without_changing_trip_or_seats(): void
    {
        [$admin, $bus, $trip, $criteria] = $this->fixture();
        $beforeTrip = DB::table('trips')->find($trip);
        $beforeSeat = DB::table('seat_layouts')->where('bus_id', $bus->id)->first();
        $this->actingAs($admin)->put(route('bus-management.buses.presentation.update', $bus->id), [
            'amenities' => BusPresentationService::AMENITIES, 'wifi_name' => 'FUTA', 'wifi_password' => '19006067',
        ])->assertRedirect(route('bus-management.buses.index'));
        $this->assertEquals($beforeTrip, DB::table('trips')->find($trip));
        $this->assertEquals($beforeSeat, DB::table('seat_layouts')->where('bus_id', $bus->id)->first());
        $found = app(TripSearchService::class)->search($criteria['departure'], $criteria['destination'], $criteria['departure_date'], 1)[0];
        $this->assertSame($bus->license_plate, $found['vehicle_details']['license_plate']);
        $this->assertSame(BusPresentationService::AMENITIES, $found['vehicle_details']['amenities']);
        $this->get(route('bus-management.buses.index'))->assertOk()->assertSee('bus-presentation-panel', false);
        $this->get(route('trip-booking.show', ['trip' => $trip, ...$criteria]))->assertOk()
            ->assertSee('THACO')->assertSee('Chăn đắp')->assertSee('Nước uống')->assertSee('Khăn ướt')->assertSee('Cổng sạc')
            ->assertSee('19006067')->assertDontSee('PRIVATE-CHASSIS')->assertDontSee('PRIVATE-ENGINE')->assertDontSee('Hình ảnh/Video');
        $this->getJson(route('api.v1.trips.show', ['trip' => $trip, ...$criteria, 'direction' => 'outbound']))
            ->assertOk()->assertJsonPath('data.vehicle_details.wifi_password', '19006067')
            ->assertJsonMissingPath('data.vehicle_details.chassis_number');
    }

    public function test_presentation_checks_admin_company_and_amenity_values(): void
    {
        [$admin, $bus] = $this->fixture();
        $url = route('bus-management.buses.presentation.update', $bus->id);
        $this->actingAs(User::factory()->create())->put($url, ['amenities' => ['wifi']])->assertForbidden();
        $this->actingAs($admin)->put($url, ['amenities' => ['invented']])->assertSessionHasErrors('amenities.0');
        $otherCompany = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $bus->update(['bus_company_id' => $otherCompany]);
        $this->put($url, ['amenities' => ['wifi']])->assertNotFound();
    }

    public function test_admin_api_updates_only_public_configuration(): void
    {
        [$admin, $bus] = $this->fixture();
        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->putJson(route('api.v1.admin.buses.presentation.update', $bus->id), [
                'amenities' => ['water', 'wifi'], 'wifi_password' => '19006067',
                'capacity'  => 1, 'chassis_number' => 'INJECTED',
            ])->assertOk()->assertJsonPath('data.amenities', ['water', 'wifi']);
        $this->assertSame(34, $bus->fresh()->capacity);
        $this->assertSame('PRIVATE-CHASSIS', $bus->fresh()->chassis_number);
    }

    public function test_omitted_checkboxes_clear_amenities_and_unpublished_wifi(): void
    {
        [$admin, $bus] = $this->fixture();
        $bus->update(['amenities' => ['wifi'], 'wifi_name' => 'FUTA', 'wifi_password' => '19006067']);
        $this->actingAs($admin)->put(route('bus-management.buses.presentation.update', $bus->id), [])
            ->assertRedirect();
        $this->assertSame([], $bus->fresh()->amenities);
        $this->assertNull($bus->fresh()->wifi_password);
        $this->assertNull(BusPresentationService::publicData($bus->fresh())['wifi_name']);
    }
}

<?php

namespace Tests\Feature\StaffTicketProcessing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketListTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_list_loads_real_company_tickets_and_filters_and_paginates(): void
    {
        $company = DB::table('bus_companies')->insertGetId(['name' => 'Company', 'code' => 'FUTA']);
        $other = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true, 'email_verified_at' => now()]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff', 'bus_company_id' => $company]);
        $permission = DB::table('permissions')->insertGetId(['name' => 'View tickets', 'slug' => 'ticket.view']);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        $trip = $this->trip($company, '51B-001');
        $otherTrip = $this->trip($other, '51B-002');
        $customer = DB::table('customers')->insertGetId(['full_name' => 'Customer', 'phone' => '0912345678']);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => 'BOOKING', 'trip_id' => $trip, 'customer_id' => $customer, 'seat_count' => 16, 'total_amount' => 4800000, 'status' => 'confirmed']);
        for ($i = 1; $i <= 16; $i++) {
            DB::table('tickets')->insert(['booking_id' => $booking, 'trip_id' => $trip, 'ticket_code' => 'TICKET-'.$i,
                'passenger_name'                       => 'Passenger '.$i, 'passenger_phone' => '0912345678', 'seat_code' => 'A'.$i, 'price' => 300000, 'status' => $i === 16 ? 'used' : 'active']);
        }
        $foreignBooking = DB::table('bookings')->insertGetId(['booking_code' => 'OTHER-BOOKING', 'trip_id' => $otherTrip, 'customer_id' => $customer, 'seat_count' => 1, 'total_amount' => 300000]);
        DB::table('tickets')->insert(['booking_id' => $foreignBooking, 'trip_id' => $otherTrip, 'ticket_code' => 'FOREIGN', 'passenger_name' => 'Foreign passenger', 'price' => 300000]);
        $this->actingAs($staff)->get(route('staff.tickets'))->assertOk()->assertSeeText('Danh sách vé');
        $list = $this->getJson(route('api.v1.staff.tickets.index'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonCount(15, 'data')->assertJsonPath('meta.total', 16)->assertJsonPath('data.0.code', 'TICKET-16')->assertJsonMissing(['code' => 'FOREIGN']);
        $this->assertSame('Đã sử dụng', $list->json('data.0.status_label'));
        $this->getJson(route('api.v1.staff.tickets.index', ['page' => 2]))->assertOk()->assertJsonCount(1, 'data');
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'TICKET-16', 'phone' => '0912345678', 'name' => 'Passenger 16', 'date' => now()->addDay()->toDateString(), 'status' => 'used']))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'TICKET-16');
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'BOOKING']))->assertOk()->assertJsonPath('meta.total', 16);
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'FOREIGN']))->assertOk()->assertJsonCount(0, 'data');
        $this->getJson(route('api.v1.staff.tickets.index', ['status' => 'paid']))->assertUnprocessable();
        $this->getJson(route('api.v1.staff.tickets.index', ['date' => 'invalid']))->assertUnprocessable();
        $departure = Carbon::parse(DB::table('trips')->where('id', $trip)->value('departure_time'));
        $this->travelTo($departure->copy()->subSecond());
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'TICKET-1']))->assertOk()
            ->assertJsonPath('data.0.usable', true)->assertJsonPath('data.0.display_status', 'active');
        $this->travelTo($departure);
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'TICKET-1']))->assertOk()
            ->assertJsonPath('data.0.usable', false)->assertJsonPath('data.0.display_status', 'expired')
            ->assertJsonPath('data.0.status_label', 'Đã quá giờ khởi hành');
        $this->getJson(route('api.v1.staff.tickets.index', ['status' => 'active']))->assertOk()->assertJsonCount(0, 'data');
        $this->getJson(route('api.v1.staff.tickets.index', ['status' => 'expired']))->assertOk()->assertJsonPath('meta.total', 15);
        $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'TICKET-16']))->assertOk()
            ->assertJsonPath('data.0.display_status', 'used')->assertJsonPath('data.0.usable', false);
        foreach (['cancelled', 'refunded'] as $status) {
            DB::table('tickets')->where('ticket_code', 'TICKET-1')->update(['status' => $status]);
            $this->getJson(route('api.v1.staff.tickets.index', ['code' => 'TICKET-1']))->assertOk()
                ->assertJsonPath('data.0.display_status', $status)->assertJsonPath('data.0.usable', false);
        }
        $this->assertDatabaseHas('tickets', ['ticket_code' => 'TICKET-2', 'status' => 'active']);
        $this->travelBack();
    }

    public function test_ticket_list_requires_verified_operator_permission_and_company(): void
    {
        $company = DB::table('bus_companies')->insertGetId(['name' => 'Company', 'code' => 'FUTA']);
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true, 'email_verified_at' => now()]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff', 'bus_company_id' => $company]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        $this->getJson(route('api.v1.staff.tickets.index'))->assertUnauthorized();
        $this->actingAs($staff)->getJson(route('api.v1.staff.tickets.index'))->assertForbidden();
        $permission = DB::table('permissions')->insertGetId(['name' => 'View tickets', 'slug' => 'ticket.view']);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        $staff->update(['bus_company_id' => null]);
        $this->getJson(route('api.v1.staff.tickets.index'))->assertForbidden();
        $customer = $this->createTestUser(['is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($customer)->getJson(route('api.v1.staff.tickets.index'))->assertForbidden();
    }

    private function trip(int $company, string $plate): int
    {
        $bus = DB::table('buses')->insertGetId(['bus_company_id' => $company, 'license_plate' => $plate, 'capacity' => 34, 'bus_type' => 'sleeper']);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => $plate, 'name' => 'Route', 'origin_city' => 'Hà Nội', 'destination_city' => 'Hồ Chí Minh', 'distance_km' => 1600, 'base_price' => 300000]);

        return DB::table('trips')->insertGetId(['route_id' => $route, 'bus_id' => $bus, 'bus_company_id' => $company,
            'departure_time'                               => now()->addDay()->setTime(8, 0), 'arrival_time' => now()->addDays(2)->setTime(8, 0), 'price' => 300000, 'available_seats' => 34]);
    }
}

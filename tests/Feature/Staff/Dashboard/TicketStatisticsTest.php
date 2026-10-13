<?php

namespace Tests\Feature\Staff\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Staff\Dashboard\Services\StaffDashboardService;
use Tests\TestCase;

class TicketStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_issued_tickets_today_within_authorized_company(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 13)->setTime(9, 48));
        $company = DB::table('bus_companies')->insertGetId(['name' => 'Company', 'code' => 'FUTA']);
        $other = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff', 'bus_company_id' => $company]);
        $permission = DB::table('permissions')->insertGetId(['name' => 'Sell tickets', 'slug' => 'booking.create']);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);

        $booking = $this->booking($company, 'CURRENT');
        $foreign = $this->booking($other, 'FOREIGN');
        $pending = $this->booking($company, 'PENDING', 'pending');
        $this->ticket($booking, 'MIDNIGHT', now()->startOfDay());
        $this->ticket($booking, 'TODAY', now());
        $this->ticket($booking, 'USED', now(), 'used');
        $this->ticket($booking, 'YESTERDAY', now()->startOfDay()->subSecond());
        $this->ticket($booking, 'TOMORROW', now()->addDay()->startOfDay());
        $this->ticket($booking, 'CANCELLED', now(), 'cancelled');
        $this->ticket($booking, 'REFUNDED', now(), 'refunded');
        $this->ticket($foreign, 'OTHER-COMPANY', now());
        $this->ticket($pending, 'UNCONFIRMED', now());

        $this->actingAs($staff)->getJson(route('api.v1.staff.dashboard.show'))
            ->assertOk()->assertJsonPath('data.statistics.tickets_today', 3);

        DB::table('permission_role')->where('role_id', $role)->delete();
        $this->assertNull(app(StaffDashboardService::class)->data($staff->fresh())['statistics']['tickets_today']);
        $this->travelBack();
    }

    private function booking(int $company, string $code, string $status = 'confirmed'): int
    {
        $bus = DB::table('buses')->insertGetId([
            'bus_company_id' => $company, 'license_plate' => $code, 'capacity' => 10, 'bus_type' => 'sleeper',
        ]);
        $route = DB::table('routes')->insertGetId([
            'bus_company_id' => $company, 'code' => $code, 'name' => 'Route',
            'origin_city'    => 'Hà Nội', 'destination_city' => 'Hồ Chí Minh', 'base_price' => 300000,
        ]);
        $trip = DB::table('trips')->insertGetId([
            'bus_company_id' => $company, 'bus_id' => $bus, 'route_id' => $route,
            'departure_time' => now()->addDays(2), 'arrival_time' => now()->addDays(3),
            'price'          => 300000, 'status' => 'scheduled', 'available_seats' => 10,
        ]);
        $customer = DB::table('customers')->where('phone', '0912345678')->value('id')
            ?? DB::table('customers')->insertGetId(['full_name' => 'Customer', 'phone' => '0912345678']);

        return DB::table('bookings')->insertGetId([
            'booking_code' => $code, 'trip_id' => $trip, 'customer_id' => $customer,
            'seat_count'   => 10, 'total_amount' => 3000000, 'status' => $status,
        ]);
    }

    private function ticket(int $booking, string $code, \DateTimeInterface $createdAt, string $status = 'active'): void
    {
        DB::table('tickets')->insert([
            'ticket_code'    => $code, 'booking_id' => $booking,
            'trip_id'        => DB::table('bookings')->where('id', $booking)->value('trip_id'),
            'passenger_name' => 'Passenger', 'price' => 300000, 'status' => $status,
            'created_at'     => $createdAt, 'updated_at' => $createdAt,
        ]);
    }
}

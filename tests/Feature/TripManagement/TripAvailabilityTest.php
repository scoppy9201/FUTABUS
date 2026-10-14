<?php

namespace Tests\Feature\TripManagement;

use App\Models\User;
use Database\Seeders\AdminAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TripAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_list_shows_remaining_seats_based_on_booked_seats(): void
    {
        config()->set('seed.admin.email', 'trip-admin@example.test');
        config()->set('seed.admin.password', 'strong-test-password-2026');
        $this->seed(AdminAccountSeeder::class);
        $admin = User::where('email', 'trip-admin@example.test')->sole();

        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA Bus Lines', 'code' => 'FUTA', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId, 'license_plate' => '51B-54321', 'capacity' => 45,
            'bus_type'       => 'sleeper', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id' => $companyId, 'code' => 'HCM-DL-TEST', 'name' => 'Ho Chi Minh - Da Lat',
            'origin_city'    => 'Ho Chi Minh', 'destination_city' => 'Da Lat', 'base_price' => 300000,
            'is_active'      => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $tripId = DB::table('trips')->insertGetId([
            'route_id'       => $routeId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
            'departure_time' => now()->addDay(), 'arrival_time' => now()->addDay()->addHours(8),
            'price'          => 300000, 'status' => 'scheduled', 'available_seats' => 45,
            'created_at'     => now(), 'updated_at' => now(),
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'full_name' => 'Booked Customer', 'phone' => '0901234567', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => 'TESTBOOKING1', 'trip_id' => $tripId, 'customer_id' => $customerId,
            'seat_count'   => 5, 'total_amount' => 1500000, 'status' => 'confirmed',
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        for ($seat = 1; $seat <= 5; $seat++) {
            $seatId = DB::table('seat_layouts')->insertGetId([
                'bus_id'        => $busId, 'seat_code' => 'A'.$seat, 'row_number' => 1,
                'column_number' => $seat, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('booked_seats')->insert([
                'booking_id' => $bookingId, 'trip_id' => $tripId, 'seat_layout_id' => $seatId,
                'seat_code'  => 'A'.$seat, 'price' => 300000, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('trip-management.trips.index'))
            ->assertOk()
            ->assertSee('40/45');
    }
}

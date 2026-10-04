<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Auth\RoleRedirector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_open_the_owner_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertForbidden();

        $admin = $this->admin();

        $this->assertSame('/dashboard', RoleRedirector::pathFor($admin));
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('Dashboard::app.overview'))
            ->assertSeeText(__('Dashboard::app.company_unavailable_hint'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false);
    }

    public function test_dashboard_lists_only_futa_operating_data(): void
    {
        $futaId = $this->company('FUTA');
        $otherId = $this->company('OTHER');
        $futaRoute = $this->routeFor($futaId, 'FUTA-TEST');
        $otherRoute = $this->routeFor($otherId, 'OTHER-TEST');
        $futaTrip = $this->tripFor($futaId, $futaRoute, 'FUTA-PLATE');
        $otherTrip = $this->tripFor($otherId, $otherRoute, 'OTHER-PLATE');
        $this->bookingFor($futaTrip, 'FUTA-BOOKING');
        $this->bookingFor($otherTrip, 'OTHER-BOOKING');

        $this->actingAs($this->admin())
            ->get(route('dashboard.section', 'routes'))
            ->assertOk()
            ->assertSeeText('FUTA-TEST')
            ->assertDontSeeText('OTHER-TEST');

        $this->get(route('dashboard.section', 'bookings'))
            ->assertOk()
            ->assertSeeText('FUTA-BOOKING')
            ->assertDontSeeText('OTHER-BOOKING');

        $this->get(route('dashboard.section', 'reports'))
            ->assertOk()
            ->assertSeeText('250.000 ₫');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('Dashboard::app.at_a_glance'))
            ->assertSeeText('FUTA-PLATE')
            ->assertDontSeeText('OTHER-PLATE');
    }

    public function test_all_owner_sections_render_with_empty_data(): void
    {
        $this->actingAs($this->admin());

        foreach (['trips', 'routes', 'buses', 'bookings', 'customers', 'reports'] as $section) {
            $this->get(route('dashboard.section', $section))
                ->assertOk()
                ->assertSeeText(__('Dashboard::app.'.$section));
        }
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);

        return $admin;
    }

    private function company(string $code): int
    {
        return DB::table('bus_companies')->insertGetId([
            'name' => $code,
            'code' => $code,
        ]);
    }

    private function routeFor(int $companyId, string $code): int
    {
        return DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => $code,
            'name'             => $code,
            'origin_city'      => 'Hồ Chí Minh',
            'destination_city' => 'Đà Lạt',
            'base_price'       => 250000,
        ]);
    }

    private function tripFor(int $companyId, int $routeId, string $plate): int
    {
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId,
            'license_plate'  => $plate,
        ]);

        return DB::table('trips')->insertGetId([
            'route_id'        => $routeId,
            'bus_id'          => $busId,
            'bus_company_id'  => $companyId,
            'departure_time'  => now()->addDay(),
            'arrival_time'    => now()->addDays(2),
            'price'           => 250000,
            'status'          => 'scheduled',
            'available_seats' => 42,
        ]);
    }

    private function bookingFor(int $tripId, string $code): void
    {
        $customerId = DB::table('customers')->insertGetId([
            'full_name' => $code,
            'phone'     => '0900'.str_pad((string) $tripId, 6, '0', STR_PAD_LEFT),
        ]);

        DB::table('bookings')->insert([
            'booking_code' => $code,
            'trip_id'      => $tripId,
            'customer_id'  => $customerId,
            'seat_count'   => 1,
            'total_amount' => 250000,
            'created_at'   => now(),
        ]);
    }
}

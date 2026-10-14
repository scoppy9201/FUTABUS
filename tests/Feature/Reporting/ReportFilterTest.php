<?php

namespace Tests\Feature\Reporting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    public function test_report_api_retains_new_types_periods_and_company_scoped_filters(): void
    {
        $this->travelTo(now('Asia/Ho_Chi_Minh')->startOfDay()->addHours(12));
        $company = $this->signInAdmin();
        $route = $this->makeRoute($company, 'OWN');
        $this->getJson(route('api.v1.reports.index', ['type' => 'trips', 'period' => 'today', 'route_id' => $route]))
            ->assertOk()->assertJsonPath('filters.type', 'trips')
            ->assertJsonPath('filters.route_id', (string) $route)
            ->assertJsonPath('filters.from', now('Asia/Ho_Chi_Minh')->toDateString());
        $this->getJson(route('api.v1.reports.index', ['period' => '30days']))->assertOk()
            ->assertJsonPath('filters.type', 'overview')
            ->assertJsonPath('filters.from', now('Asia/Ho_Chi_Minh')->subDays(29)->toDateString());
        $other = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $foreignRoute = $this->makeRoute($other, 'FOREIGN');
        $foreignBus = DB::table('buses')->insertGetId(['bus_company_id' => $other, 'license_plate' => 'OTHER-BUS']);
        $foreignTrip = DB::table('trips')->insertGetId([
            'bus_company_id' => $other, 'route_id' => $foreignRoute, 'bus_id' => $foreignBus,
            'departure_time' => now()->addDay(), 'arrival_time' => now()->addDays(2), 'price' => 350000,
        ]);
        foreach (['route_id' => $foreignRoute, 'trip_id' => $foreignTrip, 'bus_id' => $foreignBus] as $field => $id) {
            $this->getJson(route('api.v1.reports.index', [$field => $id]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_reporting_rejects_invalid_dates_and_denies_users_without_permission(): void
    {
        $this->signInAdmin();
        foreach ([['period' => 'invalid'], ['type' => 'invalid'], ['format' => 'csv'],
            ['from' => 'bad'], ['period' => 'custom', 'from' => '2026-10-20', 'to' => '2026-10-01']] as $input) {
            $this->getJson(route('api.v1.reports.index', $input))->assertUnprocessable();
        }
        $this->getJson(route('reporting.export', ['from' => '2026-10-20', 'to' => '2026-10-01']))
            ->assertUnprocessable()->assertJsonPath('message', __('Reporting::app.invalid_range'));
        $this->actingAs($this->admin)->get(route('reporting.index'))->assertOk();
        $customer = $this->createTestUser(['is_active' => true]);
        $this->app['auth']->forgetGuards();
        $this->withToken($customer->createToken('customer-report-test', ['api'])->plainTextToken)
            ->getJson(route('api.v1.reports.index'))->assertForbidden();
        $this->actingAs($customer);
        $this->get(route('reporting.index'))->assertForbidden();
    }

    private function signInAdmin(): int
    {
        $company = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $admin = $this->createTestUser(['is_active' => true]);
        $this->admin = $admin;
        $role = DB::table('roles')->insertGetId(['slug' => 'admin', 'name' => 'Admin']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $admin->id]);
        $token = $admin->createToken('report-test', ['api'])->plainTextToken;
        $this->withToken($token);

        return $company;
    }

    private function makeRoute(int $company, string $code): int
    {
        return DB::table('routes')->insertGetId([
            'bus_company_id' => $company, 'code' => $code, 'name' => 'Route',
            'origin_city'    => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000,
        ]);
    }
}

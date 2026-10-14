<?php

declare(strict_types=1);

namespace Tests\Feature\TripManagement;

use App\Models\User;
use Database\Seeders\AdminAccountSeeder;
use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TripGeneratorActiveStopsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_stops_make_a_route_schedulable_and_set_its_duration(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId([
            'name'       => 'FUTA Bus Lines',
            'code'       => 'FUTA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'HCM-DL-ACTIVE-STOPS',
            'name'             => 'Hồ Chí Minh - Đà Lạt',
            'origin_city'      => 'Hồ Chí Minh',
            'destination_city' => 'Đà Lạt',
            'base_price'       => 300000,
            'is_active'        => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('route_stops')->insert([
            [
                'route_id'    => $routeId, 'name' => 'Điểm đi', 'stop_order' => 1,
                'leg_minutes' => 0, 'offset_minutes' => 0, 'status' => 'active',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $routeId, 'name' => 'Điểm thử đã ngừng', 'stop_order' => 2,
                'leg_minutes' => 480, 'offset_minutes' => 480, 'status' => 'inactive',
                'created_at'  => now(), 'updated_at' => now(),
            ],
        ]);

        $this->assertFalse(TripGenerator::hasStops($routeId));
        $this->assertSame(0, TripGenerator::duration($routeId));

        DB::table('route_stops')->insert([
            'route_id'    => $routeId, 'name' => 'Điểm đến', 'stop_order' => 3,
            'leg_minutes' => 480, 'offset_minutes' => 480, 'status' => 'active',
            'created_at'  => now(), 'updated_at' => now(),
        ]);

        $this->assertTrue(TripGenerator::hasStops($routeId));
        $this->assertSame(480, TripGenerator::duration($routeId));
    }

    public function test_schedule_editor_only_offers_futa_routes_with_two_active_stops(): void
    {
        config()->set('seed.admin.email', 'schedule-admin@example.test');
        config()->set('seed.admin.password', 'strong-test-password-2026');
        $this->seed(AdminAccountSeeder::class);
        $admin = User::where('email', 'schedule-admin@example.test')->sole();

        $companyId = DB::table('bus_companies')->insertGetId([
            'name'       => 'FUTA Bus Lines',
            'code'       => 'FUTA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $validRouteId = $this->createRoute($companyId, 'HCM-DL-READY');
        $incompleteRouteId = $this->createRoute($companyId, 'HCM-CT-INCOMPLETE');
        $otherCompanyId = DB::table('bus_companies')->insertGetId([
            'name'       => 'Other Bus Company',
            'code'       => 'OTHER',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherCompanyRouteId = $this->createRoute($otherCompanyId, 'OTHER-DALAT-READY');

        DB::table('route_stops')->insert([
            [
                'route_id'    => $validRouteId, 'name' => 'Điểm đi', 'stop_order' => 1,
                'leg_minutes' => 0, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $validRouteId, 'name' => 'Điểm đến', 'stop_order' => 2,
                'leg_minutes' => 480, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $incompleteRouteId, 'name' => 'Điểm đi', 'stop_order' => 1,
                'leg_minutes' => 0, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $incompleteRouteId, 'name' => 'Điểm thử đã ngừng', 'stop_order' => 2,
                'leg_minutes' => 240, 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $otherCompanyRouteId, 'name' => 'Điểm đi', 'stop_order' => 1,
                'leg_minutes' => 0, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'route_id'    => $otherCompanyRouteId, 'name' => 'Điểm đến', 'stop_order' => 2,
                'leg_minutes' => 480, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('trip-management.schedules.index'));

        $response->assertOk();
        $routeIds = $response->viewData('routes')->pluck('id')->all();
        $this->assertContains($validRouteId, $routeIds);
        $this->assertNotContains($incompleteRouteId, $routeIds);
        $this->assertNotContains($otherCompanyRouteId, $routeIds);
    }

    private function createRoute(int $companyId, string $code): int
    {
        return DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => $code,
            'name'             => $code,
            'origin_city'      => 'Hồ Chí Minh',
            'destination_city' => 'Đà Lạt',
            'base_price'       => 300000,
            'is_active'        => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}

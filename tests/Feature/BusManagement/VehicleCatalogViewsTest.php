<?php

namespace Tests\Feature\BusManagement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleCatalogViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_schedule_reads_render_forms_without_creating_domain_records(): void
    {
        DB::table('bus_companies')->insert(['code' => 'FUTA', 'name' => 'FUTA']);
        $admin = $this->createTestUser();
        $role = DB::table('roles')->insertGetId(['slug' => 'admin', 'name' => 'Admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $role]);
        $this->actingAs($admin);
        foreach (['bus-management.buses.index', 'bus-management.vehicle-types.index',
            'bus-management.document-types.index', 'bus-management.vehicle-documents.index',
            'trip-management.schedules.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('method="POST"', false)
                ->assertSee('aria-labelledby=', false);
        }
        $this->assertDatabaseCount('buses', 0);
        $this->assertDatabaseCount('trips', 0);
        $this->actingAs($this->createTestUser())->get(route('bus-management.buses.index'))->assertForbidden();
    }
}

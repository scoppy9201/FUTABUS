<?php

namespace Tests\Feature\BusManagement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bus_api_does_not_expose_other_company_buses_without_a_futa_company(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);
        $otherCompany = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        DB::table('buses')->insert([
            'bus_company_id' => $otherCompany, 'license_plate' => 'OTHER-001',
        ]);

        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->getJson(route('api.v1.admin.buses.index'))->assertStatus(503);
    }

    public function test_admin_can_create_bus_and_active_trip_prevents_changes(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);
        $companyId = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        $typeId = DB::table('vehicle_types')->insertGetId([
            'name' => 'Limousine', 'default_capacity' => 24, 'status' => 'active',
        ]);
        $payload = [
            'license_plate'    => '51b-12345',
            'chassis_number'   => 'frame-12345',
            'color'            => 'Orange',
            'brand'            => 'FUTA',
            'manufacture_year' => 2025,
            'vehicle_type_id'  => $typeId,
            'seat_rows'        => 6,
            'seat_columns'     => 4,
        ];

        $this->postJson(route('api.v1.admin.buses.store'), $payload)->assertUnauthorized();
        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->postJson(route('api.v1.admin.buses.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('data.capacity', 24)
            ->assertJsonPath('data.license_plate', '51B-12345');

        $busId = DB::table('buses')->value('id');
        $this->getJson(route('api.v1.admin.buses.show', ['bus' => $busId]))
            ->assertOk()->assertJsonPath('data.bus_company_id', $companyId);

        $otherCompany = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $otherBus = DB::table('buses')->insertGetId([
            'bus_company_id' => $otherCompany, 'license_plate' => 'OTHER-001',
        ]);
        $this->getJson(route('api.v1.admin.buses.index'))->assertJsonMissing(['license_plate' => 'OTHER-001']);
        $this->getJson(route('api.v1.admin.buses.show', ['bus' => $otherBus]))->assertNotFound();

        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'TEST-ROUTE',
            'name'             => 'Test route',
            'origin_city'      => 'A',
            'destination_city' => 'B',
            'base_price'       => 1000,
        ]);
        DB::table('trips')->insert([
            'route_id'       => $routeId,
            'bus_id'         => $busId,
            'bus_company_id' => $companyId,
            'departure_time' => now()->addDay(),
            'arrival_time'   => now()->addDays(2),
            'price'          => 1000,
            'status'         => 'scheduled',
        ]);

        $this->putJson(route('api.v1.admin.buses.update', ['bus' => $busId]), [
            ...$payload, 'status' => 'inactive',
        ])->assertUnprocessable()->assertJsonValidationErrors('bus');
        $this->deleteJson(route('api.v1.admin.buses.destroy', ['bus' => $busId]))
            ->assertUnprocessable()->assertJsonValidationErrors('bus');
        $this->assertDatabaseHas('buses', ['id' => $busId, 'status' => 'active']);

        $this->actingAs($admin)->delete(route('bus-management.buses.destroy', ['id' => $busId]))
            ->assertSessionHasErrors('bus');
    }
}

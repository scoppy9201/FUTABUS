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
            'name'             => 'Limousine',
            'vehicle_class'    => 'limousine',
            'default_capacity' => 22,
            'status'           => 'active',
        ]);
        $payload = [
            'license_plate'    => '51B-123.45',
            'chassis_number'   => 'FRAME123456',
            'engine_number'    => 'ENGINE123',
            'color'            => 'orange',
            'brand'            => 'FUTA',
            'manufacture_year' => 2025,
            'vehicle_type_id'  => $typeId,
        ];

        $this->postJson(route('api.v1.admin.buses.store'), $payload)->assertUnauthorized();
        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->postJson(route('api.v1.admin.buses.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('data.capacity', 22)
            ->assertJsonPath('data.license_plate', '51B-123.45');

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
        $this->putJson(route('api.v1.admin.buses.update', ['bus' => $busId]), [])
            ->assertUnprocessable()->assertJsonValidationErrors('bus');
        $this->deleteJson(route('api.v1.admin.buses.destroy', ['bus' => $busId]))
            ->assertUnprocessable()->assertJsonValidationErrors('bus');
        $this->assertDatabaseHas('buses', ['id' => $busId, 'status' => 'active']);

        $this->actingAs($admin)->delete(route('bus-management.buses.destroy', ['id' => $busId]))
            ->assertRedirect(route('bus-management.buses.index'))
            ->assertSessionHas('warning', __('BusManagement::app.bus_err_cannot_delete'));
    }

    public function test_two_deck_limousine_bus_keeps_its_34_berth_seat_layout(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);
        DB::table('bus_companies')->insert(['name' => 'FUTA', 'code' => 'FUTA']);
        $typeId = DB::table('vehicle_types')->insertGetId([
            'name'             => 'Limousine Sleeper Two Deck 34',
            'vehicle_class'    => 'limousine_sleeper',
            'floor_count'      => 2,
            'seat_rows'        => 6,
            'seat_columns'     => 3,
            'unused_seats'     => 2,
            'default_capacity' => 34,
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->postJson(route('api.v1.admin.buses.store'), [
                'license_plate'    => '51B-123.45',
                'chassis_number'   => 'FRAME123456',
                'engine_number'    => 'ENGINE123',
                'color'            => 'red',
                'brand'            => 'FUTA',
                'manufacture_year' => 2025,
                'vehicle_type_id'  => $typeId,
            ])->assertCreated()->assertJsonPath('data.capacity', 34);

        $bus = DB::table('buses')->where('license_plate', '51B-123.45')->first();
        $this->assertSame('limousine', $bus->bus_type);
        $this->assertSame(6, (int) $bus->seat_rows);
        $this->assertSame(3, (int) $bus->seat_columns);
        $this->assertSame(34, DB::table('seat_layouts')->where('bus_id', $bus->id)->count());
        $this->assertSame(17, DB::table('seat_layouts')->where('bus_id', $bus->id)->where('deck', 'lower')->count());
        $this->assertSame(17, DB::table('seat_layouts')->where('bus_id', $bus->id)->where('deck', 'upper')->count());
        $this->assertSame(34, DB::table('seat_layouts')->where('bus_id', $bus->id)->where('seat_type', 'sleeper')->count());
        $this->assertSame(['blanket', 'water', 'wet_towel', 'charging', 'wifi'], json_decode($bus->amenities, true));
        $this->assertSame('19006067', $bus->wifi_password);
        $this->assertDatabaseHas('seat_layouts', [
            'bus_id'        => $bus->id,
            'seat_code'     => 'A01',
            'row_number'    => 1,
            'column_number' => 1,
            'deck'          => 'lower',
        ]);
        $this->assertDatabaseHas('seat_layouts', [
            'bus_id'        => $bus->id,
            'seat_code'     => 'B17',
            'row_number'    => 6,
            'column_number' => 3,
            'deck'          => 'upper',
        ]);
        foreach (['A' => 'lower', 'B' => 'upper'] as $prefix => $deck) {
            $this->assertDatabaseMissing('seat_layouts', [
                'bus_id' => $bus->id, 'deck' => $deck, 'row_number' => 1, 'column_number' => 2,
            ]);
            $this->assertDatabaseHas('seat_layouts', [
                'bus_id' => $bus->id, 'seat_code' => $prefix.'02', 'row_number' => 1, 'column_number' => 3,
            ]);
            $this->assertDatabaseHas('seat_layouts', [
                'bus_id' => $bus->id, 'seat_code' => $prefix.'03', 'row_number' => 2, 'column_number' => 1,
            ]);
        }
    }
}

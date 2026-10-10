<?php

namespace Tests\Feature\BusManagement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleTypeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_active_admin_bearer_token_can_manage_vehicle_types(): void
    {
        $url = route('api.v1.admin.vehicle-types.index');
        $this->getJson($url)->assertUnauthorized();

        $customer = User::factory()->create(['is_active' => true]);
        $this->withToken($customer->createToken('customer', ['api'])->plainTextToken)
            ->getJson($url)->assertForbidden();

        app('auth')->forgetGuards();
        $admin = $this->admin();
        $this->actingAs($admin)->getJson($url)->assertForbidden();
        app('auth')->forgetGuards();
        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken)
            ->getJson($url)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_admin_can_create_update_and_deactivate_a_vehicle_type_in_use(): void
    {
        $admin = $this->admin();
        $this->withToken($admin->createToken('admin', ['api'])->plainTextToken);
        $url = route('api.v1.admin.vehicle-types.store');

        $this->postJson($url, ['name' => 'Limousine'])
            ->assertUnprocessable()->assertJsonValidationErrors('default_capacity');
        $this->postJson($url, [
            'name' => 'Limousine', 'description' => 'Comfort seats', 'default_capacity' => 24,
        ])->assertCreated()->assertJsonPath('data.status', 'active');

        $id = DB::table('vehicle_types')->value('id');
        $this->putJson(route('api.v1.admin.vehicle-types.update', ['vehicleType' => $id]), [
            'name' => 'Limousine', 'description' => 'Updated seats', 'default_capacity' => 22,
        ])->assertOk()->assertJsonPath('data.default_capacity', 22);

        $companyId = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        DB::table('buses')->insert([
            'bus_company_id'  => $companyId,
            'vehicle_type_id' => $id,
            'license_plate'   => '51B-12345',
        ]);

        $this->deleteJson(route('api.v1.admin.vehicle-types.destroy', ['vehicleType' => $id]))
            ->assertOk()->assertJsonPath('data.status', 'inactive');
        $this->assertDatabaseHas('vehicle_types', ['id' => $id, 'status' => 'inactive']);
    }

    public function test_existing_web_vehicle_type_form_uses_the_same_business_rules(): void
    {
        $this->actingAs($this->admin())
            ->post(route('bus-management.vehicle-types.store'), [
                'name' => 'Sleeper', 'default_capacity' => 34,
            ])->assertRedirect(route('bus-management.vehicle-types.index'));

        $id = DB::table('vehicle_types')->value('id');
        $this->put(route('bus-management.vehicle-types.update', ['id' => $id]), [
            'name' => 'Sleeper', 'default_capacity' => 32,
        ])->assertRedirect(route('bus-management.vehicle-types.index'));
        $this->assertDatabaseHas('vehicle_types', ['id' => $id, 'default_capacity' => 32]);

        $this->delete(route('bus-management.vehicle-types.destroy', ['id' => $id]))
            ->assertRedirect(route('bus-management.vehicle-types.index'));
        $this->assertDatabaseMissing('vehicle_types', ['id' => $id]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);

        return $admin;
    }
}

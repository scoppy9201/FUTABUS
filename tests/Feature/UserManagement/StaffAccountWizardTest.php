<?php

namespace Tests\Feature\UserManagement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffAccountWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_and_direct_creation_url_render_the_same_three_step_modal(): void
    {
        $this->actingAs($this->manager());
        $this->get(route('staff-accounts.index'))->assertOk()
            ->assertSee('data-staff-create-dialog', false)->assertSee('data-staff-step="3"', false)
            ->assertSee('data-auto-open="0"', false);
        $this->get(route('staff-accounts.create'))->assertOk()->assertSee('data-auto-open="1"', false);
    }

    public function test_invalid_creation_returns_to_the_modal_without_creating_an_account(): void
    {
        $this->actingAs($this->manager());
        $this->from(route('staff-accounts.index'))->post(route('staff-accounts.store'), [
            ...$this->details(), 'email' => 'invalid',
        ])->assertRedirect(route('staff-accounts.index'))->assertSessionHasErrors('email');
        $this->get(route('staff-accounts.index'))->assertOk()
            ->assertSee('data-auto-open="1"', false)->assertSee('data-initial-step="2"', false)
            ->assertSee('Employee Example')->assertDontSee('wizard-secret-password');
        $this->assertDatabaseMissing('users', ['email' => 'employee@example.test']);
    }

    public function test_confirmed_creation_still_assigns_the_company_and_staff_role(): void
    {
        $manager = $this->manager();
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        $this->actingAs($manager)->post(route('staff-accounts.store'), $this->details())
            ->assertRedirect(route('staff-accounts.index'))->assertSessionHasNoErrors();
        $staff = User::where('email', 'employee@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('wizard-secret-password', $staff->password));
        $this->assertTrue($staff->is_active);
        $this->assertDatabaseHas('role_user', ['user_id' => $staff->id, 'role_id' => $role]);
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'bus_company_id' => $manager->bus_company_id]);
        $this->actingAs($this->createTestUser())->post(route('staff-accounts.store'), $this->details())->assertForbidden();
    }

    private function manager(): User
    {
        $company = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        $manager = $this->createTestUser(['bus_company_id' => $company]);
        $role = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $manager->id]);

        return $manager;
    }

    private function details(): array
    {
        return ['name'      => 'Employee Example', 'phone' => '0901234567', 'email' => 'employee@example.test',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'password'      => 'wizard-secret-password', 'password_confirmation' => 'wizard-secret-password'];
    }
}

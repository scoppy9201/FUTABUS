<?php

namespace Tests\Feature\RolePermission;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_access_tabs_and_role_forms_render_shared_layout_and_modals(): void
    {
        [$manager, $company, $permission] = $this->fixture();
        $role = $this->role($company);
        $this->actingAs($manager);
        $this->get(route('access-management.index'))->assertOk()->assertSee('data-role-wizard', false);
        $this->get(route('access-management.roles.create'))->assertOk()->assertSee('data-auto-open="1"', false);
        $this->get(route('access-management.roles.edit', $role))->assertOk()->assertSee('data-role-step="3"', false);
        $this->get(route('access-management.catalog'))->assertOk()->assertSee('permission-'.$permission)
            ->assertDontSee('onsubmit="return confirm', false)
            ->assertDontSeeText('ticket.view')->assertDontSeeText('bus_company.view');
        $staff = $this->staff($company);
        $this->get(route('access-management.users'))->assertOk()->assertSee('assign-'.$staff->id)
            ->assertSee('data-operations-pagination', false);
    }

    public function test_role_creation_rejects_inactive_permissions_and_reopens_the_correct_step(): void
    {
        [$manager, $company, $permission] = $this->fixture();
        DB::table('permissions')->where('id', $permission)->update(['is_active' => false]);
        $data = ['name' => 'Support', 'permission_ids_present' => 1, 'permission_ids' => [$permission], '_role_wizard' => 1];
        $this->actingAs($manager)->from(route('access-management.index'))
            ->post(route('access-management.roles.store'), $data)->assertSessionHasErrors('permission_ids.0');
        $this->get(route('access-management.index'))->assertOk()->assertSee('data-auto-open="1"', false)
            ->assertSee('data-initial-step="2"', false);
        $this->assertDatabaseMissing('roles', ['name' => 'Support', 'bus_company_id' => $company]);
        DB::table('permissions')->where('id', $permission)->update(['is_active' => true]);
        $this->post(route('access-management.roles.store'), $data)->assertRedirect(route('access-management.index'));
        $role = DB::table('roles')->where('bus_company_id', $company)->where('name', 'Support')->value('id');
        $this->assertDatabaseHas('permission_role', ['role_id' => $role, 'permission_id' => $permission]);
    }

    public function test_assignments_reject_foreign_roles_and_preserve_the_base_staff_role(): void
    {
        [$manager, $company] = $this->fixture();
        $staff = $this->staff($company);
        $role = $this->role($company);
        $otherCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $foreign = $this->role($otherCompany);
        $this->actingAs($manager)->put(route('access-management.users.assign', $staff->id), ['role_ids' => [$foreign]])
            ->assertSessionHasErrors('role_ids');
        $this->assertDatabaseMissing('role_user', ['role_id' => $foreign, 'user_id' => $staff->id]);
        $this->put(route('access-management.users.assign', $staff->id), ['role_ids' => [$role]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('role_user', ['role_id' => $role, 'user_id' => $staff->id]);
        $this->put(route('access-management.users.assign', $staff->id), [])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('role_user', ['role_id' => $role, 'user_id' => $staff->id]);
        $this->assertTrue($staff->fresh()->hasRole('staff'));
        $this->get(route('access-management.roles.edit', $foreign))->assertNotFound();
        $this->actingAs($this->createTestUser())->get(route('access-management.index'))->assertForbidden();
    }

    public function test_read_only_company_manager_cannot_create_roles_or_edit_catalog(): void
    {
        [$manager] = $this->fixture();
        DB::table('role_user')->where('user_id', $manager->id)->delete();
        $role = DB::table('roles')->insertGetId(['name' => 'Company', 'slug' => 'bus-company']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $manager->id]);
        $permission = DB::table('permissions')->where('slug', 'role.view')->value('id')
            ?? DB::table('permissions')->insertGetId(['slug' => 'role.view', 'name' => 'role.view', 'group' => 'role', 'is_active' => true]);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        $this->actingAs($manager)->get(route('access-management.index'))->assertOk()->assertDontSee('data-role-wizard', false);
        $this->post(route('access-management.roles.store'), ['name' => 'Forbidden', 'permission_ids_present' => 1])->assertForbidden();
        $this->post(route('access-management.catalog.store'), ['slug' => 'ticket.view', 'display_name' => 'Changed'])->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'Forbidden']);
    }

    public function test_permission_status_sets_an_explicit_state_and_retries_do_not_toggle_it(): void
    {
        [$manager, , $permission] = $this->fixture();
        $this->actingAs($manager);
        $url = route('access-management.catalog.status', $permission);
        $this->patch($url, [])->assertSessionHasErrors('is_active');
        $this->assertDatabaseHas('permissions', ['id' => $permission, 'is_active' => true]);
        $this->patch($url, ['is_active' => 0])->assertSessionHasNoErrors();
        $this->patch($url, ['is_active' => 0])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('permissions', ['id' => $permission, 'is_active' => false]);
        $this->patch($url, ['is_active' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('permissions', ['id' => $permission, 'is_active' => true]);
    }

    public function test_catalog_keeps_search_and_localizes_customer_support_permissions(): void
    {
        [$manager, $company] = $this->fixture();
        DB::table('permissions')->updateOrInsert(['slug' => 'support.assign'], [
            'name'          => 'Assign customer support',
            'group'         => 'support',
            'display_name'  => 'Assign customer support',
            'display_group' => 'Customer support',
            'is_active'     => true,
        ]);
        app()->setLocale('vi');
        $this->actingAs($manager);

        $this->get(route('access-management.catalog'))->assertOk()
            ->assertSee('x-model.debounce.150ms="query"', false)
            ->assertSee('Hỗ trợ khách hàng')
            ->assertSee('Điều phối yêu cầu hỗ trợ')
            ->assertDontSeeText('Assign customer support');

        $this->get(route('access-management.roles.create'))->assertOk()
            ->assertSee('Hỗ trợ khách hàng')
            ->assertSee('Điều phối yêu cầu hỗ trợ')
            ->assertDontSeeText('Assign customer support');
    }

    private function fixture(): array
    {
        $company = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $user = $this->createTestUser(['bus_company_id' => $company]);
        $admin = DB::table('roles')->insertGetId(['slug' => 'admin', 'name' => 'Admin']);
        DB::table('role_user')->insert(['role_id' => $admin, 'user_id' => $user->id]);
        $permission = DB::table('permissions')->insertGetId(['slug' => 'ticket.view', 'name' => 'ticket.view', 'group' => 'ticket', 'is_active' => true]);

        return [$user, $company, $permission];
    }

    private function role(int $company): int
    {
        return DB::table('roles')->insertGetId(['name' => 'Operations', 'slug' => 'operations-'.$company, 'bus_company_id' => $company]);
    }

    private function staff(int $company): User
    {
        $staff = $this->createTestUser(['bus_company_id' => $company]);
        $role = DB::table('roles')->insertGetId(['slug' => 'staff', 'name' => 'Staff']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);

        return $staff;
    }
}

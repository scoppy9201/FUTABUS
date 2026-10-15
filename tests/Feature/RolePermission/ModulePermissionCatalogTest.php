<?php

namespace Tests\Feature\RolePermission;

use App\Models\User;
use FuteBus\RolePermission\Services\PermissionCatalogService;
use FuteBus\RouteManagement\Http\Controllers\RouteController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModulePermissionCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_combines_module_guards_and_routes_without_writing_on_read(): void
    {
        $admin = $this->admin();
        $legacy = $this->permission('ticket.verify');
        $count = DB::table('permissions')->count();
        $definitions = app(PermissionCatalogService::class)->definitions();
        $this->assertContains('RouteManagement', $definitions['route.update']['modules']);
        $this->assertContains('RolePermission', $definitions['role.delete']['modules']);
        $this->assertContains('Dashboard', $definitions['trip.view']['modules']);
        $this->assertContains('StaffTicketProcessing', $definitions['booking.create']['modules']);
        $this->assertArrayNotHasKey('ticket.verify', $definitions->all());

        $this->actingAs($admin)->get(route('access-management.catalog'))->assertOk()
            ->assertSeeText('Chưa đồng bộ')->assertSeeText('Tuyến đường và điểm dừng')
            ->assertDontSee('permission-'.$legacy.'"', false);
        $this->assertDatabaseCount('permissions', $count);
        $this->assertDatabaseMissing('permissions', ['slug' => 'route.create']);
    }

    public function test_new_permission_middleware_is_discovered_without_a_central_list(): void
    {
        Route::get('/catalog-discovery-probe', [RouteController::class, 'index'])
            ->middleware('permission:route.audit');
        $this->assertContains('route.audit', app(PermissionCatalogService::class)->slugs());
    }

    public function test_catalog_uses_the_nine_business_groups_and_keeps_ticket_keys_unchanged(): void
    {
        $this->admin();
        $catalog = app(PermissionCatalogService::class);
        $groups = $catalog->rows()->groupBy('group');
        $this->assertSame(['bus', 'route', 'trip', 'trip_schedule', 'user', 'role', 'payment', 'booking', 'support'], $groups->keys()->all());
        $this->assertContains('trip_schedule.create', $catalog->slugs());
        $this->assertContains('trip.cancel', $catalog->slugs());
        $this->assertNotContains('dashboard.view', $catalog->slugs());
        $this->assertTrue($groups['bus']->every(fn (object $row): bool => $row->admin_only));
        $catalog->synchronize();
        $this->assertSame('booking', $catalog->rows()->firstWhere('slug', 'ticket.view')->group);
        $this->assertDatabaseHas('permissions', ['slug' => 'ticket.view', 'group' => 'ticket']);
        $this->assertDatabaseHas('permissions', ['slug' => 'trip_schedule.create', 'group' => 'trip_schedule']);
    }

    public function test_saving_a_role_preserves_disabled_and_out_of_catalog_permissions(): void
    {
        $admin = $this->admin();
        $catalog = app(PermissionCatalogService::class);
        $catalog->synchronize();
        $hidden = $this->permission('report.view');
        $disabled = DB::table('permissions')->where('slug', 'route.delete')->value('id');
        DB::table('permissions')->where('id', $disabled)->update(['is_active' => false]);
        $active = DB::table('permissions')->where('slug', 'route.view')->value('id');
        $role = $this->role('company-editor', $admin->bus_company_id);
        foreach ([$hidden, $disabled, $active] as $id) {
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $id]);
        }
        $this->actingAs($admin)->put(route('access-management.roles.update', $role), [
            'name' => 'Renamed', 'permission_ids_present' => 1, 'permission_ids' => [],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('permission_role', ['role_id' => $role, 'permission_id' => $hidden]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role, 'permission_id' => $disabled]);
        $this->assertDatabaseMissing('permission_role', ['role_id' => $role, 'permission_id' => $active]);
    }

    public function test_vehicle_and_schedule_routes_keep_their_admin_boundary_after_permission_registration(): void
    {
        $admin = $this->admin();
        app(PermissionCatalogService::class)->synchronize();
        $manager = $this->createTestUser(['bus_company_id' => $admin->bus_company_id]);
        $role = $this->role('bus-company');
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $manager->id]);
        foreach (['bus.view', 'trip.view', 'trip_schedule.view'] as $slug) {
            DB::table('permission_role')->insert([
                'role_id' => $role, 'permission_id' => DB::table('permissions')->where('slug', $slug)->value('id'),
            ]);
        }
        $this->actingAs($manager)->get(route('bus-management.buses.index'))->assertForbidden();
        $this->get(route('trip-management.trips.index'))->assertForbidden();
        $this->get(route('trip-management.schedules.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('trip-management.schedules.index'))->assertOk();
    }

    public function test_sync_is_admin_only_idempotent_and_preserves_disabled_permissions_and_assignments(): void
    {
        $admin = $this->admin();
        $permission = $this->permission('route.view', false);
        DB::table('permissions')->where('id', $permission)->update(['display_name' => 'Custom routes']);
        $role = $this->role('operator', $admin->bus_company_id);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $admin->id]);

        $manager = $this->createTestUser(['bus_company_id' => $admin->bus_company_id]);
        $companyRole = $this->role('bus-company');
        DB::table('role_user')->insert(['role_id' => $companyRole, 'user_id' => $manager->id]);
        $before = DB::table('permissions')->count();
        $this->actingAs($manager)->post(route('access-management.catalog.sync'))->assertForbidden();
        $this->assertDatabaseCount('permissions', $before);

        $this->actingAs($admin)->post(route('access-management.catalog.sync'))->assertRedirect(route('access-management.catalog'));
        $count = DB::table('permissions')->count();
        $this->post(route('access-management.catalog.sync'))->assertSessionHas('status', __('RolePermission::app.catalog_synchronized', ['count' => 0]));
        $this->assertDatabaseCount('permissions', $count);
        $this->assertDatabaseHas('permissions', ['id' => $permission, 'is_active' => false, 'display_name' => 'Custom routes']);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role, 'permission_id' => $permission]);
        $this->assertDatabaseHas('role_user', ['role_id' => $role, 'user_id' => $admin->id]);
        $this->assertDatabaseMissing('permissions', ['slug' => 'ticket.verify']);
        $this->get(route('access-management.catalog'))->assertOk()->assertSeeText('Custom routes')->assertDontSeeText('Chưa đồng bộ');
    }

    public function test_legacy_database_permissions_cannot_be_registered_edited_or_newly_assigned(): void
    {
        $admin = $this->admin();
        $legacy = $this->permission('ticket.verify');
        $this->actingAs($admin)->post(route('access-management.catalog.store'), [
            'slug' => 'ticket.verify', 'display_name' => 'Unsupported',
        ])->assertSessionHasErrors('slug');
        $this->put(route('access-management.catalog.update', $legacy), ['display_name' => 'Unsupported'])->assertNotFound();
        $this->patch(route('access-management.catalog.status', $legacy), ['is_active' => 0])->assertNotFound();
        $this->post(route('access-management.roles.store'), [
            'name' => 'Forged role', 'permission_ids_present' => 1, 'permission_ids' => [$legacy],
        ])->assertSessionHasErrors('permission_ids.0');
        $this->assertDatabaseMissing('roles', ['name' => 'Forged role']);
        $this->assertDatabaseHas('permissions', ['id' => $legacy, 'is_active' => true]);
    }

    public function test_disabled_permission_is_denied_to_assigned_staff_and_restored_when_reactivated(): void
    {
        $admin = $this->admin();
        $permission = $this->permission('route.view');
        $staff = $this->createTestUser(['bus_company_id' => $admin->bus_company_id]);
        $role = $this->role('company-operator', $admin->bus_company_id);
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        $this->assertTrue($staff->hasPermissionTo('route.view'));
        $this->actingAs($admin)->patch(route('access-management.catalog.status', $permission), ['is_active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($staff->hasPermissionTo('route.view'));
        $this->assertTrue($admin->hasPermissionTo('route.view'));
        $this->patch(route('access-management.catalog.status', $permission), ['is_active' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($staff->hasPermissionTo('route.view'));
    }

    private function admin(): User
    {
        $company = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $admin = $this->createTestUser(['bus_company_id' => $company]);
        DB::table('role_user')->insert(['role_id' => $this->role('admin'), 'user_id' => $admin->id]);

        return $admin;
    }

    private function role(string $slug, ?int $company = null): int
    {
        return DB::table('roles')->insertGetId(['name' => $slug, 'slug' => $slug, 'bus_company_id' => $company]);
    }

    private function permission(string $slug, bool $active = true): int
    {
        return DB::table('permissions')->insertGetId([
            'slug' => $slug, 'name' => $slug, 'group' => explode('.', $slug)[0], 'is_active' => $active,
        ]);
    }
}

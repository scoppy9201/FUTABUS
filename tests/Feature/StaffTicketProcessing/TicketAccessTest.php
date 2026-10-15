<?php

namespace Tests\Feature\StaffTicketProcessing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_booking_permissions_the_menu_is_locked_and_direct_access_is_denied(): void
    {
        [$staff] = $this->staff();
        $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk()
            ->assertSee(__('StaffDashboard::staff.ticket_access_hint'))
            ->assertDontSee('href="'.route('staff.tickets').'"', false);
        $this->get(route('staff.tickets'))->assertForbidden();
    }

    public function test_ticket_processing_permissions_unlock_processing_without_granting_other_actions(): void
    {
        [$staff, $role] = $this->staff();
        foreach (['booking.create', 'ticket.view'] as $slug) {
            DB::table('permission_role')->where('role_id', $role)->delete();
            $permission = $this->permission($slug);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
            $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk()
                ->assertSee('href="'.route('staff.tickets').'"', false);
            $this->get(route('staff.tickets'))->assertOk();
            if ($slug !== 'ticket.view') {
                $this->get(route('staff.ticket-lookup'))->assertForbidden();
            }
            if ($slug !== 'booking.create') {
                $this->post(route('staff.ticket-sales.store'), [])->assertForbidden();
            }
            DB::table('permissions')->where('id', $permission)->update(['is_active' => false]);
            $this->get(route('staff.tickets'))->assertForbidden();
        }
    }

    public function test_booking_view_only_unlocks_booking_tracking_and_keeps_ticket_processing_locked(): void
    {
        [$staff, $role] = $this->staff();
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $this->permission('booking.view')]);
        $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk()
            ->assertSee(__('StaffDashboard::staff.ticket_access_hint'))
            ->assertDontSee('href="'.route('staff.tickets').'"', false)
            ->assertSee('href="'.route('staff.bookings.index').'"', false);
        $this->get(route('staff.tickets'))->assertForbidden();
        $this->get(route('staff.bookings.index'))->assertOk();
    }

    public function test_unchecking_the_entire_booking_group_locks_an_assigned_staff_account(): void
    {
        [$staff, $role] = $this->staff();
        foreach (['booking.create', 'booking.view', 'ticket.view'] as $slug) {
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $this->permission($slug)]);
        }
        $this->actingAs($staff)->get(route('staff.tickets'))->assertOk();
        $admin = $this->createTestUser(['bus_company_id' => $staff->bus_company_id]);
        $adminRole = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $adminRole, 'user_id' => $admin->id]);
        $this->actingAs($admin)->put(route('access-management.roles.update', $role), [
            'name' => 'Operator', 'permission_ids_present' => 1, 'permission_ids' => [],
        ])->assertSessionHasNoErrors();
        $this->actingAs($staff)->get(route('staff.tickets'))->assertForbidden();
        $this->get(route('staff.dashboard'))->assertOk()->assertDontSee('href="'.route('staff.tickets').'"', false);
    }

    private function staff(): array
    {
        $company = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true, 'email_verified_at' => now()]);
        $base = DB::table('roles')->insertGetId(['slug' => 'staff', 'name' => 'Staff']);
        $role = DB::table('roles')->insertGetId(['slug' => 'operator', 'name' => 'Operator', 'bus_company_id' => $company]);
        foreach ([$base, $role] as $id) {
            DB::table('role_user')->insert(['role_id' => $id, 'user_id' => $staff->id]);
        }

        return [$staff, $role];
    }

    private function permission(string $slug): int
    {
        return DB::table('permissions')->where('slug', $slug)->value('id')
            ?? DB::table('permissions')->insertGetId(['slug' => $slug, 'name' => $slug, 'group' => explode('.', $slug)[0], 'is_active' => true]);
    }

    public function test_staff_menu_entries_are_locked_until_the_corresponding_view_permission_is_granted(): void
    {
        [$staff, $role] = $this->staff();
        foreach ([
            ['support.view', 'staff.support', 'support_access_hint'],
            ['ticket.view', 'staff.ticket-lookup', 'lookup_access_hint'],
            ['booking.view', 'staff.bookings.index', 'booking_access_hint'],
        ] as [$slug, $route, $hint]) {
            $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk()
                ->assertDontSee('href="'.route($route).'"', false)
                ->assertSee(__('StaffDashboard::staff.'.$hint));
            $this->get(route($route))->assertForbidden();
            $permission = $this->permission($slug);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
            $this->get(route('staff.dashboard'))->assertOk()->assertSee('href="'.route($route).'"', false);
            $this->get(route($route))->assertOk();
            DB::table('permissions')->where('id', $permission)->update(['is_active' => false]);
            $this->get(route('staff.dashboard'))->assertOk()->assertDontSee('href="'.route($route).'"', false);
            $this->get(route($route))->assertForbidden();
        }
    }
}

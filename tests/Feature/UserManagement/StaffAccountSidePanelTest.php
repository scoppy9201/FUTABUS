<?php

namespace Tests\Feature\UserManagement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffAccountSidePanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_avatar_is_streamed_only_for_authorized_managers_in_the_same_company(): void
    {
        Storage::fake('public');
        [$manager, $staff] = $this->managerAndStaff();
        $manager->update(['is_active' => true]);
        $staff->update(['avatar' => 'avatars/example.png']);
        Storage::disk('public')->put('avatars/example.png', 'staff-avatar-content');
        $url = route('staff-accounts.avatar', $staff->id);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($staff)->get($url)->assertForbidden();
        $response = $this->actingAs($manager)->get($url)->assertOk();
        $this->assertSame('staff-avatar-content', $response->streamedContent());
        $this->get(route('staff-accounts.edit', $staff->id))->assertOk()->assertSee($url, false);
        $other = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'OTHER']);
        $staff->update(['bus_company_id' => $other]);
        $this->get($url)->assertNotFound();
        $staff->update(['bus_company_id' => $manager->bus_company_id, 'avatar' => 'avatars/../private.txt']);
        $this->get($url)->assertNotFound();
        $staff->update(['avatar' => 'avatars/missing.png']);
        $this->get($url)->assertNotFound();
    }

    public function test_staff_details_open_in_a_side_panel_over_the_account_list(): void
    {
        [$manager, $staff] = $this->managerAndStaff();

        $this->actingAs($manager)->get(route('staff-accounts.show', $staff->id))
            ->assertOk()
            ->assertSee('data-staff-detail-panel', false)
            ->assertSee('data-auto-open="0"', false)
            ->assertSee(__('UserManagement::app.list_title'))
            ->assertSee($staff->email)
            ->assertSee('x-effect="window.FutaSidePanel.sync($el, openPanel, $nextTick)"', false);
    }

    public function test_staff_edit_form_opens_in_a_side_panel_and_keeps_the_existing_update_route(): void
    {
        [$manager, $staff] = $this->managerAndStaff();

        $this->actingAs($manager)->get(route('staff-accounts.edit', $staff->id))
            ->assertOk()
            ->assertSee('data-staff-edit-panel', false)
            ->assertSee('data-auto-open="0"', false)
            ->assertSee('id="edit-staff-avatar"', false)
            ->assertSee('name="_method" value="put"', false)
            ->assertSee(route('staff-accounts.update', $staff->id), false)
            ->assertSee($staff->email);
    }

    public function test_edit_validation_returns_to_the_open_side_panel_with_old_values(): void
    {
        [$manager, $staff] = $this->managerAndStaff();
        $this->actingAs($manager);

        $this->from(route('staff-accounts.edit', $staff->id))
            ->put(route('staff-accounts.update', $staff->id), [
                'name'                  => 'Updated Staff Name',
                'email'                 => 'invalid-email',
                'phone'                 => '0901234567',
                'date_of_birth'         => now()->subYears(25)->toDateString(),
                'password'              => '',
                'password_confirmation' => '',
                'is_active'             => 1,
            ])
            ->assertRedirect(route('staff-accounts.edit', $staff->id))
            ->assertSessionHasErrors('email');

        $this->get(route('staff-accounts.edit', $staff->id))
            ->assertOk()
            ->assertSee('data-staff-edit-panel', false)
            ->assertSee('data-auto-open="0"', false)
            ->assertSee('value="Updated Staff Name"', false)
            ->assertSee('invalid-email');
    }

    /** @return array{User, User} */
    private function managerAndStaff(): array
    {
        $companyId = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        $manager = $this->createTestUser(['bus_company_id' => $companyId]);
        $managerRoleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $managerRoleId, 'user_id' => $manager->id]);

        $staff = $this->createTestUser([
            'name'           => 'Example Staff',
            'email'          => 'staff@example.test',
            'phone'          => '0901234567',
            'date_of_birth'  => now()->subYears(25)->toDateString(),
            'bus_company_id' => $companyId,
        ]);
        $staffRoleId = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['role_id' => $staffRoleId, 'user_id' => $staff->id]);

        return [$manager, $staff];
    }
}

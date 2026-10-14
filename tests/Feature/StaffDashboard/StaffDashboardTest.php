<?php

namespace Tests\Feature\StaffDashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_redirects_to_dashboard_and_api_returns_only_allowed_data(): void
    {
        $staff = $this->createTestUser(['is_active' => true, 'password' => 'staff-test-password']);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);

        $this->post(route('login.store'), ['email' => $staff->email, 'password' => 'staff-test-password'])
            ->assertRedirect(route('staff.dashboard'));
        $this->assertAuthenticatedAs($staff);
        $this->withSession(['_token' => 'staff-test-token'])->withHeaders([
            'X-CSRF-TOKEN' => 'staff-test-token', 'Origin' => config('app.url'),
        ]);
        $this->get(route('staff.dashboard'))->assertOk()
            ->assertSeeText(__('StaffDashboard::staff.tickets.title'))
            ->assertSeeText(__('StaffDashboard::staff.support.title'))
            ->assertSee('staffDashboard', false)
            ->assertSeeText(__('StaffDashboard::staff.unavailable'))
            ->assertSeeText(__('Payment::refunds.title'))
            ->assertSee(__('StaffDashboard::staff.refund_access_hint'));
        $this->get(route('staff.refunds.index'))->assertForbidden();
        $this->getJson(route('api.v1.staff.dashboard.show'))
            ->assertOk()->assertExactJson(['data' => [
                'employee' => ['id' => $staff->id, 'name' => $staff->name, 'avatar_url' => null],
                'groups'   => [
                    'tickets' => ['sale' => false, 'exchange' => false, 'cancellation' => false, 'lookup' => false],
                    'support' => ['intake' => false, 'requests' => false, 'resolution' => false],
                ],
                'navigation'       => [],
                'statistics'       => ['trips_today' => null, 'tickets_today' => null, 'support_requests' => null, 'processing_requests' => null],
                'support_requests' => null,
                'notifications'    => [],
                'faq'              => [],
            ]]);
        $this->get(route('staff.dashboard', ['lang' => 'en']))->assertOk()->assertSeeText('Quick actions')->assertSeeText('English');
        $this->get(route('staff.dashboard', ['lang' => 'vi']))->assertOk()->assertSeeText('Chức năng nhanh')->assertSeeText('Tiếng Việt');
        $this->get(route('staff.dashboard'))->assertOk();
        $this->get(route('staff.profile'))->assertOk()->assertSee('staffProfile', false)
            ->assertSeeText(__('StaffDashboard::staff.role'))->assertDontSeeText('FUTAPay');
        $this->getJson(route('api.v1.staff.me.show'))->assertOk()
            ->assertJsonPath('data.id', $staff->id)->assertJsonMissingPath('data.password');
        $this->putJson(route('api.v1.staff.me.update'), ['name' => '', 'phone' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone']);
        $this->get(route('staff.password'))->assertOk()->assertSee('staffPassword', false)
            ->assertSee('staff-navigation', false)->assertDontSeeText('FUTAPay');
        $this->putJson(route('api.v1.staff.me.password.update'), [
            'current_password' => 'wrong', 'password' => 'NewStaffPassword123', 'password_confirmation' => 'NewStaffPassword123',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('staff-test-password', $staff->fresh()->password));
        $this->putJson(route('api.v1.staff.me.password.update', ['lang' => 'vi']), [
            'current_password' => 'staff-test-password', 'password' => '', 'password_confirmation' => '',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Vui lòng nhập mật khẩu mới.')
            ->assertJsonPath('errors.password_confirmation.0', 'Vui lòng xác nhận mật khẩu mới.');
        $this->putJson(route('api.v1.staff.me.password.update', ['lang' => 'vi']), [
            'current_password' => 'staff-test-password', 'password' => 'NewStaffPassword123', 'password_confirmation' => 'MismatchPassword123',
        ])->assertUnprocessable()->assertJsonValidationErrors('password_confirmation')
            ->assertJsonPath('errors.password_confirmation.0', 'Mật khẩu xác nhận không khớp.');
        Mail::fake();
        $staff->createToken('staff test', ['api']);
        $this->putJson(route('api.v1.staff.me.password.update'), [
            'current_password' => 'staff-test-password', 'password' => 'NewStaffPassword123', 'password_confirmation' => 'NewStaffPassword123',
        ])->assertOk()->assertJsonPath('notification_sent', true);
        $this->assertTrue(Hash::check('NewStaffPassword123', $staff->fresh()->password));
        $this->assertSame(0, $staff->tokens()->count());
        $originalEmail = $staff->email;
        $this->putJson(route('api.v1.staff.me.update'), [
            'name'      => 'Updated Staff', 'phone' => '0912345678', 'email' => 'ignored@example.test',
            'is_active' => false, 'bus_company_id' => 999,
        ])->assertOk()->assertJsonPath('data.name', 'Updated Staff')
            ->assertJsonPath('data.email', $originalEmail);
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'phone' => '+84912345678', 'is_active' => true, 'bus_company_id' => null]);
    }

    public function test_staff_session_mutation_requires_csrf_and_staff_token_requires_api_ability(): void
    {
        $staff = $this->createTestUser(['is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        $badToken = $staff->createToken('insufficient ability', ['other'])->plainTextToken;
        $this->withToken($badToken)->getJson(route('api.v1.staff.me.show'))->assertForbidden();
        $goodToken = $staff->createToken('staff API', ['api'])->plainTextToken;
        $this->app['auth']->forgetGuards();
        $this->withToken($goodToken)->getJson(route('api.v1.staff.me.show'))->assertOk();
        $this->assertSame('/api/v1/staff/me', route('api.v1.staff.me.show', [], false));
        $this->getJson('/api/v1/staff/profile')->assertNotFound();
    }

    public function test_staff_session_profile_updates_require_csrf(): void
    {
        $staff = $this->createTestUser(['is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        $this->actingAs($staff)->withHeader('Origin', config('app.url'))->withSession(['_token' => 'staff-csrf']);
        $data = ['name' => 'Updated staff', 'phone' => '0912345678'];
        $this->putJson(route('api.v1.staff.me.update'), $data)->assertStatus(419);
        $this->withHeader('X-CSRF-TOKEN', 'staff-csrf')->putJson(route('api.v1.staff.me.update'), $data)->assertOk();
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'name' => 'Updated staff']);
    }

    public function test_non_staff_and_guests_cannot_access_dashboard_or_api(): void
    {
        $this->get(route('staff.dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('api.v1.staff.dashboard.show'))->assertUnauthorized();
        $this->get(route('staff.profile'))->assertRedirect(route('login'));
        $this->getJson(route('api.v1.staff.me.show'))->assertUnauthorized();
        $this->get(route('staff.password'))->assertRedirect(route('login'));
        $this->putJson(route('api.v1.staff.me.password.update'), [])->assertUnauthorized();
        $customer = $this->createTestUser(['is_active' => true]);
        $this->actingAs($customer)->get(route('staff.dashboard'))->assertForbidden()
            ->assertDontSeeText(__('StaffDashboard::staff.tickets.title'));
        $this->getJson(route('api.v1.staff.dashboard.show'))->assertForbidden()->assertJsonMissingPath('data.employee');
        $this->get(route('staff.profile'))->assertForbidden();
        $this->getJson(route('api.v1.staff.me.show'))->assertForbidden();
        $this->putJson(route('api.v1.staff.me.update'), ['name' => 'Rejected', 'phone' => '0912345678'])->assertForbidden();
        $this->getJson(route('api.v1.staff.me.avatar'))->assertForbidden();
        $this->get(route('staff.password'))->assertForbidden();
        $this->putJson(route('api.v1.staff.me.password.update'), [])->assertForbidden();
        $role = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $customer->id]);
        $this->get(route('staff.dashboard'))->assertForbidden();
        $this->getJson(route('api.v1.staff.dashboard.show'))->assertForbidden();
    }
}

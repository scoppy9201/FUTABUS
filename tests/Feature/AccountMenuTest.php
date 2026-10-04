<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_customer_sees_account_menu_and_logout_form(): void
    {
        $user = User::factory()->create(['name' => 'Hung Manh']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Hung Manh')
            ->assertSee('id="account-menu"', false)
            ->assertSee('href="'.route('profile.show').'"', false)
            ->assertSee('href="'.route('profile.tickets.index').'"', false)
            ->assertSee('href="'.route('profile.password.edit').'"', false)
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="post"', false)
            ->assertSee('id="global-confirm-dialog"', false)
            ->assertSee('data-confirm-title="'.__('core::confirm.logout_title').'"', false)
            ->assertSee('data-confirm-message="'.__('core::confirm.logout_message').'"', false)
            ->assertSeeText('Đăng xuất');
    }

    public function test_profile_sidebar_uses_the_same_logout_confirmation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('id="global-confirm-dialog"', false)
            ->assertSee('data-confirm-title="'.__('core::confirm.logout_title').'"', false)
            ->assertSee('action="'.route('logout').'"', false);
    }

    public function test_customer_cannot_open_owner_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_logout_invalidates_session_and_prevents_reusing_authenticated_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['account_menu_test' => 'secret'])
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('account_menu_test');

        $this->assertGuest();
        $this->post(route('logout'))->assertRedirect(route('login'));
    }
}

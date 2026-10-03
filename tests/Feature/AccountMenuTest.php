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
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="post"', false)
            ->assertSeeText('Đăng xuất');
    }

    public function test_dashboard_route_redirects_to_a_real_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('home'));
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

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
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="post"', false)
            ->assertSeeText('Đăng xuất');
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

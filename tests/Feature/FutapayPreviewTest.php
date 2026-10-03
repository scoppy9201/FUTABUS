<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FutapayPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_futapay_preview(): void
    {
        $this->get(route('profile.futapay'))->assertRedirect(route('login'));
    }

    public function test_customer_sees_preview_and_unsupported_feature_notice(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('profile.futapay'))
            ->assertOk()
            ->assertSee('data-notice-on-load', false)
            ->assertSee('data-notice-trigger', false)
            ->assertSee('data-notice-message="'.__('Profile::futapay.notice_message').'"', false)
            ->assertSee('data-futapay-date-range', false)
            ->assertSee('data-range-calendar', false)
            ->assertSee('data-ticket-status-picker', false)
            ->assertSee('data-ticket-status-option="initialized"', false)
            ->assertSee('data-ticket-status-option="pending"', false)
            ->assertSee('data-ticket-status-option="cancelled"', false)
            ->assertSee('data-ticket-status-option="approved"', false)
            ->assertSeeText(__('Profile::futapay.approved'))
            ->assertSee('aria-current="page"', false)
            ->assertSeeText(__('Profile::futapay.history'))
            ->assertSeeText(__('Profile::futapay.empty'));
    }

    public function test_account_menu_links_to_futapay_preview(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('profile.futapay').'"', false);
    }
}

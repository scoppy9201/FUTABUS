<?php

namespace Tests\Feature\Payment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopupGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_footer_opens_the_public_topup_guide(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('topup-guide').'"', false);

        $this->get(route('topup-guide'))
            ->assertOk()
            ->assertSee('Hướng dẫn nạp tiền trên ứng dụng FUTAPay')
            ->assertSee('images/booking-guide/futa-logo.png', false)
            ->assertSee('images/booking-guide/futa-app-qr.png', false)
            ->assertSee('images/topup-guide/sign-in-with-phone.png', false)
            ->assertSee('images/topup-guide/select-top-up.png', false)
            ->assertSee('images/topup-guide/enter-amount-and-payment-source.png', false)
            ->assertSee('Bước 4: Nhập số tiền, chọn nguồn tiền và xác nhận');

        foreach (['sign-in-with-phone', 'select-top-up', 'enter-amount-and-payment-source'] as $image) {
            $this->assertFileExists(public_path("images/topup-guide/{$image}.png"));
        }
    }

    public function test_topup_guide_has_english_copy(): void
    {
        $this->get(route('topup-guide', ['lang' => 'en']))
            ->assertOk()
            ->assertSee('How to top up with the FUTAPay app');
    }
}

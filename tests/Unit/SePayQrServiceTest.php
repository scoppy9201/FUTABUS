<?php

namespace Tests\Unit;

use FuteBus\Core\Services\SePayQrService;
use Tests\TestCase;

class SePayQrServiceTest extends TestCase
{
    public function test_it_does_not_generate_a_payment_qr_without_a_receiving_account(): void
    {
        config()->set('services.sepay.enabled', false);

        $this->assertNull(app(SePayQrService::class)->generate(300000, 'FUTA123'));
    }

    public function test_it_generates_a_sepay_qr_url_with_the_booking_amount_and_reference(): void
    {
        config()->set('services.sepay', [
            'enabled'    => true,
            'bank'       => 'VCB',
            'account_no' => '1234567890',
        ]);

        $url = app(SePayQrService::class)->generate(300000, 'FUTA-123');
        $this->assertSame('https://vietqr.app/img', parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).parse_url($url, PHP_URL_PATH));
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame([
            'acc'      => '1234567890',
            'bank'     => 'VCB',
            'amount'   => '300000',
            'des'      => 'FUTA123',
            'template' => 'compact',
            'showinfo' => 'true',
        ], $query);
    }
}

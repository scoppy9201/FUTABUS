<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Illuminate\Http\Request;

class SePayWebhookAuthenticator
{
    public function configured(): bool
    {
        return match (config('services.sepay.webhook_auth', 'hmac')) {
            'hmac'    => filled(config('services.sepay.webhook_secret')),
            'api_key' => filled(config('services.sepay.webhook_key')),
            default   => false,
        };
    }

    public function verify(Request $request): bool
    {
        if (! $this->configured()) {
            return false;
        }

        if (config('services.sepay.webhook_auth', 'hmac') === 'api_key') {
            return hash_equals(
                'Apikey '.config('services.sepay.webhook_key'),
                (string) $request->header('Authorization')
            );
        }

        $timestamp = (string) $request->header('X-SePay-Timestamp', '');
        $signature = (string) $request->header('X-SePay-Signature', '');
        if (! preg_match('/^[0-9]{10,12}$/', $timestamp)
            || abs(now()->timestamp - (int) $timestamp) > 300
            || ! preg_match('/^sha256=[a-f0-9]{64}$/', $signature)) {
            return false;
        }

        $expected = 'sha256='.hash_hmac(
            'sha256',
            $timestamp.'.'.$request->getContent(),
            (string) config('services.sepay.webhook_secret')
        );

        return hash_equals($expected, $signature);
    }
}

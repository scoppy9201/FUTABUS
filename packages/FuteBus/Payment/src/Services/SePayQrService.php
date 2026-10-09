<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

class SePayQrService
{
    public function configured(): bool
    {
        $settings = config('services.sepay');

        return (bool) ($settings['enabled'] ?? false)
            && preg_match('/^[A-Za-z0-9]{2,20}$/', (string) ($settings['bank'] ?? '')) === 1
            && preg_match('/^[A-Za-z0-9]{1,19}$/', (string) ($settings['account_no'] ?? '')) === 1;
    }

    public function generate(int $amount, string $reference): ?string
    {
        if (! $this->configured() || $amount <= 0) {
            return null;
        }

        $settings = config('services.sepay');
        $description = preg_replace('/[^A-Za-z0-9]/', '', $reference);
        if ($description === '') {
            return null;
        }

        return 'https://vietqr.app/img?'.http_build_query([
            'acc'      => $settings['account_no'],
            'bank'     => $settings['bank'],
            'amount'   => $amount,
            'des'      => substr($description, 0, 25),
            'template' => 'compact',
            'showinfo' => 'true',
        ], '', '&', PHP_QUERY_RFC3986);
    }
}

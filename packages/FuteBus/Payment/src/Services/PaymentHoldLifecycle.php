<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Payment\Models\SePayPaymentIntent;
use Illuminate\Support\Facades\DB;

class PaymentHoldLifecycle
{
    public function release(string $token, bool $confirmedExit = false): ?string
    {
        return DB::transaction(function () use ($token, $confirmedExit): ?string {
            $intent = SePayPaymentIntent::where('token', $token)->lockForUpdate()->first();
            if ($intent === null) {
                return null;
            }
            if ($intent->status === 'pending' && ($confirmedExit || $intent->expires_at->lte(now()))) {
                $intent->update(['status' => 'expired']);
            }

            return $intent->status;
        });
    }
}

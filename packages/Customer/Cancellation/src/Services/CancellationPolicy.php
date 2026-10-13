<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CancellationPolicy
{
    public const VERSION = 'customer-mvp-2026-10-13';

    public function quote(object $ticket): array
    {
        $seconds = now()->diffInSeconds(Carbon::parse($ticket->departure_time), false);
        if ($ticket->status !== 'active' || $ticket->booking_status !== 'confirmed'
            || $ticket->trip_status !== 'scheduled' || $seconds < 4 * 3600
            || str_starts_with($ticket->booking_code, 'DEMOHIST')) {
            throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.unavailable')]);
        }
        $amount = (int) $ticket->price;
        if ($amount <= 0 || (float) $ticket->price !== (float) $amount) {
            throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.payment_unavailable')]);
        }
        $percent = $seconds > 24 * 3600 ? 10 : 30;
        $fee = intdiv($amount * $percent + 99, 100);

        return ['amount'     => $amount, 'fee_percent' => $percent, 'fee' => $fee, 'refund' => $amount - $fee,
            'policy_version' => self::VERSION];
    }
}

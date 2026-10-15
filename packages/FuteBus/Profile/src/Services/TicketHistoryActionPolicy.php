<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use Illuminate\Support\Carbon;

class TicketHistoryActionPolicy
{
    public function canContactForChange(object $booking): bool
    {
        return $booking->status === 'confirmed'
            && ($booking->exchange_eligible_count ?? 1) > 0
            && $booking->payment_status === 'completed'
            && ! str_starts_with($booking->booking_code, 'DEMOHIST')
            && $booking->trip_status === 'scheduled'
            && Carbon::parse($booking->departure_time)->greaterThanOrEqualTo(now()->addHours(4));
    }
}

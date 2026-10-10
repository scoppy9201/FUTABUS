<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use Illuminate\Support\Carbon;

class TicketHistoryActionPolicy
{
    public function canContactForChange(object $booking): bool
    {
        return $booking->status === 'confirmed'
            && $booking->payment_status === 'completed'
            && ! str_starts_with($booking->booking_code, 'DEMOHIST')
            && Carbon::parse($booking->departure_time)->greaterThan(now()->addDay());
    }
}

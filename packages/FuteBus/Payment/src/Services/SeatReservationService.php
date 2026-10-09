<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Illuminate\Support\Facades\DB;

class SeatReservationService
{
    public function releaseCancelled(int $tripId, array $seatIds): void
    {
        DB::table('booked_seats')
            ->where('trip_id', $tripId)
            ->whereIn('seat_layout_id', $seatIds)
            ->whereIn('booking_id', DB::table('bookings')->where('status', 'cancelled')->select('id'))
            ->delete();
    }
}

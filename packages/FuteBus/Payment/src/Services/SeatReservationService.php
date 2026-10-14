<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;

class SeatReservationService
{
    public function releaseCancelled(int $tripId, array $seatIds): void
    {
        BookedSeatRecord::query()->toBase()
            ->where('trip_id', $tripId)
            ->whereIn('seat_layout_id', $seatIds)
            ->whereIn('booking_id', BookingRecord::query()->toBase()->where('status', 'cancelled')->select('id'))
            ->delete();
    }
}

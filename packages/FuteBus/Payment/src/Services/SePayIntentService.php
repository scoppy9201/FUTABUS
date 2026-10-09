<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SePayIntentService
{
    public function __construct(private readonly SeatReservationService $seats) {}

    public function createIntent(array $preview): void
    {
        DB::transaction(function () use ($preview): void {
            $tripId = (int) $preview['trip']['id'];
            $trip = DB::table('trips')->where('id', $tripId)->lockForUpdate()->first();
            $seatIds = array_values(array_unique(array_map('intval', $preview['seat_ids'])));

            if ($trip === null || $trip->status !== 'scheduled'
                || $trip->departure_time <= now()->toDateTimeString()
                || (int) $trip->price !== (int) $preview['trip']['fare']
                || count($seatIds) !== count($preview['seat_ids'])) {
                $this->seatsUnavailable();
            }

            DB::table('sepay_payment_intents')
                ->where('trip_id', $tripId)
                ->where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired', 'updated_at' => now()]);

            $this->seats->releaseCancelled($tripId, $seatIds);

            $validSeats = DB::table('seat_layouts')
                ->where('bus_id', $trip->bus_id)
                ->whereIn('id', $seatIds)
                ->where('is_available', true)
                ->count();
            $booked = DB::table('booked_seats')
                ->join('bookings', 'bookings.id', '=', 'booked_seats.booking_id')
                ->where('booked_seats.trip_id', $tripId)
                ->whereIn('booked_seats.seat_layout_id', $seatIds)
                ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])
                ->exists();
            $reserved = DB::table('sepay_payment_intents')
                ->where('trip_id', $tripId)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->get(['seat_ids'])
                ->contains(function ($intent) use ($seatIds): bool {
                    $held = array_map('intval', json_decode($intent->seat_ids, true) ?: []);

                    return count(array_intersect($seatIds, $held)) > 0;
                });

            if ($validSeats !== count($seatIds) || $booked || $reserved) {
                $this->seatsUnavailable();
            }

            DB::table('sepay_payment_intents')->insert([
                'token'      => $preview['token'],
                'code'       => 'FUTA'.strtoupper(bin2hex(random_bytes(5))),
                'trip_id'    => $tripId,
                'seat_ids'   => json_encode($seatIds),
                'snapshot'   => json_encode($preview, JSON_UNESCAPED_UNICODE),
                'amount'     => count($seatIds) * (int) $trip->price,
                'status'     => 'pending',
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function seatsUnavailable(): never
    {
        throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\SeatManagement\Models\SeatLayout as SeatLayoutRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SePayIntentService
{
    public function __construct(private readonly SeatReservationService $seats) {}

    public function createIntent(array $preview): void
    {
        DB::transaction(function () use ($preview): void {
            $legs = [[
                'trip'     => $preview['trip'],
                'seat_ids' => $preview['seat_ids'],
            ]];
            if (isset($preview['outbound_trip'])) {
                $legs[] = [
                    'trip'     => $preview['outbound_trip'],
                    'seat_ids' => $preview['outbound_seat_ids'],
                ];
            }
            usort($legs, fn (array $a, array $b): int => $a['trip']['id'] <=> $b['trip']['id']);

            $amount = 0;
            foreach ($legs as $leg) {
                $tripId = (int) $leg['trip']['id'];
                $seatIds = array_values(array_unique(array_map('intval', $leg['seat_ids'])));
                $trip = TripRecord::query()->toBase()->where('id', $tripId)->lockForUpdate()->first();
                if ($trip === null || $trip->status !== 'scheduled'
                    || $trip->departure_time <= now()->toDateTimeString()
                    || (int) $trip->price !== (int) $leg['trip']['fare']
                    || count($seatIds) !== count($leg['seat_ids'])) {
                    $this->seatsUnavailable();
                }

                SePayPaymentIntentRecord::query()->toBase()
                    ->where(fn ($query) => $query->where('trip_id', $tripId)->orWhere('secondary_trip_id', $tripId))
                    ->where('status', 'pending')
                    ->where('expires_at', '<=', now())
                    ->update(['status' => 'expired', 'updated_at' => now()]);

                $this->seats->releaseCancelled($tripId, $seatIds);
                $validSeats = SeatLayoutRecord::query()->toBase()->where('bus_id', $trip->bus_id)
                    ->whereIn('id', $seatIds)->where('is_available', true)->count();
                $booked = BookedSeatRecord::query()->toBase()
                    ->join('bookings', 'bookings.id', '=', 'booked_seats.booking_id')
                    ->where('booked_seats.trip_id', $tripId)
                    ->whereIn('booked_seats.seat_layout_id', $seatIds)
                    ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])->exists();
                $reserved = SePayPaymentIntentRecord::query()->toBase()
                    ->where(fn ($query) => $query->where('trip_id', $tripId)->orWhere('secondary_trip_id', $tripId))
                    ->where('status', 'pending')->where('expires_at', '>', now())
                    ->get(['trip_id', 'seat_ids', 'secondary_trip_id', 'secondary_seat_ids'])
                    ->contains(function (object $intent) use ($tripId, $seatIds): bool {
                        $held = (int) $intent->trip_id === $tripId ? $intent->seat_ids : $intent->secondary_seat_ids;

                        return count(array_intersect($seatIds, array_map('intval', json_decode($held, true) ?: []))) > 0;
                    });
                if ($validSeats !== count($seatIds) || $booked || $reserved
                    || app(ExchangeSeatHolds::class)->conflicts($tripId, $seatIds)) {
                    $this->seatsUnavailable();
                }
                $amount += count($seatIds) * (int) $trip->price;
            }

            SePayPaymentIntentRecord::query()->toBase()->insert([
                'token'              => $preview['token'],
                'code'               => 'FUTA'.strtoupper(bin2hex(random_bytes(5))),
                'trip_id'            => $preview['trip']['id'],
                'seat_ids'           => json_encode($preview['seat_ids']),
                'secondary_trip_id'  => $preview['outbound_trip']['id'] ?? null,
                'secondary_seat_ids' => isset($preview['outbound_seat_ids']) ? json_encode($preview['outbound_seat_ids']) : null,
                'snapshot'           => json_encode($preview, JSON_UNESCAPED_UNICODE),
                'amount'             => $amount,
                'status'             => 'pending',
                'expires_at'         => now()->addMinutes(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        });
    }

    private function seatsUnavailable(): never
    {
        throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
    }
}

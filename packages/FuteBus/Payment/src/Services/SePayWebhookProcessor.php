<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\Payment\Models\Payment as PaymentRecord;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\Payment\Models\SePayTransaction as SePayTransactionRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Support\Facades\DB;

class SePayWebhookProcessor
{
    public function __construct(
        private readonly SeatReservationService $seats,
        private readonly TicketIssuer $issuer,
    ) {}

    public function receive(array $event): void
    {
        DB::transaction(function () use ($event): void {
            $transactionId = (int) $event['id'];
            $code = $this->paymentCode($event);
            $inserted = SePayTransactionRecord::query()->toBase()->insertOrIgnore([
                'sepay_id'       => $transactionId,
                'environment'    => config('services.sepay.environment', 'live'),
                'code'           => $code,
                'account_number' => $event['accountNumber'] ?? null,
                'amount'         => $event['transferAmount'],
                'status'         => 'received',
                'payload'        => json_encode($event, JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            if ($inserted === 0) {
                $transaction = SePayTransactionRecord::query()->toBase()
                    ->where('sepay_id', $transactionId)->lockForUpdate()->first();
                if ($transaction?->status !== 'unmatched') {
                    return;
                }
                $event = json_decode($transaction->payload, true);
                $code = $this->paymentCode($event);
                SePayTransactionRecord::query()->toBase()->where('sepay_id', $transactionId)
                    ->update(['code' => $code, 'updated_at' => now()]);
            }

            $code = (string) $code;
            $intent = SePayPaymentIntentRecord::query()->toBase()->where('code', $code)->first();
            $trip = null;
            $secondaryTrip = null;
            if ($intent !== null) {
                $tripIds = array_filter([(int) $intent->trip_id, (int) $intent->secondary_trip_id]);
                sort($tripIds);
                $lockedTrips = [];
                foreach ($tripIds as $tripId) {
                    $lockedTrips[$tripId] = TripRecord::query()->toBase()->where('id', $tripId)->lockForUpdate()->first();
                }
                $trip = $lockedTrips[(int) $intent->trip_id] ?? null;
                $secondaryTrip = $lockedTrips[(int) $intent->secondary_trip_id] ?? null;
                $intent = SePayPaymentIntentRecord::query()->toBase()->where('id', $intent->id)->lockForUpdate()->first();
            }
            if ($event['transferType'] !== 'in'
                || ! hash_equals((string) config('services.sepay.account_no'), (string) ($event['accountNumber'] ?? ''))
                || $intent === null) {
                $this->markTransaction($transactionId, $intent?->id, 'unmatched');

                return;
            }

            if ($intent->status === 'paid') {
                $this->markTransaction($transactionId, $intent->id, 'extra_payment');

                return;
            }

            if ($intent->status !== 'pending' || $intent->expires_at <= now()->toDateTimeString()
                || (int) $intent->amount !== (int) $event['transferAmount']) {
                SePayPaymentIntentRecord::query()->toBase()->where('id', $intent->id)
                    ->update(['status' => 'needs_review', 'updated_at' => now()]);
                $this->markTransaction($transactionId, $intent->id, 'needs_review');

                return;
            }

            $preview = json_decode($intent->snapshot, true);
            $seatIds = array_map('intval', json_decode($intent->seat_ids, true));
            $secondarySeatIds = $intent->secondary_trip_id === null
                ? [] : array_map('intval', json_decode($intent->secondary_seat_ids, true) ?: []);
            $this->seats->releaseCancelled((int) $intent->trip_id, $seatIds);
            if ($intent->secondary_trip_id !== null) {
                $this->seats->releaseCancelled((int) $intent->secondary_trip_id, $secondarySeatIds);
            }
            $booked = BookedSeatRecord::query()->toBase()
                ->where('trip_id', $intent->trip_id)
                ->whereIn('seat_layout_id', $seatIds)
                ->exists();
            $secondaryBooked = $intent->secondary_trip_id !== null && BookedSeatRecord::query()->toBase()
                ->where('trip_id', $intent->secondary_trip_id)
                ->whereIn('seat_layout_id', $secondarySeatIds)
                ->exists();
            if ($trip === null || $trip->status !== 'scheduled'
                || $trip->departure_time <= now()->toDateTimeString() || $booked
                || ($intent->secondary_trip_id !== null && ($secondaryTrip === null
                    || $secondaryTrip->status !== 'scheduled'
                    || $secondaryTrip->departure_time <= now()->toDateTimeString()
                    || $secondaryBooked))) {
                SePayPaymentIntentRecord::query()->toBase()->where('id', $intent->id)
                    ->update(['status' => 'needs_review', 'updated_at' => now()]);
                $this->markTransaction($transactionId, $intent->id, 'needs_review');

                return;
            }

            $bookingId = $this->issuer->issue($intent, $preview, $seatIds);
            $secondaryBookingId = $intent->secondary_trip_id === null ? null
                : $this->issuer->issue($intent, $preview, $secondarySeatIds, true);

            PaymentRecord::query()->toBase()->insert([
                'payment_code'   => $intent->code,
                'booking_id'     => $bookingId,
                'amount'         => $intent->amount,
                'method'         => 'bank_transfer',
                'status'         => 'completed',
                'transaction_id' => (string) $transactionId,
                'payload'        => json_encode($event, JSON_UNESCAPED_UNICODE),
                'paid_at'        => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            SePayPaymentIntentRecord::query()->toBase()->where('id', $intent->id)->update([
                'status'               => 'paid',
                'booking_id'           => $bookingId,
                'secondary_booking_id' => $secondaryBookingId,
                'paid_at'              => now(),
                'updated_at'           => now(),
            ]);
            $this->markTransaction($transactionId, $intent->id, 'matched');
        });
    }

    private function paymentCode(array $event): ?string
    {
        $code = strtoupper(trim((string) ($event['code'] ?? '')));
        if ($code !== '') {
            return $code;
        }

        if (preg_match('/(?<![A-Z0-9])FUTA[A-F0-9]{10}(?![A-Z0-9])/i',
            (string) ($event['content'] ?? ''), $matches) === 1) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    private function markTransaction(int $id, ?int $intentId, string $status): void
    {
        SePayTransactionRecord::query()->toBase()->where('sepay_id', $id)->update([
            'payment_intent_id' => $intentId,
            'status'            => $status,
            'updated_at'        => now(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

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
            $inserted = DB::table('sepay_transactions')->insertOrIgnore([
                'sepay_id'       => $transactionId,
                'code'           => $event['code'] ?? null,
                'account_number' => $event['accountNumber'] ?? null,
                'amount'         => $event['transferAmount'],
                'status'         => 'received',
                'payload'        => json_encode($event, JSON_UNESCAPED_UNICODE),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            if ($inserted === 0) {
                return;
            }

            $code = (string) ($event['code'] ?? '');
            $intent = DB::table('sepay_payment_intents')->where('code', $code)->first();
            $trip = null;
            if ($intent !== null) {
                $trip = DB::table('trips')->where('id', $intent->trip_id)->lockForUpdate()->first();
                $intent = DB::table('sepay_payment_intents')->where('id', $intent->id)->lockForUpdate()->first();
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
                DB::table('sepay_payment_intents')->where('id', $intent->id)
                    ->update(['status' => 'needs_review', 'updated_at' => now()]);
                $this->markTransaction($transactionId, $intent->id, 'needs_review');

                return;
            }

            $preview = json_decode($intent->snapshot, true);
            $seatIds = array_map('intval', json_decode($intent->seat_ids, true));
            $this->seats->releaseCancelled((int) $intent->trip_id, $seatIds);
            $booked = DB::table('booked_seats')
                ->where('trip_id', $intent->trip_id)
                ->whereIn('seat_layout_id', $seatIds)
                ->exists();
            if ($trip === null || $trip->status !== 'scheduled'
                || $trip->departure_time <= now()->toDateTimeString() || $booked) {
                DB::table('sepay_payment_intents')->where('id', $intent->id)
                    ->update(['status' => 'needs_review', 'updated_at' => now()]);
                $this->markTransaction($transactionId, $intent->id, 'needs_review');

                return;
            }

            $bookingId = $this->issuer->issue($intent, $preview, $seatIds);

            DB::table('payments')->insert([
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
            DB::table('sepay_payment_intents')->where('id', $intent->id)->update([
                'status'     => 'paid',
                'booking_id' => $bookingId,
                'paid_at'    => now(),
                'updated_at' => now(),
            ]);
            $this->markTransaction($transactionId, $intent->id, 'matched');
        });
    }

    private function markTransaction(int $id, ?int $intentId, string $status): void
    {
        DB::table('sepay_transactions')->where('sepay_id', $id)->update([
            'payment_intent_id' => $intentId,
            'status'            => $status,
            'updated_at'        => now(),
        ]);
    }
}

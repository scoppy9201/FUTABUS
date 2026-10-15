<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Payment\Models\Payment;
use FuteBus\Payment\Models\SePayTransaction;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingPaymentResolver
{
    public function constrain(Builder $query, int|string $booking, bool $column = false): Builder
    {
        return $query->where(function (Builder $scope) use ($booking, $column): void {
            $column ? $scope->whereColumn('payments.booking_id', $booking) : $scope->where('payments.booking_id', $booking);
            $scope->orWhereExists(function (Builder $intents) use ($booking, $column): void {
                $intents->selectRaw('1')->from('sepay_payment_intents')
                    ->whereColumn('sepay_payment_intents.code', 'payments.payment_code')
                    ->whereColumn('sepay_payment_intents.booking_id', 'payments.booking_id')->where('status', 'paid');
                $column ? $intents->whereColumn('secondary_booking_id', $booking) : $intents->where('secondary_booking_id', $booking);
            })->orWhereExists(function (Builder $exchanges) use ($booking, $column): void {
                $exchanges->selectRaw('1')->from('ticket_exchanges')->where('status', 'completed')
                    ->whereColumn('ticket_exchanges.payment_id', 'payments.id');
                $column ? $exchanges->whereColumn('new_booking_id', $booking) : $exchanges->where('new_booking_id', $booking);
            })->orWhere(function (Builder $cash) use ($booking, $column): void {
                $cash->where('payments.method', 'cash')->where('payments.payment_code', 'like', 'CASH%')
                    ->whereJsonContains('payments.payload->booking_ids', $column ? DB::raw($booking) : (int) $booking);
            });
        });
    }

    public function verified(int $booking, bool $lock = false): object
    {
        $query = $this->constrain(Payment::query()->toBase(), $booking)->orderByDesc('id');
        $payment = ($lock ? $query->lockForUpdate() : $query)->first();
        $payload = json_decode($payment->payload ?? '', true);
        if ($payment?->status === 'completed' && $payment->method === 'cash' && str_starts_with($payment->payment_code, 'CASH')
            && ! empty($payload['operator_id']) && $payment->paid_at && (float) $payment->amount === (float) (int) $payment->amount) {
            $this->assertTicketBudget($payment);

            return $payment;
        }
        $receiptQuery = $payment ? SePayTransaction::query()->toBase()->where('sepay_id', $payment->transaction_id) : null;
        $receipt = $receiptQuery ? ($lock ? $receiptQuery->lockForUpdate() : $receiptQuery)->first() : null;
        if ($payment === null || $payment->status !== 'completed' || $payment->method !== 'bank_transfer'
            || ! ctype_digit((string) $payment->transaction_id) || $receipt?->status !== 'matched'
            || (int) $receipt->amount !== (int) $payment->amount || (float) $payment->amount !== (float) (int) $payment->amount
            || $receipt->code !== $payment->payment_code) {
            throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.payment_unavailable')]);
        }

        $this->assertTicketBudget($payment);

        return $payment;
    }

    private function assertTicketBudget(object $payment): void
    {
        $payload = json_decode($payment->payload ?? '', true) ?: [];
        $bookings = collect([$payment->booking_id])->merge($payment->method === 'cash' ? ($payload['booking_ids'] ?? []) : [])
            ->merge(DB::table('sepay_payment_intents')->where('booking_id', $payment->booking_id)
                ->where('code', $payment->payment_code)->where('status', 'paid')->pluck('secondary_booking_id'))->filter()->unique();
        if ((int) DB::table('tickets')->whereIn('booking_id', $bookings)->sum('price') > (int) $payment->amount) {
            throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.payment_unavailable')]);
        }
    }
}

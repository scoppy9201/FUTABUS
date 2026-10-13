<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use FuteBus\Payment\Notifications\TicketRefundCompleted;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualRefundService
{
    public function visible(User $actor): Builder
    {
        abort_unless($actor->is_active && $actor->hasPermissionTo('payment.view'), 403);
        $query = DB::table('ticket_refunds')->join('ticket_cancellations', 'ticket_cancellations.id', '=', 'ticket_refunds.cancellation_id')
            ->join('tickets', 'tickets.id', '=', 'ticket_cancellations.ticket_id')
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')
            ->join('payments', 'payments.id', '=', 'ticket_refunds.payment_id')
            ->join('users as customer', 'customer.id', '=', 'ticket_cancellations.user_id')
            ->leftJoin('users as handler', 'handler.id', '=', 'ticket_refunds.assigned_to')
            ->select('ticket_refunds.*', 'ticket_cancellations.reference', 'ticket_cancellations.fee_amount',
                'ticket_cancellations.reason', 'ticket_cancellations.note as customer_note',
                'ticket_cancellations.created_at as cancelled_at', 'ticket_cancellations.ticket_id',
                'ticket_cancellations.user_id as customer_user_id',
                'tickets.ticket_code', 'tickets.seat_code', 'bookings.booking_code', 'bookings.id as booking_id',
                'payments.payment_code', 'payments.amount as original_amount', 'customer.name as customer_name',
                'handler.name as handler_name');
        if (! $actor->isAdmin()) {
            $company = $actor->bus_company_id ?: DB::table('bus_companies')->whereRaw('LOWER(email) = ?', [mb_strtolower($actor->email)])->value('id');
            abort_if(! $company, 403);
            $query->where('trips.bus_company_id', $company);
        }

        return $query;
    }

    public function change(User $actor, int $id, array $input): object
    {
        abort_unless($actor->is_active && $actor->hasPermissionTo('payment.refund'), 403);
        try {
            return DB::transaction(function () use ($actor, $id, $input): object {
                $current = DB::table('ticket_refunds')->where('id', $id)->lockForUpdate()->first();
                abort_if($current === null, 404);
                $record = $this->visible($actor)->where('ticket_refunds.id', $id)->first();
                abort_if($record === null, 404);
                abort_unless($current->processing_mode === 'manual', 409, __('Payment::refunds.automatic_blocked'));
                $target = match ($input['action']) {
                    'claim' => 'refund_processing', 'complete' => 'refunded', 'fail' => 'refund_failed',
                };
                if ($current->status === 'refunded' && $target === 'refunded'
                    && (int) $current->assigned_to === $actor->id && $current->bank_reference === strtoupper(trim($input['bank_reference'] ?? ''))
                    && (int) $current->confirmed_amount === (int) ($input['amount'] ?? 0)) {
                    return $current;
                }
                abort_unless((int) $current->version === (int) $input['version'], 409, __('Payment::refunds.stale'));
                $changes = $this->transition($actor, $record, $input, $target);
                $changes['version'] = $current->version + 1;
                $changes['updated_at'] = now();
                DB::table('ticket_refunds')->where('id', $id)->update($changes);
                DB::table('ticket_refund_events')->insert([
                    'refund_id'      => $id, 'actor_id' => $actor->id, 'version' => $changes['version'],
                    'from_status'    => $current->status, 'to_status' => $target, 'note' => $input['note'] ?? null,
                    'amount'         => $changes['confirmed_amount'] ?? null,
                    'bank_reference' => $changes['bank_reference'] ?? null, 'bank_paid_at' => $changes['bank_paid_at'] ?? null,
                    'created_at'     => now(),
                ]);
                if ($target === 'refunded') {
                    DB::table('tickets')->where('id', $record->ticket_id)->where('status', 'cancelled')
                        ->update(['status' => 'refunded', 'updated_at' => now()]);
                    User::findOrFail($record->customer_user_id)->notify(new TicketRefundCompleted([
                        'reference' => $record->reference, 'amount' => (int) $record->amount, 'status' => 'refunded',
                    ]));
                }

                return DB::table('ticket_refunds')->where('id', $id)->first();
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['bank_reference' => __('Payment::refunds.duplicate_reference')]);
        }
    }

    private function transition(User $actor, object $record, array $input, string $target): array
    {
        if ($target === 'refund_processing') {
            abort_unless(in_array($record->status, ['refund_pending', 'refund_failed'], true), 409, __('Payment::refunds.claimed'));

            return ['status' => $target, 'assigned_to' => $actor->id, 'error_code' => null];
        }
        abort_unless($record->status === 'refund_processing' && (int) $record->assigned_to === $actor->id, 409, __('Payment::refunds.claimed'));
        if ($target === 'refund_failed') {
            return ['status' => $target, 'staff_note' => $input['note']];
        }
        if (! filter_var($input['bank_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || (int) $input['amount'] !== (int) $record->amount
            || Carbon::parse($input['bank_paid_at'])->lt(Carbon::parse($record->cancelled_at))) {
            throw ValidationException::withMessages(['amount' => __('Payment::refunds.invalid_settlement')]);
        }
        abort_unless(DB::table('tickets')->where('id', $record->ticket_id)->value('status') === 'cancelled', 409);

        return ['status'     => $target, 'confirmed_amount' => (int) $input['amount'],
            'bank_reference' => strtoupper(trim($input['bank_reference'])), 'bank_paid_at' => Carbon::parse($input['bank_paid_at']),
            'succeeded_at'   => now(), 'staff_note' => $input['note'] ?? null];
    }
}

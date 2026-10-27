<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class StaffCashRefundService
{
    public function complete(User $actor, int $cancellation): void
    {
        abort_unless($actor->is_active && $actor->hasPermissionTo('payment.refund'), 403);
        $refund = DB::table('ticket_refunds')->where('cancellation_id', $cancellation)->lockForUpdate()->first();
        $record = DB::table('ticket_cancellations')->where('id', $cancellation)->first();
        abort_unless((int) $record->actor_id === $actor->id, 403);
        if ($refund->status === 'refunded') {
            return;
        }
        abort_unless($refund->staff_note === 'staff-cash-refund-selected', 403);
        abort_unless($refund->status === 'refund_pending', 409);
        $reference = 'CASH-REFUND-'.$record->reference;
        DB::table('ticket_refunds')->where('id', $refund->id)->update([
            'status'         => 'refunded', 'assigned_to' => $actor->id, 'version' => 1,
            'bank_reference' => $reference, 'bank_paid_at' => now(), 'confirmed_amount' => $refund->amount,
            'staff_note'     => __('StaffTicketProcessing::cancellation.cash_recorded'),
            'succeeded_at'   => now(), 'updated_at' => now(),
        ]);
        DB::table('ticket_refund_events')->insert([
            'refund_id'      => $refund->id, 'actor_id' => $actor->id, 'version' => 1, 'action' => 'cash_refund',
            'from_status'    => 'refund_pending', 'to_status' => 'refunded', 'amount' => $refund->amount,
            'bank_reference' => $reference, 'bank_paid_at' => now(), 'created_at' => now(),
            'note'           => __('StaffTicketProcessing::cancellation.cash_recorded'),
        ]);
        DB::table('tickets')->where('id', $record->ticket_id)->update(['status' => 'refunded', 'updated_at' => now()]);
        app(TicketEmailService::class)->refundCompleted((int) $refund->id);
    }
}

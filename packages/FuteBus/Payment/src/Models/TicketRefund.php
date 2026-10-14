<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use App\Models\User;
use FuteBus\Cancellation\Models\TicketCancellation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketRefund extends Model
{
    protected $table = 'ticket_refunds';

    protected $fillable = [
        'cancellation_id',
        'payment_id',
        'transaction_id',
        'idempotency_key',
        'amount',
        'environment',
        'status',
        'provider_refund_id',
        'error_code',
        'succeeded_at',
        'processing_mode',
        'assigned_to',
        'version',
        'bank_reference',
        'bank_paid_at',
        'confirmed_amount',
        'staff_note',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return [
            'cancellation_id'  => 'integer',
            'payment_id'       => 'integer',
            'amount'           => 'integer',
            'succeeded_at'     => 'datetime',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
            'assigned_to'      => 'integer',
            'version'          => 'integer',
            'bank_paid_at'     => 'datetime',
            'confirmed_amount' => 'integer',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(TicketCancellation::class, 'cancellation_id', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function ticketRefundEvents(): HasMany
    {
        return $this->hasMany(TicketRefundEvent::class, 'refund_id', 'id');
    }
}

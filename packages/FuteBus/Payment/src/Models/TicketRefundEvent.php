<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRefundEvent extends Model
{
    protected $table = 'ticket_refund_events';

    public const UPDATED_AT = null;

    protected $fillable = [
        'refund_id',
        'actor_id',
        'action',
        'previous_assigned_to',
        'assigned_to',
        'version',
        'from_status',
        'to_status',
        'note',
        'amount',
        'bank_reference',
        'bank_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_id'            => 'integer',
            'actor_id'             => 'integer',
            'previous_assigned_to' => 'integer',
            'assigned_to'          => 'integer',
            'version'              => 'integer',
            'amount'               => 'integer',
            'bank_paid_at'         => 'datetime',
            'created_at'           => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(TicketRefund::class, 'refund_id', 'id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function previousAssignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_assigned_to', 'id');
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Models;

use App\Models\User;
use FuteBus\Payment\Models\TicketRefund;
use FuteBus\TicketManagement\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TicketCancellation extends Model
{
    protected $table = 'ticket_cancellations';

    protected $fillable = [
        'reference',
        'ticket_id',
        'user_id',
        'reason',
        'note',
        'ticket_amount',
        'fee_percent',
        'fee_amount',
        'refund_amount',
        'policy_version',
        'departure_at',
    ];

    protected function casts(): array
    {
        return [
            'ticket_id'     => 'integer',
            'user_id'       => 'integer',
            'ticket_amount' => 'integer',
            'fee_percent'   => 'integer',
            'fee_amount'    => 'integer',
            'refund_amount' => 'integer',
            'departure_at'  => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ticketRefund(): HasOne
    {
        return $this->hasOne(TicketRefund::class, 'cancellation_id', 'id');
    }
}

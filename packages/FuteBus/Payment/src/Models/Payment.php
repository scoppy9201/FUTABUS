<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use FuteBus\BookingManagement\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'payment_code',
        'booking_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'payload',
        'paid_at',
    ];

    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'amount'     => 'decimal:2',
            'payload'    => 'array',
            'paid_at'    => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function ticketRefunds(): HasMany
    {
        return $this->hasMany(TicketRefund::class, 'payment_id', 'id');
    }

    public function ticketExchanges(): HasMany
    {
        return $this->hasMany(TicketExchange::class, 'payment_id', 'id');
    }
}

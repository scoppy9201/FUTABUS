<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use FuteBus\BookingManagement\Models\Booking;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SePayPaymentIntent extends Model
{
    protected $table = 'sepay_payment_intents';

    protected $fillable = [
        'token',
        'code',
        'trip_id',
        'seat_ids',
        'snapshot',
        'amount',
        'status',
        'booking_id',
        'expires_at',
        'paid_at',
        'secondary_trip_id',
        'secondary_seat_ids',
        'secondary_booking_id',
    ];

    protected $hidden = ['token', 'snapshot'];

    protected function casts(): array
    {
        return [
            'trip_id'              => 'integer',
            'seat_ids'             => 'array',
            'snapshot'             => 'array',
            'amount'               => 'decimal:2',
            'booking_id'           => 'integer',
            'expires_at'           => 'datetime',
            'paid_at'              => 'datetime',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
            'secondary_trip_id'    => 'integer',
            'secondary_seat_ids'   => 'array',
            'secondary_booking_id' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function secondaryBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'secondary_booking_id', 'id');
    }

    public function secondaryTrip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'secondary_trip_id', 'id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }

    public function sepayTransactions(): HasMany
    {
        return $this->hasMany(SePayTransaction::class, 'payment_intent_id', 'id');
    }
}

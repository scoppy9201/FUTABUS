<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Models;

use FuteBus\BookingManagement\Models\Booking;
use FuteBus\Cancellation\Models\TicketCancellation;
use FuteBus\SeatManagement\Models\SeatLayout;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'ticket_code',
        'booking_id',
        'trip_id',
        'seat_layout_id',
        'passenger_name',
        'passenger_phone',
        'seat_code',
        'price',
        'qr_code',
        'status',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_id'     => 'integer',
            'trip_id'        => 'integer',
            'seat_layout_id' => 'integer',
            'price'          => 'decimal:2',
            'used_at'        => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function seatLayout(): BelongsTo
    {
        return $this->belongsTo(SeatLayout::class, 'seat_layout_id', 'id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }

    public function ticketCancellation(): HasOne
    {
        return $this->hasOne(TicketCancellation::class, 'ticket_id', 'id');
    }
}

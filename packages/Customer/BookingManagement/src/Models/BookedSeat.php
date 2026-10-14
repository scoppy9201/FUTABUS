<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Models;

use FuteBus\SeatManagement\Models\SeatLayout;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookedSeat extends Model
{
    protected $table = 'booked_seats';

    protected $fillable = [
        'booking_id',
        'trip_id',
        'seat_layout_id',
        'seat_code',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'booking_id'     => 'integer',
            'trip_id'        => 'integer',
            'seat_layout_id' => 'integer',
            'price'          => 'decimal:2',
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
}

<?php

declare(strict_types=1);

namespace FuteBus\SeatManagement\Models;

use FuteBus\BookingManagement\Models\BookedSeat;
use FuteBus\BusManagement\Models\Bus;
use FuteBus\TicketManagement\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatLayout extends Model
{
    protected $table = 'seat_layouts';

    protected $fillable = [
        'bus_id',
        'seat_code',
        'row_number',
        'column_number',
        'seat_type',
        'deck',
        'price_multiplier',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'bus_id'           => 'integer',
            'row_number'       => 'integer',
            'column_number'    => 'integer',
            'price_multiplier' => 'decimal:2',
            'is_available'     => 'boolean',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id', 'id');
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class, 'seat_layout_id', 'id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'seat_layout_id', 'id');
    }
}

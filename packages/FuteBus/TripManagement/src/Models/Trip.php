<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Models;

use FuteBus\BookingManagement\Models\BookedSeat;
use FuteBus\BookingManagement\Models\Booking;
use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\BusManagement\Models\Bus;
use FuteBus\Payment\Models\SePayPaymentIntent;
use FuteBus\RouteManagement\Models\BusRoute;
use FuteBus\TicketManagement\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $table = 'trips';

    protected $fillable = [
        'trip_schedule_id',
        'route_id',
        'bus_id',
        'bus_company_id',
        'departure_time',
        'arrival_time',
        'price',
        'status',
        'available_seats',
    ];

    protected function casts(): array
    {
        return [
            'trip_schedule_id' => 'integer',
            'route_id'         => 'integer',
            'bus_id'           => 'integer',
            'bus_company_id'   => 'integer',
            'departure_time'   => 'datetime',
            'arrival_time'     => 'datetime',
            'price'            => 'decimal:2',
            'available_seats'  => 'integer',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id', 'id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(BusRoute::class, 'route_id', 'id');
    }

    public function tripSchedule(): BelongsTo
    {
        return $this->belongsTo(TripSchedule::class, 'trip_schedule_id', 'id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'trip_id', 'id');
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class, 'trip_id', 'id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'trip_id', 'id');
    }

    public function secondaryTripSepayPaymentIntents(): HasMany
    {
        return $this->hasMany(SePayPaymentIntent::class, 'secondary_trip_id', 'id');
    }

    public function tripSepayPaymentIntents(): HasMany
    {
        return $this->hasMany(SePayPaymentIntent::class, 'trip_id', 'id');
    }
}

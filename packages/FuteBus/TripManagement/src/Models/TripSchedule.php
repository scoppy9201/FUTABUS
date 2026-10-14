<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Models;

use FuteBus\RouteManagement\Models\BusRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripSchedule extends Model
{
    protected $table = 'trip_schedules';

    protected $fillable = [
        'route_id',
        'departure_time',
        'duration_minutes',
        'days_of_week',
        'start_date',
        'end_date',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'route_id'         => 'integer',
            'duration_minutes' => 'integer',
            'days_of_week'     => 'array',
            'start_date'       => 'date',
            'end_date'         => 'date',
            'price'            => 'decimal:2',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(BusRoute::class, 'route_id', 'id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'trip_schedule_id', 'id');
    }
}

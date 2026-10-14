<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    protected $table = 'route_stops';

    protected $fillable = [
        'route_id',
        'name',
        'address',
        'stop_order',
        'leg_minutes',
        'offset_minutes',
        'stop_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'route_id'       => 'integer',
            'stop_order'     => 'integer',
            'leg_minutes'    => 'integer',
            'offset_minutes' => 'integer',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(BusRoute::class, 'route_id', 'id');
    }
}

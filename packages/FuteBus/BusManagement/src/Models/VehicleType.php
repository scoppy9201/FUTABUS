<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    protected $table = 'vehicle_types';

    protected $fillable = [
        'name',
        'vehicle_class',
        'floor_count',
        'seat_rows',
        'seat_columns',
        'unused_seats',
        'description',
        'default_capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'floor_count'      => 'integer',
            'seat_rows'        => 'integer',
            'seat_columns'     => 'integer',
            'unused_seats'     => 'integer',
            'default_capacity' => 'integer',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class, 'vehicle_type_id', 'id');
    }
}

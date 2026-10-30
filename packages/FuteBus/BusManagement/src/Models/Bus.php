<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Models;

use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\SeatManagement\Models\SeatLayout;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $table = 'buses';

    protected $fillable = [
        'bus_company_id',
        'vehicle_type_id',
        'license_plate',
        'color',
        'chassis_number',
        'engine_number',
        'brand',
        'manufacture_year',
        'expiration_year',
        'name',
        'capacity',
        'bus_type',
        'status',
        'doc_status',
        'seat_rows',
        'seat_columns',
        'description',
        'amenities',
        'wifi_name',
        'wifi_password',
    ];

    protected function casts(): array
    {
        return [
            'bus_company_id'   => 'integer',
            'amenities'        => 'array',
            'vehicle_type_id'  => 'integer',
            'manufacture_year' => 'integer',
            'expiration_year'  => 'integer',
            'capacity'         => 'integer',
            'seat_rows'        => 'integer',
            'seat_columns'     => 'integer',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id', 'id');
    }

    public function vehicleDocuments(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'bus_id', 'id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'bus_id', 'id');
    }

    public function seatLayouts(): HasMany
    {
        return $this->hasMany(SeatLayout::class, 'bus_id', 'id');
    }
}

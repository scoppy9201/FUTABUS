<?php

declare(strict_types=1);

namespace FuteBus\RouteManagement\Models;

use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\TripManagement\Models\Trip;
use FuteBus\TripManagement\Models\TripSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusRoute extends Model
{
    protected $table = 'routes';

    protected $fillable = [
        'bus_company_id',
        'code',
        'name',
        'origin_city',
        'origin_station',
        'destination_city',
        'destination_station',
        'distance_km',
        'duration_minutes',
        'vehicle_type',
        'schedule_group',
        'sort_order',
        'is_public_schedule',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bus_company_id'     => 'integer',
            'distance_km'        => 'integer',
            'duration_minutes'   => 'integer',
            'schedule_group'     => 'integer',
            'sort_order'         => 'integer',
            'is_public_schedule' => 'boolean',
            'base_price'         => 'decimal:2',
            'is_active'          => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeScheduleOrder(Builder $query): Builder
    {
        return $query->orderBy('schedule_group')->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublicSchedule(Builder $query): Builder
    {
        return $query->forFuta()->where('is_public_schedule', true);
    }

    public function scopeForFuta(Builder $query): Builder
    {
        return $query->whereIn('bus_company_id', fn ($company) => $company->select('id')->from('bus_companies')->where('code', 'FUTA'));
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class, 'route_id', 'id');
    }

    public function tripSchedules(): HasMany
    {
        return $this->hasMany(TripSchedule::class, 'route_id', 'id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'route_id', 'id');
    }
}

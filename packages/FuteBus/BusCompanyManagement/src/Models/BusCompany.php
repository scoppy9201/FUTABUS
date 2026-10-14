<?php

declare(strict_types=1);

namespace FuteBus\BusCompanyManagement\Models;

use App\Models\User;
use FuteBus\BusManagement\Models\Bus;
use FuteBus\Profile\Models\CustomerFeedbackReport;
use FuteBus\RolePermission\Models\Role;
use FuteBus\RouteManagement\Models\BusRoute;
use FuteBus\StaffCustomerSupport\Models\CustomerSupportEvent;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusCompany extends Model
{
    protected $table = 'bus_companies';

    protected $fillable = [
        'name',
        'code',
        'logo',
        'hotline',
        'email',
        'address',
        'website',
        'tax_code',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class, 'bus_company_id', 'id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(BusRoute::class, 'bus_company_id', 'id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'bus_company_id', 'id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'bus_company_id', 'id');
    }

    public function customerFeedbackReports(): HasMany
    {
        return $this->hasMany(CustomerFeedbackReport::class, 'assigned_bus_company_id', 'id');
    }

    public function busCompanyCustomerSupportEvents(): HasMany
    {
        return $this->hasMany(CustomerSupportEvent::class, 'bus_company_id', 'id');
    }

    public function previousBusCompanyCustomerSupportEvents(): HasMany
    {
        return $this->hasMany(CustomerSupportEvent::class, 'previous_bus_company_id', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bus_company_id', 'id');
    }
}

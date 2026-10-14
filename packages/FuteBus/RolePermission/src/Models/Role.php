<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Models;

use App\Models\User;
use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\Profile\Models\CustomerFeedbackReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'bus_company_id',
        'name',
        'slug',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'bus_company_id' => 'integer',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function customerFeedbackReports(): HasMany
    {
        return $this->hasMany(CustomerFeedbackReport::class, 'department_role_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
}

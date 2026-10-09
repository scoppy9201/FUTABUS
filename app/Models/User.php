<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'gender',
        'date_of_birth',
        'address',
        'occupation',
        'is_active',
        'bus_company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'date_of_birth'      => 'date',
        'password'           => 'hashed',
        'is_active'          => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isBusCompany(): bool
    {
        return $this->hasRole('bus-company');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function hasRole(string|array $roles): bool
    {
        return $this->exists && DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $this->getKey())
            ->whereIn('roles.slug', (array) $roles)
            ->exists();
    }

    public function hasPermissionTo(string|array $permissions): bool
    {
        if (! $this->exists) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $this->getKey())
            ->whereIn('permissions.slug', (array) $permissions)
            ->where('permissions.is_active', true)
            ->where(function ($query): void {
                $query->where('roles.slug', '!=', 'staff')
                    ->orWhereNotNull('roles.bus_company_id');
            })
            ->where(function ($query): void {
                $query->whereNull('roles.bus_company_id')
                    ->orWhere('roles.bus_company_id', $this->bus_company_id);
            })
            ->exists();
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use stdClass;

class AccessControlService
{
    public function companyFor(User $user): ?stdClass
    {
        if ($user->isAdmin()) {
            return DB::table('bus_companies')->where('code', 'FUTA')->first();
        }

        if ($user->bus_company_id) {
            return DB::table('bus_companies')->where('id', $user->bus_company_id)->first();
        }

        return DB::table('bus_companies')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])
            ->first();
    }

    public function roleListing(int $companyId)
    {
        $permissionCount = DB::table('permission_role')
            ->selectRaw('COUNT(*)')
            ->whereColumn('permission_role.role_id', 'roles.id');
        $userCount = DB::table('role_user')
            ->selectRaw('COUNT(*)')
            ->whereColumn('role_user.role_id', 'roles.id');

        return DB::table('roles')
            ->where('bus_company_id', $companyId)
            ->select('id', 'name', 'description', 'created_at')
            ->selectSub($permissionCount, 'permission_count')
            ->selectSub($userCount, 'user_count')
            ->orderBy('name')
            ->get();
    }

    public function roleForCompany(int $companyId, int $roleId): stdClass
    {
        $role = DB::table('roles')->where('bus_company_id', $companyId)->where('id', $roleId)->first();
        abort_if($role === null, 404);

        return $role;
    }

    public function permissionGroups(bool $activeOnly = true)
    {
        return DB::table('permissions')
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('group')->orderBy('slug')->get()->groupBy('group');
    }

    public function permissionIdsForRole(int $roleId): array
    {
        return DB::table('permission_role')->where('role_id', $roleId)->pluck('permission_id')->all();
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        DB::table('permission_role')->where('role_id', $roleId)->delete();

        if ($permissionIds === []) {
            return;
        }

        DB::table('permission_role')->insertOrIgnore(array_map(
            static fn (int|string $permissionId): array => [
                'role_id' => $roleId,
                'permission_id' => (int) $permissionId,
            ],
            $permissionIds,
        ));
    }

    public function staffListing(int $companyId, ?string $search = null): LengthAwarePaginator
    {
        $staff = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles as staff_role', 'staff_role.id', '=', 'role_user.role_id')
            ->where('users.bus_company_id', $companyId)
            ->whereNull('staff_role.bus_company_id')
            ->where('staff_role.slug', 'staff')
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $term = '%'.$search.'%';
                    $query->where('users.name', 'like', $term)
                        ->orWhere('users.email', 'like', $term)
                        ->orWhere('users.phone', 'like', $term);
                });
            })
            ->select('users.id', 'users.name', 'users.email', 'users.is_active')
            ->distinct()
            ->orderBy('users.name')
            ->paginate(10)
            ->withQueryString();

        $staffIds = $staff->getCollection()->pluck('id');
        $roleAssignments = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.bus_company_id', $companyId)
            ->whereIn('role_user.user_id', $staffIds)
            ->select('role_user.user_id', 'roles.id', 'roles.name')
            ->get()
            ->groupBy('user_id');

        $staff->getCollection()->transform(function (stdClass $user) use ($roleAssignments): stdClass {
            $assignments = $roleAssignments->get($user->id, collect());
            $user->role_ids = $assignments->pluck('id')->map(static fn ($id): int => (int) $id)->all();
            $user->role_names = $assignments->pluck('name')->all();

            return $user;
        });

        return $staff;
    }

    public function staffForCompany(int $companyId, int $staffId): User
    {
        $staff = User::query()
            ->where('users.id', $staffId)
            ->where('users.bus_company_id', $companyId)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->whereNull('roles.bus_company_id')
                    ->where('roles.slug', 'staff');
            })
            ->first();

        abort_if($staff === null, 404);

        return $staff;
    }

    public function companyRoleIds(int $companyId, array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        return DB::table('roles')
            ->where('bus_company_id', $companyId)
            ->whereIn('id', $roleIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    public function syncUserRoles(int $companyId, int $staffId, array $roleIds): void
    {
        $companyRoleIds = DB::table('roles')->where('bus_company_id', $companyId)->pluck('id')->all();
        DB::table('role_user')->where('user_id', $staffId)->whereIn('role_id', $companyRoleIds)->delete();

        foreach ($roleIds as $roleId) {
            DB::table('role_user')->insertOrIgnore([
                'role_id' => $roleId,
                'user_id' => $staffId,
            ]);
        }
    }
}

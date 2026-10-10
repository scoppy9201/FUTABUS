<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class StaffAccountService
{
    public function companyForManager(User $manager): ?stdClass
    {
        if ($manager->isAdmin()) {
            return DB::table('bus_companies')->where('code', 'FUTA')->first();
        }

        if ($manager->bus_company_id) {
            return DB::table('bus_companies')->where('id', $manager->bus_company_id)->first();
        }

        return DB::table('bus_companies')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($manager->email)])
            ->first();
    }

    public function listing(int $companyId, array $filters): LengthAwarePaginator
    {
        $query = $this->staffQuery($companyId)
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.avatar', 'users.is_active', 'users.created_at');

        if ($filters['search'] ?? null) {
            $search = '%'.$filters['search'].'%';
            $query->where(function (Builder $query) use ($search): void {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.phone', 'like', $search);
            });
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('users.is_active', $filters['status'] === 'active');
        }

        return $query->orderBy('users.name')->paginate(10)->withQueryString();
    }

    public function statistics(int $companyId): array
    {
        $counts = $this->staffQuery($companyId)
            ->selectRaw('COUNT(DISTINCT users.id) as total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN users.is_active = 1 THEN users.id END) as active')
            ->selectRaw('COUNT(DISTINCT CASE WHEN users.is_active = 0 THEN users.id END) as inactive')
            ->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'active' => (int) ($counts->active ?? 0),
            'inactive' => (int) ($counts->inactive ?? 0),
        ];
    }

    public function staffForCompany(int $companyId, int $staffId): User
    {
        $staff = User::query()
            ->where('users.id', $staffId)
            ->where('users.bus_company_id', $companyId)
            ->whereExists(function (Builder $query): void {
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

    private function staffQuery(int $companyId): Builder
    {
        return DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('users.bus_company_id', $companyId)
            ->whereNull('roles.bus_company_id')
            ->where('roles.slug', 'staff');
    }
}

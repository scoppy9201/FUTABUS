<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Http\Controllers;

use App\Models\User;
use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccessControlController extends Controller
{
    public function index(Request $request, AccessControlService $access): View
    {
        $company = $this->company($request, $access, 'role.view');

        return view('RolePermission::index', [
            'company' => $company,
            'roles' => $access->roleListing($company->id),
        ]);
    }

    public function catalog(Request $request, AccessControlService $access): View
    {
        $company = $this->company($request, $access, 'role.view');
        $registeredSlugs = collect(config('permission_catalog', []))
            ->flatMap(fn (array $actions, string $group): array => collect($actions)->map(fn (string $action): string => $group.'.'.$action)->all())
            ->values();
        $knownSlugs = DB::table('permissions')->pluck('slug');

        return view('RolePermission::catalog', [
            'company' => $company,
            'permissionGroups' => $access->permissionGroups(false),
            'canManageCatalog' => $request->user()->isAdmin(),
            'availablePermissionSlugs' => $registeredSlugs->diff($knownSlugs)->values(),
        ]);
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $this->assertCatalogAdmin($request);
        $allowedSlugs = collect(config('permission_catalog', []))
            ->flatMap(fn (array $actions, string $group): array => collect($actions)->map(fn (string $action): string => $group.'.'.$action)->all())
            ->all();
        $data = $request->validate([
            'slug' => ['required', 'string', Rule::in($allowedSlugs)],
            'display_name' => ['required', 'string', 'max:120'],
            'display_group' => ['nullable', 'string', 'max:80'],
        ]);

        $group = Str::before($data['slug'], '.');
        $existing = DB::table('permissions')->where('slug', $data['slug'])->first();
        if ($existing && $existing->is_active) {
            throw ValidationException::withMessages(['slug' => __('RolePermission::app.validation.permission_exists')]);
        }

        DB::table('permissions')->updateOrInsert(
            ['slug' => $data['slug']],
            [
                'name' => $data['slug'],
                'display_name' => $data['display_name'],
                'group' => $group,
                'display_group' => $data['display_group'] ?? null,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => $existing?->created_at ?? now(),
            ],
        );
        if (filled($data['display_group'] ?? null)) {
            DB::table('permissions')->where('group', $group)->update([
                'display_group' => $data['display_group'],
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('access-management.catalog')->with('status', __('RolePermission::app.permission_saved'));
    }

    public function updatePermission(Request $request, int $permission): RedirectResponse
    {
        $this->assertCatalogAdmin($request);
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'display_group' => ['nullable', 'string', 'max:80'],
        ]);

        $row = DB::table('permissions')->where('id', $permission)->first();
        abort_if($row === null, 404);
        DB::table('permissions')->where('group', $row->group)->update([
            'display_group' => $data['display_group'] ?? null,
            'updated_at' => now(),
        ]);
        DB::table('permissions')->where('id', $permission)->update([
            'display_name' => $data['display_name'],
            'updated_at' => now(),
        ]);

        return redirect()->route('access-management.catalog')->with('status', __('RolePermission::app.permission_saved'));
    }

    public function setPermissionStatus(Request $request, int $permission): RedirectResponse
    {
        $this->assertCatalogAdmin($request);
        $row = DB::table('permissions')->where('id', $permission)->first();
        abort_if($row === null, 404);

        DB::table('permissions')->where('id', $permission)->update([
            'is_active' => ! (bool) $row->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->route('access-management.catalog')->with('status', __((bool) $row->is_active ? 'RolePermission::app.permission_deactivated' : 'RolePermission::app.permission_activated'));
    }

    public function users(Request $request, AccessControlService $access): View
    {
        $company = $this->company($request, $access, 'user.view');
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');

        return view('RolePermission::users', [
            'company' => $company,
            'staffAccounts' => $access->staffListing($company->id, $search),
            'roles' => $access->roleListing($company->id),
            'search' => $search,
        ]);
    }

    public function assignRoles(Request $request, AccessControlService $access, int $staff): RedirectResponse
    {
        $company = $this->company($request, $access, 'user.update');
        $access->staffForCompany($company->id, $staff);
        $data = $request->validate([
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['integer', 'distinct'],
        ]);
        $roleIds = array_map('intval', $data['role_ids'] ?? []);
        $companyRoleIds = $access->companyRoleIds($company->id, $roleIds);

        if (count($companyRoleIds) !== count($roleIds)) {
            throw ValidationException::withMessages(['role_ids' => __('RolePermission::app.validation.invalid_role')]);
        }

        DB::transaction(fn () => $access->syncUserRoles($company->id, $staff, $companyRoleIds));

        return redirect()->route('access-management.users', ['q' => $request->input('q')])
            ->with('status', __('RolePermission::app.user_roles_saved'));
    }

    public function createRole(Request $request, AccessControlService $access): View
    {
        $company = $this->company($request, $access, 'role.create');

        return view('RolePermission::roles.create', [
            'company' => $company,
            'permissionGroups' => $access->permissionGroups(),
        ]);
    }

    public function storeRole(Request $request, AccessControlService $access): RedirectResponse
    {
        $company = $this->company($request, $access, 'role.create');
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->where(fn ($query) => $query->where('bus_company_id', $company->id)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_ids_present' => ['required', 'accepted'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')->where('is_active', true)],
        ]);

        try {
            DB::transaction(function () use ($data, $company, $access): void {
                $baseSlug = 'company-'.$company->id.'-'.Str::slug($data['name']);
                $slug = $baseSlug;
                $suffix = 2;
                while (DB::table('roles')->where('bus_company_id', $company->id)->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$suffix++;
                }

                $roleId = DB::table('roles')->insertGetId([
                    'bus_company_id' => $company->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $access->syncRolePermissions($roleId, array_map('intval', $data['permission_ids'] ?? []));
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return back()->withInput()->withErrors(['name' => __('RolePermission::app.validation.role_exists')]);
            }

            throw $exception;
        }

        return redirect()->route('access-management.index')->with('status', __('RolePermission::app.role_created'));
    }

    public function editRole(Request $request, AccessControlService $access, int $role): View
    {
        $company = $this->company($request, $access, 'role.update');
        $role = $access->roleForCompany($company->id, $role);

        return view('RolePermission::roles.edit', [
            'company' => $company,
            'role' => $role,
            'permissionGroups' => $access->permissionGroups(),
            'selectedPermissionIds' => $access->permissionIdsForRole($role->id),
        ]);
    }

    public function updateRole(Request $request, AccessControlService $access, int $role): RedirectResponse
    {
        $company = $this->company($request, $access, 'role.update');
        $role = $access->roleForCompany($company->id, $role);
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('bus_company_id', $company->id))
                    ->ignore($role->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_ids_present' => ['required', 'accepted'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')->where('is_active', true)],
        ]);

        DB::transaction(function () use ($data, $role, $access): void {
            DB::table('roles')->where('id', $role->id)->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'updated_at' => now(),
            ]);
            $access->syncRolePermissions($role->id, array_map('intval', $data['permission_ids'] ?? []));
        });

        return redirect()->route('access-management.index')->with('status', __('RolePermission::app.role_updated'));
    }

    public function deleteRole(Request $request, AccessControlService $access, int $role): RedirectResponse
    {
        $company = $this->company($request, $access, 'role.delete');
        $role = $access->roleForCompany($company->id, $role);
        DB::table('roles')->where('id', $role->id)->delete();

        return redirect()->route('access-management.index')->with('status', __('RolePermission::app.role_deleted'));
    }

    private function company(Request $request, AccessControlService $access, string $permission): object
    {
        $user = $request->user();
        abort_unless($user instanceof User && ($user->isAdmin() || $user->isBusCompany()), 403);
        abort_unless($user->hasPermissionTo($permission), 403);

        $company = $access->companyFor($user);
        abort_if($company === null, 403);

        return $company;
    }

    private function assertCatalogAdmin(Request $request): void
    {
        abort_unless($request->user() instanceof User && $request->user()->isAdmin(), 403);
    }
}

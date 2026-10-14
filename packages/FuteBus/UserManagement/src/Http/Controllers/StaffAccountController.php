<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use FuteBus\UserManagement\Http\Requests\StaffAccountFilterRequest;
use FuteBus\UserManagement\Http\Requests\StaffAccountRequest;
use FuteBus\UserManagement\Http\Requests\StaffAccountStatusRequest;
use FuteBus\UserManagement\Services\StaffAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class StaffAccountController extends Controller
{
    public function index(StaffAccountFilterRequest $request, StaffAccountService $accounts): View
    {
        return view('UserManagement::index', $this->listingData($request, $accounts));
    }

    public function show(StaffAccountFilterRequest $request, StaffAccountService $accounts, int $staff): View
    {
        $company = $this->company($request, $accounts);

        return view('UserManagement::index', [
            ...$this->listingData($request, $accounts, $company),
            'viewingStaff' => $accounts->staffForCompany($company->id, $staff),
        ]);
    }

    public function create(Request $request, StaffAccountService $accounts): View
    {
        return view('UserManagement::create', ['company' => $this->company($request, $accounts)]);
    }

    public function store(StaffAccountRequest $request, StaffAccountService $accounts): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $data = $request->validated();
        $data['date_of_birth'] = Carbon::createFromFormat('!Y-m-d', $data['date_of_birth'])->toDateString();
        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        if ($request->hasFile('avatar') && ! is_string($avatarPath)) {
            throw ValidationException::withMessages(['avatar' => __('UserManagement::app.validation.avatar_store_failed')]);
        }
        unset($data['avatar']);

        try {
            DB::transaction(function () use ($data, $company, $avatarPath): void {
                $roleId = DB::table('roles')->whereNull('bus_company_id')->where('slug', 'staff')->value('id');
                abort_if($roleId === null, 500);

                $staff = User::create([
                    'name'           => $data['name'],
                    'email'          => $data['email'],
                    'phone'          => $data['phone'] ?? null,
                    'gender'         => $data['gender'] ?? null,
                    'date_of_birth'  => $data['date_of_birth'] ?? null,
                    'address'        => $data['address'] ?? null,
                    'password'       => $data['password'],
                    'avatar'         => $avatarPath,
                    'bus_company_id' => $company->id,
                    'is_active'      => true,
                ]);
                $staff->email_verified_at = now();
                $staff->save();

                DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $staff->id]);
            });
        } catch (QueryException $exception) {
            if (is_string($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return back()->withInput()->withErrors(['email' => __('UserManagement::app.validation.account_exists')]);
            }

            throw $exception;
        } catch (Throwable $exception) {
            if (is_string($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            throw $exception;
        }

        return redirect()->route('staff-accounts.index')->with('status', __('UserManagement::app.created'));
    }

    public function edit(StaffAccountFilterRequest $request, StaffAccountService $accounts, int $staff): View
    {
        $company = $this->company($request, $accounts);

        return view('UserManagement::index', [
            ...$this->listingData($request, $accounts, $company),
            'editingStaff' => $accounts->staffForCompany($company->id, $staff),
        ]);
    }

    public function update(StaffAccountRequest $request, StaffAccountService $accounts, int $staff): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $account = $accounts->staffForCompany($company->id, $staff);
        $data = $request->validated();
        $data['date_of_birth'] = Carbon::createFromFormat('!Y-m-d', $data['date_of_birth'])->toDateString();
        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        if ($request->hasFile('avatar') && ! is_string($avatarPath)) {
            throw ValidationException::withMessages(['avatar' => __('UserManagement::app.validation.avatar_store_failed')]);
        }
        unset($data['avatar']);
        $oldAvatar = $account->avatar;

        try {
            $account->fill([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'gender'        => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address'       => $data['address'] ?? null,
                'is_active'     => (bool) $data['is_active'],
            ]);

            if (is_string($avatarPath)) {
                $account->avatar = $avatarPath;
            }

            if (! empty($data['password'])) {
                $account->password = $data['password'];
            }

            $account->save();
        } catch (Throwable $exception) {
            if (is_string($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            throw $exception;
        }

        if (is_string($avatarPath) && is_string($oldAvatar) && str_starts_with($oldAvatar, 'avatars/')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return redirect()->route('staff-accounts.index')->with('status', __('UserManagement::app.updated'));
    }

    public function toggleStatus(StaffAccountStatusRequest $request, StaffAccountService $accounts, int $staff): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $account = $accounts->staffForCompany($company->id, $staff);
        $avatarPath = $account->avatar;
        $data = $request->validated();
        $account->is_active = (bool) $data['is_active'];
        $account->save();

        return redirect()->route('staff-accounts.index')->with(
            'status',
            __($account->is_active ? 'UserManagement::app.activated' : 'UserManagement::app.deactivated'),
        );
    }

    public function destroy(Request $request, StaffAccountService $accounts, int $staff): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $account = $accounts->staffForCompany($company->id, $staff);
        $avatarPath = $account->avatar;

        DB::transaction(function () use ($account): void {
            DB::table('sessions')->where('user_id', $account->id)->delete();
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $account->id)
                    ->delete();
            }

            $account->delete();
        });

        if (is_string($avatarPath) && str_starts_with($avatarPath, 'avatars/')) {
            Storage::disk('public')->delete($avatarPath);
        }

        return redirect()->route('staff-accounts.index')->with('status', __('UserManagement::app.deleted'));
    }

    private function company(Request $request, StaffAccountService $accounts): object
    {
        $manager = $request->user();
        abort_unless($manager instanceof User && ($manager->hasRole('bus-company') || $manager->isAdmin()), 403);

        $company = $accounts->companyForManager($manager);
        abort_if($company === null, 403);

        return $company;
    }

    private function listingData(
        StaffAccountFilterRequest $request,
        StaffAccountService $accounts,
        ?object $company = null,
    ): array {
        $company ??= $this->company($request, $accounts);
        $filters = $request->validated();

        return [
            'company'       => $company,
            'staffAccounts' => $accounts->listing($company->id, $filters),
            'statistics'    => $accounts->statistics($company->id),
            'filters'       => $filters,
        ];
    }
}

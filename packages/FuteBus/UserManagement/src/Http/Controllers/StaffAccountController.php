<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use FuteBus\UserManagement\Services\StaffAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class StaffAccountController extends Controller
{
    public function index(Request $request, StaffAccountService $accounts): View
    {
        $company = $this->company($request, $accounts);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        return view('UserManagement::index', [
            'company' => $company,
            'staffAccounts' => $accounts->listing($company->id, $filters),
            'statistics' => $accounts->statistics($company->id),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, StaffAccountService $accounts, int $staff): View
    {
        $company = $this->company($request, $accounts);

        return view('UserManagement::show', [
            'company' => $company,
            'staff' => $accounts->staffForCompany($company->id, $staff),
        ]);
    }

    public function create(Request $request, StaffAccountService $accounts): View
    {
        return view('UserManagement::create', ['company' => $this->company($request, $accounts)]);
    }

    public function store(Request $request, StaffAccountService $accounts): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $this->normalizePhone($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'regex:/^(?:0|\+84)[0-9]{9}$/', Rule::unique('users', 'phone')],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => $this->dateOfBirthRules(),
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png', 'max:1024'],
        ], $this->validationMessages());
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
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'address' => $data['address'] ?? null,
                    'password' => $data['password'],
                    'avatar' => $avatarPath,
                    'bus_company_id' => $company->id,
                    'is_active' => true,
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

    public function edit(Request $request, StaffAccountService $accounts, int $staff): View
    {
        $company = $this->company($request, $accounts);

        return view('UserManagement::edit', [
            'company' => $company,
            'staff' => $accounts->staffForCompany($company->id, $staff),
        ]);
    }

    public function update(Request $request, StaffAccountService $accounts, int $staff): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $account = $accounts->staffForCompany($company->id, $staff);
        $this->normalizePhone($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($account->id)],
            'phone' => ['required', 'string', 'regex:/^(?:0|\+84)[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($account->id)],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => $this->dateOfBirthRules(),
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'password_confirmation' => ['nullable', 'required_with:password', 'string', 'same:password'],
            'is_active' => ['required', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png', 'max:1024'],
        ], $this->validationMessages());
        $data['date_of_birth'] = Carbon::createFromFormat('!Y-m-d', $data['date_of_birth'])->toDateString();
        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        if ($request->hasFile('avatar') && ! is_string($avatarPath)) {
            throw ValidationException::withMessages(['avatar' => __('UserManagement::app.validation.avatar_store_failed')]);
        }
        unset($data['avatar']);
        $oldAvatar = $account->avatar;

        try {
            $account->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => (bool) $data['is_active'],
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

    public function toggleStatus(Request $request, StaffAccountService $accounts, int $staff): RedirectResponse
    {
        $company = $this->company($request, $accounts);
        $account = $accounts->staffForCompany($company->id, $staff);
        $avatarPath = $account->avatar;
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
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

    private function normalizePhone(Request $request): void
    {
        $phone = $request->input('phone');
        if (is_string($phone)) {
            $request->merge(['phone' => preg_replace('/[\s.()-]+/', '', $phone)]);
        }
    }

    private function dateOfBirthRules(): array
    {
        return [
            'bail',
            'required',
            'regex:/^\d{4}-\d{2}-\d{2}$/',
            'date_format:Y-m-d',
            function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    $date = Carbon::createFromFormat('!Y-m-d', $value);
                } catch (Throwable) {
                    $date = false;
                }

                if (! $date instanceof Carbon || $date->format('Y-m-d') !== $value) {
                    $fail(__('UserManagement::app.validation.birth_date_invalid'));

                    return;
                }

                $today = today();
                if ($date->greaterThanOrEqualTo($today)) {
                    $fail(__('UserManagement::app.validation.birth_date_before_today'));

                    return;
                }

                if ($date->greaterThan($today->copy()->subYears(16))) {
                    $fail(__('UserManagement::app.validation.birth_date_min_age'));

                    return;
                }

                if ($date->lessThan($today->copy()->subYears(100))) {
                    $fail(__('UserManagement::app.validation.birth_date_invalid'));
                }
            },
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => __('UserManagement::app.validation.name_required'),
            'email.required' => __('UserManagement::app.validation.email_required'),
            'email.email' => __('UserManagement::app.validation.email_invalid'),
            'email.unique' => __('UserManagement::app.validation.email_exists'),
            'phone.required' => __('UserManagement::app.validation.phone_required'),
            'phone.regex' => __('UserManagement::app.validation.phone_invalid'),
            'phone.unique' => __('UserManagement::app.validation.phone_exists'),
            'date_of_birth.required' => __('UserManagement::app.validation.birth_date_required'),
            'date_of_birth.regex' => __('UserManagement::app.validation.birth_date_invalid'),
            'date_of_birth.date_format' => __('UserManagement::app.validation.birth_date_invalid'),
            'password.required' => __('UserManagement::app.validation.password_required'),
            'password.min' => __('UserManagement::app.validation.password_min'),
            'password_confirmation.required' => __('UserManagement::app.validation.password_confirmation_required'),
            'password_confirmation.required_with' => __('UserManagement::app.validation.password_confirmation_required'),
            'password_confirmation.same' => __('UserManagement::app.validation.password_confirmed'),
        ];
    }
}

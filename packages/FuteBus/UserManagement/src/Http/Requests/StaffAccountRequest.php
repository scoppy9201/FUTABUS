<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Http\Requests;

use Carbon\Carbon;
use FuteBus\UserManagement\Services\StaffAccountService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class StaffAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        if (! $actor || (! $actor->isAdmin() && ! $actor->isBusCompany())) {
            return false;
        }
        $accounts = app(StaffAccountService::class);
        $company = $accounts->companyForManager($actor);
        if (! $company) {
            return false;
        }
        if ($this->route('staff')) {
            $accounts->staffForCompany($company->id, (int) $this->route('staff'));
        }

        return true;
    }

    public function rules(): array
    {
        $id = $this->route('staff') ? (int) $this->route('staff') : null;

        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone'                 => ['required', 'string', 'regex:/^(?:0|\+84)[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($id)],
            'gender'                => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth'         => $this->dateOfBirthRules(),
            'address'               => ['nullable', 'string', 'max:255'],
            'password'              => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'password_confirmation' => [$id ? 'nullable' : 'required', 'required_with:password', 'string', 'same:password'],
            'avatar'                => ['nullable', 'image', 'mimes:jpeg,png', 'max:1024'],
            ...($id ? ['is_active' => ['required', 'boolean']] : []),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/[\s.()-]+/', '', $this->input('phone'))]);
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

    public function messages(): array
    {
        return [
            'name.required'                       => __('UserManagement::app.validation.name_required'),
            'email.required'                      => __('UserManagement::app.validation.email_required'),
            'email.email'                         => __('UserManagement::app.validation.email_invalid'),
            'email.unique'                        => __('UserManagement::app.validation.email_exists'),
            'phone.required'                      => __('UserManagement::app.validation.phone_required'),
            'phone.regex'                         => __('UserManagement::app.validation.phone_invalid'),
            'phone.unique'                        => __('UserManagement::app.validation.phone_exists'),
            'date_of_birth.required'              => __('UserManagement::app.validation.birth_date_required'),
            'date_of_birth.regex'                 => __('UserManagement::app.validation.birth_date_invalid'),
            'date_of_birth.date_format'           => __('UserManagement::app.validation.birth_date_invalid'),
            'password.required'                   => __('UserManagement::app.validation.password_required'),
            'password.min'                        => __('UserManagement::app.validation.password_min'),
            'password_confirmation.required'      => __('UserManagement::app.validation.password_confirmation_required'),
            'password_confirmation.required_with' => __('UserManagement::app.validation.password_confirmation_required'),
            'password_confirmation.same'          => __('UserManagement::app.validation.password_confirmed'),
        ];
    }
}

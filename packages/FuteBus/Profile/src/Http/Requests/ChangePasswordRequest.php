<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string', 'current_password:web'],
            'password'              => ['required', 'string', 'min:8', 'different:current_password', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => __('Profile::app.password_change.validation.current_required'),
            'current_password.string'           => __('Profile::app.password_change.validation.current_invalid'),
            'current_password.current_password' => __('Profile::app.password_change.validation.current_invalid'),
            'password.required'                 => __('Profile::app.password_change.validation.new_required'),
            'password.string'                   => __('Profile::app.password_change.validation.new_invalid'),
            'password.min'                      => __('Profile::app.password_change.validation.new_min'),
            'password.different'                => __('Profile::app.password_change.validation.new_different'),
            'password.confirmed'                => __('Profile::app.password_change.validation.confirmed'),
            'password_confirmation.required'    => __('Profile::app.password_change.validation.confirm_required'),
            'password_confirmation.string'      => __('Profile::app.password_change.validation.confirmed'),
        ];
    }
}

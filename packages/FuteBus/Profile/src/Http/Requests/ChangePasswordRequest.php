<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    private const STRONG_PASSWORD = 'regex:/^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*\p{Nd}).+$/u';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string', 'current_password:web'],
            'password'              => ['bail', 'required', 'string', 'min:8', self::STRONG_PASSWORD, 'different:current_password'],
            'password_confirmation' => ['bail', 'required', 'string', 'min:8', self::STRONG_PASSWORD, 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => __('Profile::app.password_change.validation.current_required'),
            'current_password.string'           => __('Profile::app.password_change.validation.current_invalid'),
            'current_password.current_password' => __('Profile::app.password_change.validation.current_invalid'),
            'password.required'                 => __('Profile::app.password_change.validation.empty_password'),
            'password.string'                   => __('Profile::app.password_change.validation.new_invalid'),
            'password.min'                      => __('Profile::app.password_change.validation.strong_password'),
            'password.regex'                    => __('Profile::app.password_change.validation.strong_password'),
            'password.different'                => __('Profile::app.password_change.validation.new_different'),
            'password_confirmation.required'    => __('Profile::app.password_change.validation.empty_password'),
            'password_confirmation.string'      => __('Profile::app.password_change.validation.confirmed'),
            'password_confirmation.min'         => __('Profile::app.password_change.validation.strong_password'),
            'password_confirmation.regex'       => __('Profile::app.password_change.validation.strong_password'),
            'password_confirmation.same'        => __('Profile::app.password_change.validation.confirmed'),
        ];
    }
}

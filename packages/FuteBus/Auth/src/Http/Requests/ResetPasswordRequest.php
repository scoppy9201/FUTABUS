<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['password' => ['required', 'string', 'min:8', 'confirmed']];
    }

    public function messages(): array
    {
        return [
            'password.required' => __('Auth::app.registration_flow.validation.password_required'),
            'password.string' => __('Auth::app.registration_flow.validation.invalid_text'),
            'password.min' => __('Auth::app.registration_flow.validation.password_min'),
            'password.confirmed' => __('Auth::app.registration_flow.validation.password_confirmed'),
        ];
    }
}

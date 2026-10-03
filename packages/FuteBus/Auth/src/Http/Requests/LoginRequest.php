<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($email = $this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($email))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Auth::app.registration_flow.validation.email_required'),
            'email.email' => __('Auth::app.registration_flow.validation.invalid_email'),
            'password.required' => __('Auth::app.registration_flow.validation.password_required'),
            'password.string' => __('Auth::app.registration_flow.validation.invalid_text'),
        ];
    }
}

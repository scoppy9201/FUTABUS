<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendRegistrationEmailRequest extends FormRequest
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
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Auth::app.registration_flow.validation.email_required'),
            'email.email' => __('Auth::app.registration_flow.validation.invalid_email'),
            'email.max' => __('Auth::app.registration_flow.validation.email_max'),
            'email.unique' => __('Auth::app.registration_flow.validation.email_taken'),
            'terms.accepted' => __('Auth::app.registration_flow.validation.terms_accepted'),
        ];
    }
}

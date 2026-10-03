<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Auth::app.registration_flow.validation.name_required'),
            'name.string' => __('Auth::app.registration_flow.validation.invalid_text'),
            'name.min' => __('Auth::app.registration_flow.validation.name_min'),
            'name.max' => __('Auth::app.registration_flow.validation.name_max'),
            'phone.required' => __('Auth::app.registration_flow.validation.phone_required'),
            'phone.string' => __('Auth::app.registration_flow.validation.invalid_text'),
            'phone.regex' => __('Auth::app.registration_flow.validation.invalid_phone'),
        ];
    }
}

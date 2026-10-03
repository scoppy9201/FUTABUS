<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRegistrationOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['otp' => ['required', 'digits:6']];
    }

    public function messages(): array
    {
        return [
            'otp.required' => __('Auth::app.registration_flow.validation.otp_required'),
            'otp.digits' => __('Auth::app.registration_flow.validation.otp_digits'),
        ];
    }
}

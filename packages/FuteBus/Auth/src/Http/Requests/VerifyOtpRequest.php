<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge' => ['required', 'string', 'size:64'],
            'otp'       => ['required', 'digits:6'],
        ];
    }
}

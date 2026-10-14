<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOtpRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge' => ['required', 'string', 'size:64'],
            'name'      => ['required', 'string', 'min:2', 'max:255'],
            'phone'     => ['required', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}

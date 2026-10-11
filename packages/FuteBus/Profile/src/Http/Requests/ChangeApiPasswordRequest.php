<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class ChangeApiPasswordRequest extends FormRequest
{
    private const STRONG_PASSWORD = 'regex:/^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*\p{Nd}).+$/u';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string'],
            'password'              => ['bail', 'required', 'string', 'min:8', self::STRONG_PASSWORD, 'different:current_password'],
            'password_confirmation' => ['bail', 'required', 'string', 'min:8', self::STRONG_PASSWORD, 'same:password'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $currentPassword = $this->input('current_password');
            if (! is_string($currentPassword) || ! Hash::check($currentPassword, $this->user()->password)) {
                $validator->errors()->add(
                    'current_password',
                    __('Profile::app.password_change.validation.current_invalid'),
                );
            }
        }];
    }
}

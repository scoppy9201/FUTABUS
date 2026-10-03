<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $submittedPhone = $this->input('phone');

        if (! is_string($submittedPhone)) {
            return;
        }

        $phone = preg_replace('/\s+/', '', $submittedPhone);

        if (str_starts_with($phone, '0')) {
            $phone = '+84'.substr($phone, 1);
        }

        $this->merge(['phone' => $phone]);
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:2', 'max:100'],
            'phone'         => ['required', 'string', 'regex:/^\+84(?:3|5|7|8|9)\d{8}$/', Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address'       => ['nullable', 'string', 'max:255'],
            'occupation'    => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png', 'max:1024'],
        ];
    }

    public function attributes(): array
    {
        return __('Profile::app.attributes');
    }

    public function messages(): array
    {
        return [
            'name.required'                 => __('Profile::app.validation.name_required'),
            'name.string'                   => __('Profile::app.validation.name_invalid'),
            'name.min'                      => __('Profile::app.validation.name_length'),
            'name.max'                      => __('Profile::app.validation.name_length'),
            'phone.required'                => __('Profile::app.validation.phone_required'),
            'phone.string'                  => __('Profile::app.validation.phone_invalid'),
            'phone.regex'                   => __('Profile::app.validation.phone_invalid'),
            'phone.unique'                  => __('Profile::app.validation.phone_taken'),
            'gender.in'                     => __('Profile::app.validation.gender_invalid'),
            'date_of_birth.date'            => __('Profile::app.validation.birth_date_invalid'),
            'date_of_birth.before_or_equal' => __('Profile::app.validation.birth_date_future'),
            'address.max'                   => __('Profile::app.validation.text_too_long'),
            'address.string'                => __('Profile::app.validation.text_invalid'),
            'occupation.max'                => __('Profile::app.validation.text_too_long'),
            'occupation.string'             => __('Profile::app.validation.text_invalid'),
            'avatar.image'                  => __('Profile::app.validation.avatar_invalid'),
            'avatar.mimes'                  => __('Profile::app.validation.avatar_invalid'),
            'avatar.uploaded'               => __('Profile::app.validation.avatar_invalid'),
            'avatar.max'                    => __('Profile::app.validation.avatar_large'),
        ];
    }
}

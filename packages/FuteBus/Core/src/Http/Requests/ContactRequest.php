<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department' => ['required', 'in:futabus'],
            'name'       => ['required', 'string', 'max:120'],
            'email'      => ['required', 'email', 'max:255'],
            'phone'      => ['required', 'regex:/^[0-9+\s.()-]{8,20}$/'],
            'subject'    => ['required', 'string', 'max:255'],
            'message'    => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return __('core::contact.validation');
    }

    public function attributes(): array
    {
        return __('core::contact.attributes');
    }
}

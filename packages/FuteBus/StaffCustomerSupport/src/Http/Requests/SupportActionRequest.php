<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('content'))) {
            $this->merge(['content' => trim($this->input('content'))]);
        }
    }

    public function rules(): array
    {
        $rules = ['version' => ['required', 'integer', 'min:0'], 'content' => ['required', 'string', 'max:3000'], 'idempotency_key' => ['required', 'uuid']];
        if ($this->routeIs('*.transfers.store')) {
            $rules['department_role_id'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }
}

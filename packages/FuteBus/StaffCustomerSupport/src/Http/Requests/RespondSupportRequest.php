<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'version'  => ['required', 'integer', 'min:0'], 'status' => ['required', Rule::in(['reviewing', 'resolved'])],
            'response' => ['required', 'string', 'max:3000'], 'idempotency_key' => ['required', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('response'))) {
            $this->merge(['response' => trim($this->input('response'))]);
        }
    }
}

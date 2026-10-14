<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['version' => ['required', 'integer', 'min:0']];
    }
}

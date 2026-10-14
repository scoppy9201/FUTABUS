<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code'   => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:20'],
            'name'   => ['nullable', 'string', 'max:255'], 'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(['active', 'expired', 'used', 'cancelled', 'refunded'])], 'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}

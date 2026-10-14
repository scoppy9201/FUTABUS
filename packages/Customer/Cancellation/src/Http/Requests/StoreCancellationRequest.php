<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ticket_id'       => ['required', 'integer', 'min:1'],
            'expected_refund' => ['required', 'integer', 'min:0'],
            'reason'          => ['required', Rule::in(['plans', 'mistake', 'urgent', 'alternative'])],
            'note'            => ['nullable', 'string', 'max:500'], 'consent' => ['required', 'accepted'],
        ];
    }
}

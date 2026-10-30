<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffCancelTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('ticket.cancel') === true;
    }

    public function rules(): array
    {
        return [
            'refund_method'   => ['required', Rule::in(['cash', 'request'])],
            'expected_refund' => ['required', 'integer', 'min:0'],
            'reason'          => ['required', Rule::in(['plans', 'mistake', 'urgent', 'alternative'])],
            'note'            => ['nullable', 'string', 'max:500'],
            'cash_paid'       => ['sometimes', 'boolean'],
        ];
    }
}

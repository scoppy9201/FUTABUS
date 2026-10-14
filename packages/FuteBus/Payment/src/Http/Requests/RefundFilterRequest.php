<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->hasPermissionTo('payment.view');
    }

    public function rules(): array
    {
        return ['status'                        => ['nullable', Rule::in(['refund_pending', 'refund_processing', 'refunded', 'refund_failed'])],
            'search'                            => ['nullable', 'string', 'max:100'],
            'period'                            => ['nullable', Rule::in(['7', '30', 'all'])],
            'selected'                          => ['nullable', 'integer', 'min:1'],
            'export'                            => ['nullable', Rule::in(['csv'])],
        ];
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManualRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->hasPermissionTo('payment.refund');
    }

    public function rules(): array
    {
        return [
            'version'        => ['required', 'integer', 'min:0'], 'action' => ['required', Rule::in(['claim', 'complete', 'fail'])],
            'amount'         => ['required_if:action,complete', 'nullable', 'integer', 'min:1'],
            'bank_reference' => ['required_if:action,complete', 'nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9][A-Za-z0-9._\/-]*$/'],
            'bank_paid_at'   => ['required_if:action,complete', 'nullable', 'date', 'before_or_equal:now'],
            'bank_verified'  => ['required_if:action,complete', 'accepted_if:action,complete'],
            'note'           => ['required_if:action,fail', 'nullable', 'string', 'max:1000'],
        ];
    }
}

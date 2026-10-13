<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Http\Requests;

use FuteBus\Payment\Http\Requests\PaymentPreviewRequest;
use Illuminate\Validation\Validator;

class TicketSaleRequest extends PaymentPreviewRequest
{
    public function rules(): array
    {
        return [...parent::rules(),
            'trip_id'         => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'uuid'],
            'quoted_fare'     => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (is_array($this->input('seats')) && count($this->input('seats')) !== (int) $this->input('quantity')) {
                $validator->errors()->add('seats', __('StaffTickets::sale.seat_count'));
            }
        }];
    }
}

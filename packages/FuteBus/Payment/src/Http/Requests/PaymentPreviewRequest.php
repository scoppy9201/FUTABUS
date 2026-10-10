<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use Illuminate\Validation\Rule;

class PaymentPreviewRequest extends TripSearchRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'direction'       => ['nullable', Rule::in(['outbound', 'return'])],
            'name'            => ['required', 'string', 'max:120'],
            'phone'           => ['required', 'regex:/^(?:0[35789]\\d{8}|\\+84[35789]\\d{8})$/'],
            'email'           => ['required', 'email', 'max:255'],
            'accept_terms'    => ['accepted'],
            'seats'           => ['required', 'array', 'min:1', 'max:5'],
            'seats.*'         => ['required', 'integer', 'distinct'],
            'pickup_mode'     => ['required', Rule::in(['station', 'transfer'])],
            'dropoff_mode'    => ['required', Rule::in(['station', 'transfer'])],
            'pickup_address'  => ['required_if:pickup_mode,transfer', 'nullable', 'string', 'max:255'],
            'dropoff_address' => ['required_if:dropoff_mode,transfer', 'nullable', 'string', 'max:255'],
        ];
    }
}

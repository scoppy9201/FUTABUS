<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

use FuteBus\Payment\Http\Requests\HoldTicketExchangeRequest;
use FuteBus\StaffTicketProcessing\Services\ExchangePassengerDetails;
use Illuminate\Validation\Rule;

class StaffHoldTicketExchangeRequest extends HoldTicketExchangeRequest
{
    public function rules(): array
    {
        return [...parent::rules(), ...ExchangePassengerDetails::rules(),
            'ticket_id'       => ['required', 'integer', Rule::in([(int) $this->route('ticket')])],
            'pickup_mode'     => ['sometimes', Rule::in(['station', 'transfer'])],
            'dropoff_mode'    => ['sometimes', Rule::in(['station', 'transfer'])],
            'pickup_address'  => ['required_if:pickup_mode,transfer', 'nullable', 'string', 'max:500'],
            'dropoff_address' => ['required_if:dropoff_mode,transfer', 'nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [...parent::attributes(),
            'passenger_name'  => __('StaffTicketProcessing::tickets.customer'),
            'passenger_phone' => __('StaffTicketProcessing::tickets.phone'),
        ];
    }
}

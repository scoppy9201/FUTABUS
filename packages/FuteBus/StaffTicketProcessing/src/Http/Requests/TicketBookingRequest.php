<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

class TicketBookingRequest extends TicketTripRequest
{
    public function rules(): array
    {
        return [...parent::rules(),
            'seats'          => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
            'outbound_trip'  => ['required_if:trip_type,round_trip', 'nullable', 'integer', 'min:1'],
            'outbound_seats' => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
        ];
    }
}

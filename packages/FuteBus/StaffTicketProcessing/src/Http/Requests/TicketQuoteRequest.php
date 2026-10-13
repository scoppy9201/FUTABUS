<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Requests;

class TicketQuoteRequest extends TicketTripRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'trip_id' => ['required', 'integer', 'min:1'], 'seats' => ['present', 'array', 'max:5'], 'seats.*' => ['integer', 'distinct']];
    }
}

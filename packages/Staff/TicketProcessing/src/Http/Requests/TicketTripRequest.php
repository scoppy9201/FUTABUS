<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Http\Requests;

use FuteBus\Core\Http\Requests\TripSearchRequest;

class TicketTripRequest extends TripSearchRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'direction' => ['nullable', 'in:outbound,return']];
    }
}

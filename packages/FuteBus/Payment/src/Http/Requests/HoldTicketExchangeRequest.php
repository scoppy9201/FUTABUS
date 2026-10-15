<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

class HoldTicketExchangeRequest extends TicketExchangeRequest
{
    public function rules(): array
    {
        return ['ticket_id' => ['required', 'integer'], 'trip_id' => ['required', 'integer'], 'seat_id' => ['required', 'integer'], 'pickup' => ['required', 'string', 'max:40'], 'dropoff' => ['required', 'string', 'max:40'], 'reason' => ['nullable', 'string', 'max:500']];
    }
}

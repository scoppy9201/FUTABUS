<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

class ExchangeTripsRequest extends TicketExchangeRequest
{
    public function rules(): array
    {
        return ['ticket_id' => ['required', 'integer'], 'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']];
    }
}

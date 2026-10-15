<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

class CompleteTicketExchangeRequest extends TicketExchangeRequest
{
    public function rules(): array
    {
        return ['consent' => ['required', 'accepted']];
    }
}

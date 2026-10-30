<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Requests;

use FuteBus\Payment\Services\TicketExchangeCatalog;

class ExchangeManagementRequest extends TicketExchangeRequest
{
    public function authorize(): bool
    {
        return $this->user() && app(TicketExchangeCatalog::class)->canOperate($this->user());
    }

    public function rules(): array
    {
        return ['code' => ['nullable', 'string', 'max:64'], 'tab' => ['nullable', 'in:tickets,history'],
            'page'     => ['nullable', 'integer', 'min:1']];
    }
}

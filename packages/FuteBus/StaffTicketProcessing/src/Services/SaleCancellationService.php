<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;

class SaleCancellationService
{
    public function __construct(private readonly CounterSaleService $sales, private readonly CashSaleService $cash) {}

    public function cancel(User $actor, string $sale, ?array $draft): array
    {
        abort_if($this->cash->show($actor, $sale) !== null, 409);
        if ($draft !== null && $draft['token'] === $sale && $draft['sale']['id'] === null) {
            abort_unless($draft['operator'] === $actor->id && $draft['company'] === $actor->bus_company_id, 404);

            return [...$draft['sale'], 'status' => 'expired'];
        }

        return $this->sales->cancelPreview($actor, $sale);
    }
}

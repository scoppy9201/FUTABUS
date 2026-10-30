<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Payment\Models\TicketExchange;
use Illuminate\Support\Collection;

class ExchangeSeatHolds
{
    public function rows(array $tripIds, ?string $ignore = null): Collection
    {
        return TicketExchange::query()->toBase()->whereIn('target_trip_id', $tripIds)->where('status', 'held')
            ->where('expires_at', '>', now())->when($ignore, fn ($q) => $q->where('token', '!=', $ignore))
            ->get(['target_trip_id as trip_id', 'seat_layout_id']);
    }

    public function conflicts(int $trip, array $seats, ?string $ignore = null): bool
    {
        return $this->rows([$trip], $ignore)->whereIn('seat_layout_id', $seats)->isNotEmpty();
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketExchangePolicy
{
    public const VERSION = 'free-equivalent-2026-10-15';

    public function eligible(object $ticket, bool $alreadyExchanged): void
    {
        if ($alreadyExchanged || $ticket->status !== 'active' || $ticket->booking_status !== 'confirmed'
            || $ticket->trip_status !== 'scheduled' || Carbon::parse($ticket->departure_time)->lt(now()->addHours(4))
            || str_starts_with($ticket->booking_code, 'DEMOHIST') || (int) $ticket->price <= 0
            || (float) $ticket->price !== (float) (int) $ticket->price) {
            throw ValidationException::withMessages(['ticket' => __('Payment::exchange.ineligible')]);
        }
    }

    public function equivalent(object $ticket, object $trip): bool
    {
        return (int) $trip->id !== (int) $ticket->trip_id && $trip->status === 'scheduled'
            && $trip->route_active && $trip->bus_status === 'active'
            && (int) $trip->bus_company_id === (int) $ticket->bus_company_id
            && (int) $trip->route_id === (int) $ticket->route_id
            && $trip->bus_type === $ticket->bus_type
            && (int) $trip->capacity === (int) $ticket->capacity
            && (int) ($trip->vehicle_type_id ?? 0) === (int) ($ticket->vehicle_type_id ?? 0)
            && (float) $trip->price === (float) $ticket->price
            && Carbon::parse($trip->departure_time)->gte(now()->addHours(4));
    }
}

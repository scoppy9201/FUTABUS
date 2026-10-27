<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use FuteBus\Payment\Services\TicketExchangePolicy;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class StaffExchangeEligibility
{
    public function validate(object $ticket, bool $alreadyExchanged): void
    {
        $reason = match (true) {
            $alreadyExchanged                                                                    => 'already_exchanged',
            $ticket->status === 'cancelled'                                                      => 'cancelled',
            $ticket->status === 'used'                                                           => 'used',
            $ticket->status !== 'active'                                                         => 'inactive',
            $ticket->booking_status !== 'confirmed'                                              => 'unconfirmed',
            $ticket->trip_status !== 'scheduled'                                                 => 'trip_unavailable',
            Carbon::parse($ticket->departure_time)->lt(now()->addHours(4))                       => 'too_late',
            str_starts_with($ticket->booking_code, 'DEMOHIST')                                   => 'demo',
            (int) $ticket->price <= 0 || (float) $ticket->price !== (float) (int) $ticket->price => 'invalid_fare',
            default                                                                              => null,
        };
        if ($reason !== null) {
            throw ValidationException::withMessages(['ticket' => __('StaffTicketProcessing::exchange.rejection.'.$reason)]);
        }
        app(TicketExchangePolicy::class)->eligible($ticket, $alreadyExchanged);
    }
}

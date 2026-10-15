<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketExchangeManagement
{
    public function __construct(
        private readonly TicketExchangeCatalog $catalog,
        private readonly TicketExchangePolicy $policy,
        private readonly BookingPaymentResolver $payments,
    ) {}

    public function tickets(User $actor, string $code): LengthAwarePaginator
    {
        $query = $this->catalog->managedTickets($actor);
        if ($code !== '') {
            $query->where(fn (Builder $q) => $q->where('tickets.ticket_code', $code)->orWhere('bookings.booking_code', $code));
        } else {
            $query->where('tickets.status', 'active')->where('bookings.status', 'confirmed')
                ->where('trips.status', 'scheduled')->where('trips.departure_time', '>', now());
        }
        $tickets = $query->orderBy('trips.departure_time')->orderBy('tickets.id')->paginate(15)->withQueryString();
        $this->eligibility($tickets->getCollection());

        return $tickets;
    }

    public function eligibility(Collection $tickets): void
    {
        $payments = [];
        foreach ($tickets as $ticket) {
            $ticket->eligible = true;
            $ticket->blocked_reason = null;
            try {
                $this->policy->eligible($ticket, $this->catalog->hasExchanged((int) $ticket->id));
                if (! array_key_exists($ticket->booking_id, $payments)) {
                    try {
                        $this->payments->verified((int) $ticket->booking_id);
                        $payments[$ticket->booking_id] = null;
                    } catch (ValidationException $error) {
                        $payments[$ticket->booking_id] = $error;
                    }
                }
                if ($payments[$ticket->booking_id]) {
                    throw $payments[$ticket->booking_id];
                }
            } catch (ValidationException $error) {
                $ticket->eligible = false;
                $ticket->blocked_reason = collect($error->errors())->flatten()->first();
            }
        }
    }

    public function history(User $actor, string $code): LengthAwarePaginator
    {
        $visible = $this->catalog->managedTickets($actor)->select('tickets.id');
        $query = DB::table('ticket_exchanges as exchanges')
            ->join('tickets as old', 'old.id', '=', 'exchanges.old_ticket_id')
            ->join('tickets as replacement', 'replacement.id', '=', 'exchanges.new_ticket_id')
            ->join('bookings as original_booking', 'original_booking.id', '=', 'old.booking_id')
            ->join('bookings as new_booking', 'new_booking.id', '=', 'replacement.booking_id')
            ->join('trips as old_trip', 'old_trip.id', '=', 'old.trip_id')
            ->join('trips as new_trip', 'new_trip.id', '=', 'replacement.trip_id')
            ->join('routes', 'routes.id', '=', 'old_trip.route_id')
            ->leftJoin('users as actor', 'actor.id', '=', 'exchanges.actor_id')
            ->where('exchanges.status', 'completed')->whereIn('exchanges.old_ticket_id', $visible)
            ->select('exchanges.id', 'exchanges.completed_at', 'exchanges.fee', 'old.ticket_code as old_code',
                'replacement.ticket_code as new_code', 'old.seat_code as old_seat', 'replacement.seat_code as new_seat',
                'old.passenger_name', 'old.passenger_phone', 'old_trip.departure_time as old_departure',
                'new_trip.departure_time as new_departure', 'replacement.booking_id as new_booking_id',
                'actor.name as actor_name', 'routes.origin_city', 'routes.destination_city');
        if ($code !== '') {
            $query->where(fn (Builder $q) => $q->where('old.ticket_code', $code)->orWhere('replacement.ticket_code', $code)
                ->orWhere('original_booking.booking_code', $code)->orWhere('new_booking.booking_code', $code));
        }

        return $query->orderByDesc('exchanges.completed_at')->orderByDesc('exchanges.id')->paginate(15)->withQueryString();
    }
}

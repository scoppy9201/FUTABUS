<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use FuteBus\BookingManagement\Services\BookingTripStops;
use FuteBus\RouteManagement\Services\RouteItineraryPresenter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketExchangeCatalog
{
    public function canOperate(User $actor): bool
    {
        return $actor->is_active && $actor->email_verified_at !== null && ($actor->isAdmin() || $actor->isBusCompany() || $actor->isStaff())
            && $actor->hasPermissionTo('ticket.view') && $actor->hasPermissionTo('ticket.exchange');
    }

    public function tickets(User $actor, int $booking): Builder
    {
        abort_unless($actor->is_active, 403);
        $query = $this->ticketQuery()->where('tickets.booking_id', $booking);

        return $query->where(function (Builder $scope) use ($actor): void {
            $scope->where('bookings.user_id', $actor->id)
                ->orWhere(fn ($guest) => $guest->whereNull('bookings.user_id')->where('customers.user_id', $actor->id));
            if ($this->canOperate($actor)) {
                if ($actor->isAdmin()) {
                    $scope->orWhereRaw('1 = 1');
                } elseif ($company = $this->companyId($actor)) {
                    $scope->orWhere('trips.bus_company_id', $company);
                }
            }
        });
    }

    public function managedTickets(User $actor): Builder
    {
        abort_unless($this->canOperate($actor), 403);
        $query = $this->ticketQuery();

        return $actor->isAdmin() ? $query : $query->where('trips.bus_company_id', $this->companyId($actor) ?: 0);
    }

    private function companyId(User $actor): ?int
    {
        $id = $actor->bus_company_id ?: DB::table('bus_companies')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($actor->email)])->value('id');

        return $id ? (int) $id : null;
    }

    private function ticketQuery(): Builder
    {
        return DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->select('tickets.*', 'bookings.status as booking_status', 'bookings.booking_code', 'bookings.user_id',
                'bookings.customer_id', 'bookings.notes', 'trips.status as trip_status', 'trips.departure_time',
                'trips.arrival_time', 'trips.route_id', 'trips.bus_company_id', 'buses.bus_type', 'buses.capacity',
                'buses.vehicle_type_id', 'routes.origin_city', 'routes.destination_city');
    }

    public function owned(User $actor, int $booking, int $ticket): object
    {
        $record = $this->tickets($actor, $booking)->where('tickets.id', $ticket)->first();
        abort_if($record === null, 404);

        return $record;
    }

    public function tripQuery(): Builder
    {
        return DB::table('trips')->join('buses', 'buses.id', '=', 'trips.bus_id')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->select('trips.*', 'buses.bus_type', 'buses.capacity', 'buses.vehicle_type_id', 'buses.status as bus_status',
                'routes.is_active as route_active', 'routes.origin_city', 'routes.destination_city', 'routes.distance_km')
            ->selectRaw('COALESCE(routes.origin_station, routes.origin_city) as origin, COALESCE(routes.destination_station, routes.destination_city) as destination');
    }

    public function trips(object $ticket, string $date): Collection
    {
        return $this->tripQuery()->where('trips.route_id', $ticket->route_id)->whereDate('trips.departure_time', $date)
            ->orderBy('trips.departure_time')->get()->filter(fn ($trip) => app(TicketExchangePolicy::class)->equivalent($ticket, $trip))
            ->map(fn ($trip) => $this->describe($trip))
            ->filter(fn (array $trip): bool => $trip['stops']['pickup'] !== [] && $trip['stops']['dropoff'] !== [])->values();
    }

    public function describe(object $trip, ?string $ignoreHold = null): array
    {
        $occupied = DB::table('booked_seats')->join('bookings', 'bookings.id', '=', 'booked_seats.booking_id')
            ->where('booked_seats.trip_id', $trip->id)->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])->pluck('seat_layout_id');
        $intents = DB::table('sepay_payment_intents')->where('status', 'pending')->where('expires_at', '>', now())
            ->where(fn ($q) => $q->where('trip_id', $trip->id)->orWhere('secondary_trip_id', $trip->id))->get();
        foreach ($intents as $intent) {
            $occupied = $occupied->merge(json_decode((int) $intent->trip_id === (int) $trip->id ? $intent->seat_ids : $intent->secondary_seat_ids, true) ?: []);
        }
        $occupied = $occupied->merge(app(ExchangeSeatHolds::class)->rows([(int) $trip->id], $ignoreHold)->pluck('seat_layout_id'))->map(fn ($id) => (int) $id);
        $seats = DB::table('seat_layouts')->where('bus_id', $trip->bus_id)->orderBy('deck')->orderBy('row_number')->orderBy('column_number')->get()
            ->map(fn ($seat): array => ['id' => $seat->id, 'code' => $seat->seat_code, 'row' => $seat->row_number,
                'column'                     => $seat->column_number, 'deck' => $seat->deck, 'sold' => ! $seat->is_available || $occupied->contains((int) $seat->id)]);
        $available = min($seats->where('sold', false)->count(), $trip->available_seats ?? PHP_INT_MAX);
        $stops = app(BookingTripStops::class)->forTrip((array) $trip);
        $routeStops = DB::table('route_stops')->where('route_id', $trip->route_id)->orderBy('stop_order')->get();
        if ($routeStops->isNotEmpty()) {
            foreach (['pickup', 'dropoff'] as $kind) {
                $stops[$kind] = array_values(array_filter($stops[$kind], fn (array $stop): bool => ctype_digit($stop['id'])));
            }
        }

        $departure = Carbon::parse($trip->departure_time);
        $arrival = Carbon::parse($trip->arrival_time);
        if ($routeStops->isEmpty()) {
            $routeStops = collect([
                (object) ['id' => 'pickup', 'name' => $trip->origin, 'address' => null, 'status' => 'active', 'stop_type' => 'pickup', 'offset_minutes' => 0],
                (object) ['id'       => 'dropoff', 'name' => $trip->destination, 'address' => null, 'status' => 'active', 'stop_type' => 'dropoff',
                    'offset_minutes' => (int) $departure->diffInMinutes($arrival)],
            ]);
        }
        $timeline = RouteItineraryPresenter::present($routeStops, $departure);
        foreach (['pickup' => $departure, 'dropoff' => $arrival] as $kind => $time) {
            if (collect($timeline)->contains(fn (array $stop): bool => (string) $stop['id'] === $kind)) {
                continue;
            }
            if (! collect($stops[$kind])->contains(fn (array $stop): bool => $stop['id'] === $kind)) {
                continue;
            }

            $fallback = [
                'id'             => $kind,
                'name'           => $kind === 'pickup' ? $trip->origin : $trip->destination,
                'address'        => null,
                'type'           => $kind,
                'active'         => true,
                'is_origin'      => $kind === 'pickup',
                'is_destination' => $kind === 'dropoff',
                'hour'           => $time->format('H:i'),
                'date'           => $time->format('d/m/Y'),
                'day_offset'     => (int) $departure->copy()->startOfDay()->diffInDays($time->copy()->startOfDay()),
                'expires_at'     => $time->getTimestamp() * 1000,
                'expired'        => $time->lessThanOrEqualTo(now()),
                'map_url'        => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($kind === 'pickup' ? $trip->origin : $trip->destination),
            ];
            if ($kind === 'pickup') {
                array_unshift($timeline, $fallback);
            } else {
                $timeline[] = $fallback;
            }
        }
        $activeTimeline = collect($timeline)->where('active', true)->values();
        $originId = $activeTimeline->first()['id'] ?? null;
        $destinationId = $activeTimeline->last()['id'] ?? null;
        foreach ($timeline as &$stop) {
            $stop['is_origin'] = $originId !== null && (string) $stop['id'] === (string) $originId;
            $stop['is_destination'] = $destinationId !== null && (string) $stop['id'] === (string) $destinationId;
        }
        unset($stop);

        return ['id'           => $trip->id, 'departure' => $departure->toIso8601String(), 'arrival' => $arrival->toIso8601String(),
            'fare'             => (int) $trip->price, 'available' => $available, 'seats' => $seats->all(), 'stops' => $stops,
            'departure_hour'   => $departure->format('H:i'), 'arrival_hour' => $arrival->format('H:i'),
            'duration_minutes' => (int) $departure->diffInMinutes($arrival), 'origin' => $trip->origin, 'destination' => $trip->destination,
            'distance_km'      => $trip->distance_km, 'vehicle_type' => $trip->bus_type, 'available_seats' => $available, 'price' => (int) $trip->price,
            'seat_decks'       => $seats->pluck('deck')->unique()->values()->all(), 'seat_columns' => max(1, (int) $seats->max('column')),
            'timeline'         => $timeline];
    }

    public function hasExchanged(int $ticket): bool
    {
        return DB::table('ticket_exchanges')->where('status', 'completed')
            ->where(fn ($q) => $q->where('old_ticket_id', $ticket)->orWhere('new_ticket_id', $ticket))->exists();
    }
}

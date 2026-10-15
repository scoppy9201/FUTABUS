<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use FuteBus\BookingManagement\Services\BookingTripStops;
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
        $query = DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where('tickets.booking_id', $booking)->select('tickets.*', 'bookings.status as booking_status',
                'bookings.booking_code', 'bookings.user_id', 'bookings.customer_id', 'bookings.notes',
                'trips.status as trip_status', 'trips.departure_time', 'trips.arrival_time', 'trips.route_id', 'trips.bus_company_id',
                'buses.bus_type', 'buses.capacity', 'buses.vehicle_type_id', 'routes.origin_city', 'routes.destination_city');

        return $query->where(function (Builder $scope) use ($actor): void {
            $scope->where('bookings.user_id', $actor->id)
                ->orWhere(fn ($guest) => $guest->whereNull('bookings.user_id')->where('customers.user_id', $actor->id));
            if ($this->canOperate($actor)) {
                if ($actor->isAdmin()) {
                    $scope->orWhereRaw('1 = 1');
                } elseif ($company = $actor->bus_company_id ?: DB::table('bus_companies')->whereRaw('LOWER(email) = ?', [mb_strtolower($actor->email)])->value('id')) {
                    $scope->orWhere('trips.bus_company_id', $company);
                }
            }
        });
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
                'routes.is_active as route_active', 'routes.origin_city', 'routes.destination_city')
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
        if (DB::table('route_stops')->where('route_id', $trip->route_id)->exists()) {
            foreach (['pickup', 'dropoff'] as $kind) {
                $stops[$kind] = array_values(array_filter($stops[$kind], fn (array $stop): bool => ctype_digit($stop['id'])));
            }
        }

        return ['id'    => $trip->id, 'departure' => Carbon::parse($trip->departure_time)->toIso8601String(), 'arrival' => Carbon::parse($trip->arrival_time)->toIso8601String(), 'fare' => (int) $trip->price,
            'available' => $available, 'seats' => $seats->all(), 'stops' => $stops];
    }

    public function hasExchanged(int $ticket): bool
    {
        return DB::table('ticket_exchanges')->where('status', 'completed')
            ->where(fn ($q) => $q->where('old_ticket_id', $ticket)->orWhere('new_ticket_id', $ticket))->exists();
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Controllers;

use FuteBus\Payment\Services\BookingPaymentResolver;
use FuteBus\Payment\Services\TicketExchangeCatalog;
use FuteBus\Payment\Services\TicketExchangePolicy;
use FuteBus\StaffTicketProcessing\Http\Requests\StaffExchangeTripsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StaffExchangeController extends Controller
{
    public function __construct(
        private readonly TicketExchangeCatalog $catalog,
        private readonly TicketExchangePolicy $policy,
        private readonly BookingPaymentResolver $payments,
    ) {}

    public function context(Request $request, int $ticket): JsonResponse
    {
        $record = $this->ticket($request, $ticket);

        return response()->json(['ticket' => [
            'id'        => $record->id, 'code' => $record->ticket_code, 'booking_id' => $record->booking_id,
            'customer'  => $record->passenger_name, 'phone' => $record->passenger_phone,
            'route'     => $record->origin_city.' → '.$record->destination_city,
            'departure' => Carbon::parse($record->departure_time)->toIso8601String(),
            'origin'    => $record->origin_city, 'destination' => $record->destination_city,
            'fare'      => (int) $record->price, 'seat' => $record->seat_code,
        ], 'urls' => [
            'trips' => route('staff.tickets.exchange.trips', $ticket),
            'hold'  => route('exchange.hold', $record->booking_id),
            'base'  => route('exchange.show', $record->booking_id),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function trips(StaffExchangeTripsRequest $request, int $ticket): JsonResponse
    {
        $record = $this->ticket($request, $ticket);
        $choices = $this->catalog->trips($record, $request->validated('date'));
        $trips = $choices->map(function (array $choice) use ($record): array {
            $trip = $this->catalog->tripQuery()->where('trips.id', $choice['id'])->first();
            $departure = Carbon::parse($choice['departure']);
            $arrival = Carbon::parse($choice['arrival']);

            return [...$choice,
                'route_id'         => $trip->route_id, 'departure_time' => $choice['departure'], 'arrival_time' => $choice['arrival'],
                'departure_hour'   => $departure->format('H:i'), 'arrival_hour' => $arrival->format('H:i'),
                'duration_minutes' => $departure->diffInMinutes($arrival), 'distance_km' => null,
                'origin'           => $record->origin_city, 'destination' => $record->destination_city,
                'price'            => $choice['fare'], 'available_seats' => $choice['available'], 'vehicle_type' => $trip->bus_type,
                'row_options'      => ['front', 'middle', 'back'], 'deck_options' => array_values(array_unique(array_column($choice['seats'], 'deck'))),
                'seat_decks'       => array_values(array_unique(array_column($choice['seats'], 'deck'))),
                'seat_columns'     => max(1, ...array_column($choice['seats'], 'column')),
            ];
        })->all();
        $html = view('StaffTicketProcessing::partials.trip-results', [
            'trips'     => $trips, 'returnTrips' => [], 'from' => $record->origin_city, 'to' => $record->destination_city,
            'roundTrip' => false, 'departureDate' => $request->validated('date'), 'returnDate' => null,
        ])->render();

        return response()->json(['trips' => $trips, 'html' => $html])->header('Cache-Control', 'private, no-store');
    }

    private function ticket(Request $request, int $ticket): object
    {
        abort_unless($this->catalog->canOperate($request->user()), 403);
        $booking = DB::table('tickets')->where('id', $ticket)->value('booking_id');
        abort_if($booking === null, 404);
        $record = $this->catalog->owned($request->user(), (int) $booking, $ticket);
        $this->policy->eligible($record, $this->catalog->hasExchanged($ticket));
        $this->payments->verified((int) $booking);

        return $record;
    }
}

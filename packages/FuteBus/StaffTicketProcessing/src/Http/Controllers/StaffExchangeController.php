<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Controllers;

use FuteBus\Payment\Http\Requests\CompleteTicketExchangeRequest;
use FuteBus\Payment\Services\BookingPaymentResolver;
use FuteBus\Payment\Services\TicketExchangeCatalog;
use FuteBus\Payment\Services\TicketExchangePolicy;
use FuteBus\Payment\Services\TicketQrService;
use FuteBus\StaffTicketProcessing\Http\Requests\StaffExchangeTripsRequest;
use FuteBus\StaffTicketProcessing\Http\Requests\StaffHoldTicketExchangeRequest;
use FuteBus\StaffTicketProcessing\Services\StaffExchangeEligibility;
use FuteBus\StaffTicketProcessing\Services\StaffExchangeSuccessPresenter;
use FuteBus\StaffTicketProcessing\Services\StaffTicketExchangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $notes = json_decode($record->notes ?? '', true) ?: [];

        return response()->json(['ticket' => [
            'id'           => $record->id, 'code' => $record->ticket_code, 'booking_id' => $record->booking_id,
            'customer'     => $record->passenger_name, 'phone' => $record->passenger_phone,
            'route'        => $record->origin_city.' → '.$record->destination_city,
            'departure'    => Carbon::parse($record->departure_time)->toIso8601String(),
            'origin'       => $record->origin_city, 'destination' => $record->destination_city,
            'fare'         => (int) $record->price, 'seat' => $record->seat_code,
            'booking_code' => $record->booking_code,
            'arrival'      => Carbon::parse($record->arrival_time)->toIso8601String(),
            'pickup'       => is_array($notes['pickup'] ?? null) ? ($notes['pickup']['address'] ?? $notes['pickup']['name'] ?? null) : ($notes['pickup'] ?? null),
            'dropoff'      => is_array($notes['dropoff'] ?? null) ? ($notes['dropoff']['address'] ?? $notes['dropoff']['name'] ?? null) : ($notes['dropoff'] ?? null),
        ], 'urls' => [
            'trips' => route('staff.tickets.exchange.trips', $ticket),
            'hold'  => route('staff.tickets.exchange.hold', $ticket),
            'base'  => url('/nhan-vien/doi-ve/dat-cho/'.$record->booking_id),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function trips(StaffExchangeTripsRequest $request, int $ticket): JsonResponse
    {
        $record = $this->ticket($request, $ticket);
        $choices = $this->catalog->trips($record, $request->validated('date'));
        $trips = $choices->map(function (array $choice) use ($record): array {
            $seats = collect($choice['seats']);
            $freeSeats = $seats->where('sold', false);
            $rowCount = max(1, (int) $seats->max('row'));
            $rowOptions = $freeSeats->map(fn (array $seat): string => $seat['row'] <= (int) ceil($rowCount / 3)
                ? 'front' : ($seat['row'] > (int) floor($rowCount * 2 / 3) ? 'back' : 'middle'))->unique()->values()->all();

            return [...$choice,
                'route_id'    => $record->route_id, 'departure_time' => $choice['departure'], 'arrival_time' => $choice['arrival'],
                'row_options' => $rowOptions, 'deck_options' => $freeSeats->pluck('deck')->unique()->values()->all(),
            ];
        })->all();
        $html = view('StaffTicketProcessing::partials.trip-results', [
            'trips'     => $trips, 'returnTrips' => [], 'from' => $record->origin_city, 'to' => $record->destination_city,
            'roundTrip' => false, 'departureDate' => $request->validated('date'), 'returnDate' => null, 'exchangeSearch' => true,
        ])->render();

        return response()->json(['trips' => $trips, 'html' => $html])->header('Cache-Control', 'private, no-store');
    }

    public function hold(StaffHoldTicketExchangeRequest $request, StaffTicketExchangeService $service, int $ticket): JsonResponse
    {
        $record = $this->ticket($request, $ticket);
        $hold = $service->hold($request->user(), (int) $record->booking_id, $request->validated());

        return response()->json(['token' => $hold->token, 'expires_at' => $hold->expires_at->toIso8601String(),
            'fare'                       => $hold->fare, 'fee' => $hold->fee,
            'passenger'                  => $hold->snapshot['passenger']['after'],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function complete(CompleteTicketExchangeRequest $request, StaffTicketExchangeService $service, TicketQrService $qr, StaffExchangeSuccessPresenter $success, int $booking, string $token): JsonResponse
    {
        $exchange = $service->complete($request->user(), $booking, $token);
        $ticket = DB::table('tickets')->where('id', $exchange->new_ticket_id)->first();
        $qrImage = $qr->dataUri($ticket->ticket_code);

        return response()->json([
            'code'         => $ticket->ticket_code, 'qr' => $qrImage,
            'html'         => $success->render($exchange, $qrImage),
            'url'          => route('staff.exchange.manage', ['code' => $ticket->ticket_code]),
            'completed_at' => $exchange->completed_at->toIso8601String(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function release(Request $request, StaffTicketExchangeService $service, int $booking, string $token): Response
    {
        $service->release($request->user(), $booking, $token);

        return response()->noContent();
    }

    private function ticket(Request $request, int $ticket): object
    {
        abort_unless($this->catalog->canOperate($request->user()), 403);
        $booking = DB::table('tickets')->where('id', $ticket)->value('booking_id');
        abort_if($booking === null, 404);
        $record = $this->catalog->owned($request->user(), (int) $booking, $ticket);
        app(StaffExchangeEligibility::class)->validate($record, $this->catalog->hasExchanged($ticket));
        $this->payments->verified((int) $booking);

        return $record;
    }
}

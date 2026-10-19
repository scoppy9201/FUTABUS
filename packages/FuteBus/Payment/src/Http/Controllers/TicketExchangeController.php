<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Http\Requests\CompleteTicketExchangeRequest;
use FuteBus\Payment\Http\Requests\ExchangeManagementRequest;
use FuteBus\Payment\Http\Requests\ExchangeTripsRequest;
use FuteBus\Payment\Http\Requests\HoldTicketExchangeRequest;
use FuteBus\Payment\Services\TicketExchangeCatalog;
use FuteBus\Payment\Services\TicketExchangeManagement;
use FuteBus\Payment\Services\TicketExchangePolicy;
use FuteBus\Payment\Services\TicketExchangeService;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketExchangeController extends Controller
{
    public function show(Request $request, TicketExchangeCatalog $catalog, TicketExchangeManagement $management, int $booking): Response
    {
        $tickets = $catalog->tickets($request->user(), $booking)->get();
        abort_if($tickets->isEmpty(), 404);
        $management->eligibility($tickets);

        return response()->view('Payment::exchange.wizard', compact('tickets', 'booking'))->header('Cache-Control', 'private, no-store');
    }

    public function trips(ExchangeTripsRequest $request, TicketExchangeCatalog $catalog, TicketExchangePolicy $policy, int $booking): JsonResponse
    {
        $data = $request->validated();
        $ticket = $catalog->owned($request->user(), $booking, (int) $data['ticket_id']);
        $policy->eligible($ticket, $catalog->hasExchanged((int) $ticket->id));

        return response()->json(['trips' => $catalog->trips($ticket, $data['date'])])->header('Cache-Control', 'private, no-store');
    }

    public function hold(HoldTicketExchangeRequest $request, TicketExchangeService $service, int $booking): JsonResponse
    {
        $data = $request->validated();
        $hold = $service->hold($request->user(), $booking, $data);

        return response()->json(['token' => $hold->token, 'expires_at' => $hold->expires_at->toIso8601String(),
            'fare'                       => $hold->fare, 'fee' => 0])->header('Cache-Control', 'no-store');
    }

    public function complete(CompleteTicketExchangeRequest $request, TicketExchangeService $service, TicketQrService $qr, int $booking, string $token): JsonResponse
    {

        $exchange = $service->complete($request->user(), $booking, $token);
        $ticket = DB::table('tickets')->where('id', $exchange->new_ticket_id)->first();
        $url = (int) $exchange->user_id === $request->user()->id
            ? route('profile.tickets.show', $exchange->new_booking_id)
            : route($request->user()->isStaff() ? 'staff.exchange.manage' : 'exchange.manage', ['code' => $ticket->ticket_code]);

        return response()->json(['code' => $ticket->ticket_code, 'qr' => $qr->dataUri($ticket->ticket_code),
            'url'                       => $url,
            'completed_at'              => Carbon::parse($exchange->completed_at)->toIso8601String()])->header('Cache-Control', 'no-store');
    }

    public function release(Request $request, TicketExchangeService $service, int $booking, string $token): Response
    {
        $service->release($request->user(), $booking, $token);

        return response()->noContent();
    }

    public function manage(ExchangeManagementRequest $request, TicketExchangeManagement $management): View
    {
        $input = $request->validated();
        $code = trim($input['code'] ?? '');
        $tab = $input['tab'] ?? 'tickets';
        $records = $tab === 'history' ? $management->history($request->user(), $code) : $management->tickets($request->user(), $code);

        return view($request->routeIs('staff.*') ? 'Payment::exchange.staff' : 'Payment::exchange.manage',
            ['records' => $records, 'tab' => $tab, 'code' => $code, 'section' => $request->routeIs('staff.*') ? 'tickets' : 'exchanges']);
    }
}

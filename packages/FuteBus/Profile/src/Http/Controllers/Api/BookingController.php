<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers\Api;

use FuteBus\Profile\Http\Requests\TicketHistoryRequest;
use FuteBus\Profile\Services\TicketHistoryActionPolicy;
use FuteBus\Profile\Services\TicketHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BookingController extends Controller
{
    public function index(
        TicketHistoryRequest $request,
        TicketHistoryService $history,
        TicketHistoryActionPolicy $actions,
    ): JsonResponse {
        $bookings = $history->search($request->user(), $request->validated());
        $bookings->getCollection()->transform(fn (object $booking): array => [
            ...$this->summary($booking),
            'can_contact_for_change' => $actions->canContactForChange($booking),
        ]);

        return response()->json($bookings);
    }

    public function show(Request $request, TicketHistoryService $history, int $booking): JsonResponse
    {
        $ticket = $history->findForUser($request->user(), $booking);
        abort_if($ticket === null, 404);

        return response()->json(['data' => $this->summary($ticket)]);
    }

    private function summary(object $booking): array
    {
        return [
            'id'             => $booking->id,
            'booking_code'   => $booking->booking_code,
            'seat_count'     => $booking->seat_count,
            'total_amount'   => $booking->total_amount,
            'status'         => $booking->status,
            'payment_status' => $booking->payment_status,
            'departure_time' => $booking->departure_time,
            'origin'         => $booking->origin_city,
            'destination'    => $booking->destination_city,
        ];
    }
}

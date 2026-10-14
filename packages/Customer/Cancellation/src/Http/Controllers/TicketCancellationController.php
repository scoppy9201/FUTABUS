<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Http\Controllers;

use FuteBus\Cancellation\Http\Requests\CancellationQuoteRequest;
use FuteBus\Cancellation\Http\Requests\StoreCancellationRequest;
use FuteBus\Cancellation\Services\TicketCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class TicketCancellationController extends Controller
{
    public function show(Request $request, TicketCancellationService $service, int $booking): Response
    {
        $tickets = $service->owned($request->user(), $booking)->get();
        abort_if($tickets->isEmpty(), 404);
        $receipts = DB::table('ticket_cancellations')->join('ticket_refunds', 'ticket_refunds.cancellation_id', '=', 'ticket_cancellations.id')
            ->whereIn('ticket_cancellations.ticket_id', $tickets->pluck('id'))
            ->select('ticket_cancellations.reference', 'ticket_cancellations.refund_amount', 'ticket_refunds.status')->get();

        return response()->view('Cancellation::modal', compact('tickets', 'booking', 'receipts'))->header('Cache-Control', 'private, no-store');
    }

    public function quote(CancellationQuoteRequest $request, TicketCancellationService $service, int $booking): JsonResponse
    {
        $input = $request->validated();

        return response()->json($service->quote($request->user(), $booking, (int) $input['ticket_id']))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(StoreCancellationRequest $request, TicketCancellationService $service, int $booking): JsonResponse
    {
        $input = $request->validated();
        $cancellation = $service->cancel($request->user(), $booking, $input);

        return response()->json(['reference' => $cancellation->reference,
            'fee'                            => $cancellation->fee_amount, 'refund' => $cancellation->refund_amount,
            'refund_status'                  => DB::table('ticket_refunds')->where('cancellation_id', $cancellation->id)->value('status')]);
    }

    public function status(Request $request, TicketCancellationService $service, int $booking): JsonResponse
    {
        $tickets = $service->owned($request->user(), $booking)->pluck('tickets.id');
        abort_if($tickets->isEmpty(), 404);
        $receipts = DB::table('ticket_cancellations')->join('ticket_refunds', 'ticket_refunds.cancellation_id', '=', 'ticket_cancellations.id')
            ->whereIn('ticket_cancellations.ticket_id', $tickets)
            ->select('ticket_cancellations.reference', 'ticket_refunds.status')->get();

        return response()->json(['receipts' => $receipts])->header('Cache-Control', 'private, no-store');
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Http\Controllers;

use FuteBus\Cancellation\Services\TicketCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function quote(Request $request, TicketCancellationService $service, int $booking): JsonResponse
    {
        $input = $request->validate(['ticket_id' => ['required', 'integer', 'min:1']]);

        return response()->json($service->quote($request->user(), $booking, (int) $input['ticket_id']))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, TicketCancellationService $service, int $booking): JsonResponse
    {
        $input = $request->validate([
            'ticket_id'       => ['required', 'integer', 'min:1'],
            'expected_refund' => ['required', 'integer', 'min:0'],
            'reason'          => ['required', Rule::in(['plans', 'mistake', 'urgent', 'alternative'])],
            'note'            => ['nullable', 'string', 'max:500'], 'consent' => ['required', 'accepted'],
        ]);
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

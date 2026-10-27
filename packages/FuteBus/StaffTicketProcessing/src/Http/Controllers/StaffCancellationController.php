<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Controllers;

use FuteBus\Payment\Services\TicketQrService;
use FuteBus\StaffTicketProcessing\Http\Requests\StaffCancelTicketRequest;
use FuteBus\StaffTicketProcessing\Services\StaffTicketCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class StaffCancellationController extends Controller
{
    public function context(Request $request, StaffTicketCancellationService $service, int $ticket): JsonResponse
    {
        $data = $service->quote($request->user(), $this->booking($ticket), $ticket);
        $data['qr'] = app(TicketQrService::class)->dataUri($data['ticket']->ticket_code);
        $data['details_url'] = route('staff.ticket-lookup', ['code' => $data['ticket']->ticket_code]);
        $notes = json_decode($data['ticket']->notes ?? '', true) ?: [];
        foreach (['pickup', 'dropoff'] as $kind) {
            $stop = $notes[$kind] ?? null;
            $data[$kind] = is_array($stop) ? ($stop['address'] ?? $stop['name'] ?? '—') : ($stop ?: '—');
        }

        return response()->json($data)
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(StaffCancelTicketRequest $request, StaffTicketCancellationService $service, int $ticket): JsonResponse
    {
        $record = $service->cancel($request->user(), $this->booking($ticket), [...$request->validated(), 'ticket_id' => $ticket]);
        $refund = DB::table('ticket_refunds')->where('cancellation_id', $record->id)->first();

        return response()->json(['reference' => $record->reference, 'fee' => (int) $record->fee_amount,
            'refund'                         => (int) $record->refund_amount, 'status' => $refund->status,
            'cash_reference'                 => $refund->status === 'refunded' ? $refund->bank_reference : null])
            ->header('Cache-Control', 'private, no-store');
    }

    private function booking(int $ticket): int
    {
        $booking = DB::table('tickets')->where('id', $ticket)->value('booking_id');
        abort_if($booking === null, 404);

        return (int) $booking;
    }
}

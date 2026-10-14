<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Http\Controllers;

use FuteBus\Payment\Services\TicketQrService;
use FuteBus\TicketManagement\Http\Requests\TicketLookupRequest;
use FuteBus\TicketManagement\Services\TicketLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class TicketLookupController extends Controller
{
    public function index(): View
    {
        return view('TicketManagement::ticket-lookup');
    }

    public function search(TicketLookupRequest $request, TicketLookupService $lookup, TicketQrService $ticketQr): View
    {
        $input = $request->validated();
        $result = $lookup->find($input['phone'], $input['ticket_code']);
        $qrCodes = $result === null ? [] : $result['tickets']
            ->mapWithKeys(fn (object $ticket): array => [$ticket->ticket_code => $ticketQr->dataUri($ticket->ticket_code)])
            ->all();

        return view('TicketManagement::ticket-lookup', [
            'input'    => $input,
            'result'   => $result,
            'qrCodes'  => $qrCodes,
            'notFound' => $result === null,
        ]);
    }
}

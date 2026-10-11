<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Http\Requests\TicketLookupRequest;
use FuteBus\Core\Services\TicketLookupService;
use FuteBus\Payment\Services\TicketQrService;
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

<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Http\Requests\TicketLookupRequest;
use FuteBus\Core\Services\TicketLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class TicketLookupController extends Controller
{
    public function index(): View
    {
        return view('core::ticket-lookup');
    }

    public function search(TicketLookupRequest $request, TicketLookupService $lookup): View
    {
        $input = $request->validated();
        $result = $lookup->find($input['phone'], $input['ticket_code']);

        return view('core::ticket-lookup', [
            'input'    => $input,
            'result'   => $result,
            'notFound' => $result === null,
        ]);
    }
}

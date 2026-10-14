<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Http\Controllers\Api;

use FuteBus\TicketManagement\Http\Requests\TicketLookupRequest;
use FuteBus\TicketManagement\Services\TicketLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TicketLookupController extends Controller
{
    public function __invoke(TicketLookupRequest $request, TicketLookupService $lookup): JsonResponse
    {
        $input = $request->validated();
        $result = $lookup->find($input['phone'], $input['ticket_code']);
        abort_if($result === null, 404);

        return response()->json(['data' => [
            'booking'        => $result['booking'],
            'tickets'        => $result['tickets'],
            'payment_status' => $result['paymentStatus'],
        ]])->header('Cache-Control', 'no-store');
    }
}

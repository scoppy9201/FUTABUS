<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Http\Requests\SePayWebhookRequest;
use FuteBus\Payment\Services\SePayPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SePayWebhookController extends Controller
{
    public function __invoke(
        SePayWebhookRequest $request,
        SePayPaymentService $payments
    ): JsonResponse {
        $data = $request->validated();

        $payments->receive(array_merge($request->all(), $data));

        return response()->json(['success' => true]);
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Services\SePayPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SePayWebhookController extends Controller
{
    public function __invoke(Request $request, SePayPaymentService $payments): JsonResponse
    {
        $key = config('services.sepay.webhook_key');
        if (! $payments->ready()) {
            return response()->json(['success' => false], 503);
        }

        if (! hash_equals('Apikey '.$key, (string) $request->header('Authorization'))) {
            return response()->json(['success' => false], 401);
        }

        $data = Validator::make($request->all(), [
            'id'             => ['required', 'integer', 'min:1'],
            'transferType'   => ['required', 'in:in,out'],
            'transferAmount' => ['required', 'integer', 'min:0'],
            'accountNumber'  => ['required', 'string', 'max:32'],
            'code'           => ['nullable', 'string', 'max:64'],
        ])->validate();

        $payments->receive(array_merge($request->all(), $data));

        return response()->json(['success' => true]);
    }
}

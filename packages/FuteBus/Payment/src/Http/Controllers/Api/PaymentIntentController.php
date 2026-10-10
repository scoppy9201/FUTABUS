<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers\Api;

use FuteBus\Payment\Http\Requests\PaymentPreviewRequest;
use FuteBus\Payment\Services\PaymentPreviewBuilder;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PaymentIntentController extends Controller
{
    public function store(
        PaymentPreviewRequest $request,
        PaymentPreviewBuilder $builder,
        SePayPaymentService $payments,
        SePayQrService $qr,
        int $trip,
    ): JsonResponse {
        abort_unless($payments->ready(), 503);

        $preview = $builder->build($request->validated(), $trip, $request->user()->id);
        $payments->createIntent($preview);
        $intent = DB::table('sepay_payment_intents')->where('token', $preview['token'])->first();

        return response()->json(['data' => $this->resource($intent, $qr, true)], 201)
            ->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, SePayQrService $qr, string $intent): JsonResponse
    {
        $record = $this->ownedIntent($request, $intent);

        if ($record->status === 'pending' && $record->expires_at <= now()->toDateTimeString()) {
            DB::table('sepay_payment_intents')->where('id', $record->id)->where('status', 'pending')
                ->update(['status' => 'expired', 'updated_at' => now()]);
            $record = DB::table('sepay_payment_intents')->where('id', $record->id)->first();
        }

        return response()->json(['data' => $this->resource($record, $qr, false)])
            ->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request, string $intent): JsonResponse
    {
        $status = DB::transaction(function () use ($request, $intent): string {
            $record = DB::table('sepay_payment_intents')->where('token', $intent)->lockForUpdate()->first();
            abort_if($record === null || ! $this->belongsTo($record, $request->user()->id), 404);

            if ($record->status === 'pending') {
                DB::table('sepay_payment_intents')->where('id', $record->id)
                    ->update(['status' => 'expired', 'updated_at' => now()]);

                return 'expired';
            }

            return $record->status;
        });

        abort_if(in_array($status, ['paid', 'needs_review'], true), 409);

        return response()->json(['data' => ['status' => $status]])
            ->header('Cache-Control', 'no-store');
    }

    private function ownedIntent(Request $request, string $token): object
    {
        $intent = DB::table('sepay_payment_intents')->where('token', $token)->first();
        abort_if($intent === null || ! $this->belongsTo($intent, $request->user()->id), 404);

        return $intent;
    }

    private function belongsTo(object $intent, int $userId): bool
    {
        $snapshot = json_decode($intent->snapshot, true);

        return is_array($snapshot) && (int) ($snapshot['user_id'] ?? 0) === $userId;
    }

    private function resource(object $intent, SePayQrService $qr, bool $includeCode): array
    {
        $data = [
            'id'         => $intent->token,
            'status'     => $intent->status,
            'amount'     => (int) $intent->amount,
            'expires_at' => $intent->expires_at,
            'qr_url'     => $intent->status === 'pending'
                ? $qr->generate((int) $intent->amount, $intent->code)
                : null,
        ];

        if ($includeCode) {
            $data['reference'] = $intent->code;
        }

        if ($intent->status === 'paid' && $intent->booking_id !== null) {
            $data['booking'] = DB::table('bookings')->where('id', $intent->booking_id)
                ->first(['id', 'booking_code']);
        }

        return $data;
    }
}

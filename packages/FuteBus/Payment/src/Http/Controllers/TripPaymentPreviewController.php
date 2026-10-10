<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Http\Requests\PaymentPreviewRequest;
use FuteBus\Payment\Services\PaymentPreviewBuilder;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayQrService;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TripPaymentPreviewController extends Controller
{
    public function store(
        PaymentPreviewRequest $request,
        PaymentPreviewBuilder $builder,
        SePayPaymentService $payments,
        int $trip,
    ): RedirectResponse {
        $preview = $builder->build($request->validated(), $trip, $request->user()?->id);

        if ($payments->ready()) {
            $payments->createIntent($preview);
        }
        $request->session()->put('trip_payment_preview', $preview);

        return redirect()->route('trip-payment-preview.show', ['draft' => $preview['token']]);
    }

    public function activate(Request $request, SePayPaymentService $payments, string $draft): RedirectResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft
            || ($preview['created_at'] ?? 0) + 600 <= now()->timestamp) {
            return redirect()->to(route('home').'#trip-search');
        }

        if ($payments->ready() && ! DB::table('sepay_payment_intents')->where('token', $draft)->exists()) {
            $payments->createIntent($preview);
        }

        return redirect()->route('trip-payment-preview.show', ['draft' => $draft]);
    }

    public function show(Request $request, SePayQrService $sePay, SePayPaymentService $payments, TicketQrService $ticketQr, string $draft): View|RedirectResponse
    {
        $intent = DB::table('sepay_payment_intents')->where('token', $draft)->first();
        if ($intent?->status === 'paid') {
            $tickets = DB::table('tickets')->where('booking_id', $intent->booking_id)
                ->orderBy('id')->get(['ticket_code', 'seat_code', 'price'])
                ->map(fn (object $ticket): array => [
                    'code'      => $ticket->ticket_code,
                    'seat'      => $ticket->seat_code,
                    'price'     => $ticket->price,
                    'qr'        => $ticketQr->dataUri($ticket->ticket_code),
                ]);

            return view('Payment::trip-payment-success', [
                'bookingCode' => DB::table('bookings')->where('id', $intent->booking_id)->value('booking_code'),
                'preview'     => json_decode($intent->snapshot, true),
                'total'       => $intent->amount,
                'tickets'     => $tickets,
            ]);
        }
        if ($intent?->status === 'needs_review') {
            return view('Payment::trip-payment-review', ['paymentCode' => $intent->code]);
        }

        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return redirect()->to(route('home').'#trip-search');
        }

        if (($preview['created_at'] ?? 0) + 600 <= now()->timestamp
            || ($intent !== null && $intent->expires_at <= now()->toDateTimeString())) {
            if ($intent !== null && $intent->status === 'pending') {
                DB::table('sepay_payment_intents')->where('id', $intent->id)
                    ->update(['status' => 'expired', 'updated_at' => now()]);
            }
            $request->session()->forget('trip_payment_preview');

            return redirect()->to(route('home').'#trip-search');
        }

        $qrUrl = $intent !== null && $intent->status === 'pending' && $payments->ready()
            ? $sePay->generate((int) $intent->amount, $intent->code)
            : null;

        return view('Payment::trip-payment-preview', [
            'preview'            => $preview,
            'sePayQrUrl'         => $qrUrl,
            'paymentEnabled'     => $qrUrl !== null,
            'paymentCanActivate' => $intent === null && $payments->ready(),
        ]);
    }

    public function cancel(Request $request, string $draft): RedirectResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return redirect()->to(route('home').'#trip-search');
        }

        $paymentStatus = DB::transaction(function () use ($draft): ?string {
            $intent = DB::table('sepay_payment_intents')->where('token', $draft)->lockForUpdate()->first();
            if ($intent === null) {
                return null;
            }
            if ($intent->status === 'pending') {
                DB::table('sepay_payment_intents')->where('id', $intent->id)
                    ->update(['status' => 'expired', 'updated_at' => now()]);
            }

            return $intent->status;
        });

        if (in_array($paymentStatus, ['paid', 'needs_review'], true)) {
            return redirect()->route('trip-payment-preview.show', ['draft' => $draft]);
        }

        $request->session()->forget('trip_payment_preview');

        return redirect()->route('trip-booking.show', array_merge(
            ['trip' => $preview['trip']['id']],
            $preview['criteria'],
            ['direction' => $preview['direction'], 'seats' => implode(',', $preview['seat_ids'])]
        ));
    }

    public function status(Request $request, string $draft): JsonResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return response()->json(['status' => 'expired'], 200)->header('Cache-Control', 'no-store');
        }

        $intent = DB::table('sepay_payment_intents')->where('token', $draft)->first();
        $status = $intent?->status ?? 'preview';
        if ($status === 'pending' && $intent->expires_at <= now()->toDateTimeString()) {
            DB::table('sepay_payment_intents')->where('id', $intent->id)
                ->update(['status' => 'expired', 'updated_at' => now()]);
            $status = 'expired';
        }

        return response()->json(['status' => $status])->header('Cache-Control', 'no-store');
    }
}

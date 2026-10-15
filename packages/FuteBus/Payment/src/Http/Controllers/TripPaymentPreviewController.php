<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\Payment\Http\Requests\PaymentExitRequest;
use FuteBus\Payment\Http\Requests\PaymentPreviewRequest;
use FuteBus\Payment\Models\SePayPaymentIntent as SePayPaymentIntentRecord;
use FuteBus\Payment\Services\PaymentHoldLifecycle;
use FuteBus\Payment\Services\PaymentPreviewBuilder;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayQrService;
use FuteBus\Payment\Services\TicketQrService;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
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

        if ($payments->ready() && ! SePayPaymentIntentRecord::query()->toBase()->where('token', $draft)->exists()) {
            $payments->createIntent($preview);
        }

        return redirect()->route('trip-payment-preview.show', ['draft' => $draft]);
    }

    public function show(Request $request, SePayQrService $sePay, SePayPaymentService $payments, TicketQrService $ticketQr, PaymentHoldLifecycle $holds, string $draft): View|RedirectResponse
    {
        $holds->release($draft);
        $intent = SePayPaymentIntentRecord::query()->toBase()->where('token', $draft)->first();
        if ($intent?->status === 'paid') {
            $preview = json_decode($intent->snapshot, true);
            $bookingIds = array_filter([$intent->booking_id, $intent->secondary_booking_id]);
            $tickets = TicketRecord::query()->toBase()->whereIn('booking_id', $bookingIds)
                ->orderBy('id')->get(['ticket_code', 'trip_id', 'seat_code', 'price'])
                ->map(fn (object $ticket): array => [
                    'code'      => $ticket->ticket_code,
                    'seat'      => $ticket->seat_code,
                    'price'     => $ticket->price,
                    'qr'        => $ticketQr->dataUri($ticket->ticket_code),
                    'trip'      => isset($preview['outbound_trip']) && $ticket->trip_id === $preview['outbound_trip']['id']
                        ? $preview['outbound_trip'] : $preview['trip'],
                ])->sortBy(fn (array $ticket): string => $ticket['trip']['departure_time'])->values();

            return view('Payment::trip-payment-preview', [
                'paymentCompleted'   => true,
                'paymentEnabled'     => false,
                'paymentCanActivate' => false,
                'sePayQrUrl'         => null,
                'expiresAt'          => Carbon::parse($intent->expires_at)->timestamp,
                'bookingCode'        => BookingRecord::query()->toBase()->where('id', $intent->booking_id)->value('booking_code'),
                'preview'            => $preview,
                'total'              => $intent->amount,
                'tickets'            => $tickets,
            ]);
        }
        if ($intent?->status === 'needs_review') {
            return view('Payment::trip-payment-review', ['paymentCode' => $intent->code]);
        }

        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return redirect()->to(route('home').'#trip-search');
        }

        if ($intent?->status === 'expired'
            || ($intent === null && ($preview['created_at'] ?? 0) + 600 <= now()->timestamp)) {
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
            'expiresAt'          => $intent !== null ? Carbon::parse($intent->expires_at)->timestamp : $preview['created_at'] + 600,
        ]);
    }

    public function cancel(PaymentExitRequest $request, PaymentHoldLifecycle $holds, string $draft): RedirectResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return redirect()->to(route('home').'#trip-search');
        }

        $paymentStatus = $holds->release($draft, confirmedExit: true);

        if (in_array($paymentStatus, ['paid', 'needs_review'], true)) {
            return redirect()->route('trip-payment-preview.show', ['draft' => $draft]);
        }

        $request->session()->forget('trip_payment_preview');

        return redirect()->route('trip-booking.show', array_merge(
            ['trip' => $preview['trip']['id']],
            $preview['criteria'],
            [
                'direction'      => $preview['direction'],
                'seats'          => implode(',', $preview['seat_ids']),
                'outbound_trip'  => $preview['outbound_trip']['id'] ?? null,
                'outbound_seats' => isset($preview['outbound_seat_ids']) ? implode(',', $preview['outbound_seat_ids']) : null,
            ]
        ));
    }

    public function status(Request $request, PaymentHoldLifecycle $holds, string $draft): JsonResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return response()->json(['status' => 'expired'], 200)->header('Cache-Control', 'no-store');
        }

        $status = $holds->release($draft) ?? (($preview['created_at'] ?? 0) + 600 <= now()->timestamp ? 'expired' : 'preview');

        return response()->json(['status' => $status])->header('Cache-Control', 'no-store');
    }
}

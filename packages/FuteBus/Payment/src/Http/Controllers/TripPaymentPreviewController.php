<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use FuteBus\Core\Services\BookingLocationCatalog;
use FuteBus\Core\Services\TripSearchService;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\Payment\Services\SePayQrService;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripPaymentPreviewController extends Controller
{
    public function store(
        TripSearchRequest $request,
        TripSearchService $search,
        BookingLocationCatalog $locations,
        SePayPaymentService $payments,
        int $trip
    ): RedirectResponse {
        $criteria = $request->validated();
        $booking = $request->validate([
            'name'            => ['required', 'string', 'max:120'],
            'phone'           => ['required', 'regex:/^(?:0[35789]\\d{8}|\\+84[35789]\\d{8})$/'],
            'email'           => ['required', 'email', 'max:255'],
            'accept_terms'    => ['accepted'],
            'seats'           => ['required', 'array', 'min:1', 'max:5'],
            'seats.*'         => ['required', 'integer', 'distinct'],
            'pickup_mode'     => ['required', Rule::in(['station', 'transfer'])],
            'dropoff_mode'    => ['required', Rule::in(['station', 'transfer'])],
            'pickup_address'  => ['required_if:pickup_mode,transfer', 'nullable', 'string', 'max:255'],
            'dropoff_address' => ['required_if:dropoff_mode,transfer', 'nullable', 'string', 'max:255'],
        ]);

        $isReturn = $criteria['trip_type'] === 'round_trip' && $request->query('direction') === 'return';
        $from = $isReturn ? $criteria['destination'] : $criteria['departure'];
        $to = $isReturn ? $criteria['departure'] : $criteria['destination'];
        $date = $isReturn ? $criteria['return_date'] : $criteria['departure_date'];
        $selectedTrip = collect($search->search($from, $to, $date, (int) $criteria['quantity']))
            ->firstWhere('id', $trip);
        abort_if($selectedTrip === null, 404);

        $seatIds = array_map('intval', $booking['seats']);
        $selectedSeats = collect($selectedTrip['seats'])
            ->filter(fn (array $seat): bool => ! $seat['sold'] && in_array($seat['id'], $seatIds, true))
            ->values();
        if ($selectedSeats->count() !== count($seatIds)) {
            throw ValidationException::withMessages([
                'seats' => __('core::booking.seats_unavailable'),
            ]);
        }

        $catalog = $locations->all();
        $pickupIsTransfer = $booking['pickup_mode'] === 'transfer';
        $dropoffIsTransfer = $booking['dropoff_mode'] === 'transfer';
        $pickupName = $pickupIsTransfer ? __('core::booking.transfer') : $selectedTrip['origin'];
        $dropoffName = $dropoffIsTransfer ? __('core::booking.transfer') : $selectedTrip['destination'];
        $pickupAddress = $pickupIsTransfer
            ? $booking['pickup_address']
            : $this->addressFor($selectedTrip['origin'], $catalog);
        $dropoffAddress = $dropoffIsTransfer
            ? $booking['dropoff_address']
            : $this->addressFor($selectedTrip['destination'], $catalog);

        $token = (string) Str::uuid();
        $preview = [
            'user_id'    => $request->user()?->id,
            'token'      => $token,
            'created_at' => now()->timestamp,
            'trip'       => [
                'id'             => $selectedTrip['id'],
                'origin'         => $selectedTrip['origin'],
                'destination'    => $selectedTrip['destination'],
                'departure_time' => $selectedTrip['departure_time'],
                'fare'           => $selectedTrip['price'],
            ],
            'criteria'  => $criteria,
            'direction' => $isReturn ? 'return' : 'outbound',
            'customer'  => [
                'name'  => $booking['name'],
                'phone' => $booking['phone'],
                'email' => $booking['email'],
            ],
            'seats'    => $selectedSeats->pluck('code')->all(),
            'seat_ids' => $seatIds,
            'pickup'   => [
                'name'         => $pickupName,
                'address'      => $pickupAddress,
                'arrival_time' => Carbon::parse($selectedTrip['departure_time'])->subMinutes(15)->toDateTimeString(),
            ],
            'dropoff' => [
                'name'    => $dropoffName,
                'address' => $dropoffAddress,
            ],
        ];

        if ($payments->ready()) {
            $payments->createIntent($preview);
        }
        $request->session()->put('trip_payment_preview', $preview);

        return redirect()->route('trip-payment-preview.show', ['draft' => $token]);
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

    private function addressFor(string $station, array $catalog): ?string
    {
        $normalize = static fn (string $name): string => trim((string) preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            (string) preg_replace(
                '/^(?:(?:van phong|ben xe|bx|lien tinh)\s+)*/i',
                '',
                strtolower(Str::ascii($name))
            )
        ));
        $stationName = $normalize($station);
        $bestAddress = null;
        $bestScore = 0;

        foreach (['departure', 'destination'] as $side) {
            $offices = collect($catalog[$side]['areas'] ?? [])
                ->flatMap(fn (array $area): array => $area['offices'] ?? [])
                ->concat($catalog[$side]['directory_offices'] ?? []);
            foreach ($offices as $office) {
                $candidate = $normalize((string) ($office['name'] ?? ''));
                $address = trim((string) ($office['address'] ?? ''));
                if ($candidate === '' || $address === '') {
                    continue;
                }
                $score = $candidate === $stationName
                    ? 2
                    : (str_ends_with($candidate, ' '.$stationName) ? 1 : 0);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestAddress = $address;
                }
            }
        }

        return $bestAddress;
    }
}

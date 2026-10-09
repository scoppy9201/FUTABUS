<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use FuteBus\Core\Services\BookingLocationCatalog;
use FuteBus\Core\Services\SePayQrService;
use FuteBus\Core\Services\TripSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
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
        $request->session()->put('trip_payment_preview', [
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
        ]);

        return redirect()->route('trip-payment-preview.show', ['draft' => $token]);
    }

    public function show(Request $request, SePayQrService $sePay, string $draft): View|RedirectResponse
    {
        $preview = $request->session()->get('trip_payment_preview');
        if (! is_array($preview) || ($preview['token'] ?? null) !== $draft) {
            return redirect()->to(route('home').'#trip-search');
        }

        if (($preview['created_at'] ?? 0) + 600 <= now()->timestamp) {
            $request->session()->forget('trip_payment_preview');

            return redirect()->to(route('home').'#trip-search');
        }

        $qrUrl = $preview['sepay_qr_url'] ?? null;
        if ($qrUrl === null && $sePay->configured()) {
            $reference = 'FUTA'.substr(str_replace('-', '', $draft), 0, 16);
            $amount = count($preview['seats']) * $preview['trip']['fare'];
            $qrUrl = $sePay->generate($amount, $reference);
            if ($qrUrl !== null) {
                $request->session()->put('trip_payment_preview.sepay_qr_url', $qrUrl);
            }
        }

        return view('core::trip-payment-preview', [
            'preview'       => $preview,
            'sePayQrUrl'    => $qrUrl,
        ]);
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

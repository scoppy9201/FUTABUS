<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\BookingManagement\Services\BookingTripStops;
use FuteBus\TripSearch\Services\BookingLocationCatalog;
use FuteBus\TripSearch\Services\TripSearchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentPreviewBuilder
{
    public function __construct(
        private readonly TripSearchService $search,
        private readonly BookingLocationCatalog $locations,
        private readonly BookingTripStops $stops,
    ) {}

    public function build(array $data, int $trip, ?int $userId): array
    {
        $isReturn = $data['trip_type'] === 'round_trip' && ($data['direction'] ?? 'outbound') === 'return';
        $from = $isReturn ? $data['destination'] : $data['departure'];
        $to = $isReturn ? $data['departure'] : $data['destination'];
        $date = $isReturn ? $data['return_date'] : $data['departure_date'];
        $selected = collect($this->search->search($from, $to, $date, (int) $data['quantity']))
            ->firstWhere('id', $trip);
        abort_if($selected === null, 404);

        $seatIds = array_map('intval', $data['seats']);
        $selectedSeats = collect($selected['seats'])
            ->filter(fn (array $seat): bool => ! $seat['sold'] && in_array($seat['id'], $seatIds, true))
            ->values();
        if ($selectedSeats->count() !== count($seatIds)) {
            throw ValidationException::withMessages(['seats' => __('core::booking.seats_unavailable')]);
        }

        $outboundTrip = null;
        $outboundSeatIds = [];
        $outboundSeats = collect();
        if ($data['trip_type'] === 'round_trip') {
            if (! $isReturn) {
                throw ValidationException::withMessages(['direction' => __('core::booking.invalid_round_trip')]);
            }
            $outboundTrip = collect($this->search->search(
                $data['departure'], $data['destination'], $data['departure_date'], (int) $data['quantity'],
            ))->firstWhere('id', (int) $data['outbound_trip']);
            abort_if($outboundTrip === null, 404);
            if (Carbon::parse($outboundTrip['arrival_time'])->greaterThanOrEqualTo(Carbon::parse($selected['departure_time']))) {
                throw ValidationException::withMessages(['outbound_trip' => __('core::booking.invalid_round_trip')]);
            }
            $outboundSeatIds = array_map('intval', $data['outbound_seats']);
            $outboundSeats = collect($outboundTrip['seats'])
                ->filter(fn (array $seat): bool => ! $seat['sold'] && in_array($seat['id'], $outboundSeatIds, true))
                ->values();
            if ($outboundSeats->count() !== count($outboundSeatIds)) {
                throw ValidationException::withMessages(['outbound_seats' => __('core::booking.seats_unavailable')]);
            }
        }

        $catalog = $this->locations->all();
        $returnStops = $this->buildStops($selected, $data, $catalog);

        $preview = [
            'user_id'    => $userId,
            'token'      => (string) Str::uuid(),
            'created_at' => now()->timestamp,
            'trip'       => [
                'id'             => $selected['id'],
                'origin'         => $selected['origin'],
                'destination'    => $selected['destination'],
                'departure_time' => $selected['departure_time'],
                'fare'           => $selected['price'],
            ],
            'criteria' => array_intersect_key($data, array_flip([
                'trip_type', 'departure', 'destination', 'departure_date', 'return_date', 'quantity',
            ])),
            'direction' => $isReturn ? 'return' : 'outbound',
            'customer'  => [
                'name'  => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
            ],
            'seats'    => $selectedSeats->pluck('code')->all(),
            'seat_ids' => $seatIds,
            'pickup'   => $returnStops['pickup'],
            'dropoff'  => $returnStops['dropoff'],
        ];

        if ($outboundTrip !== null) {
            $preview['outbound_trip'] = [
                'id'             => $outboundTrip['id'],
                'origin'         => $outboundTrip['origin'],
                'destination'    => $outboundTrip['destination'],
                'departure_time' => $outboundTrip['departure_time'],
                'fare'           => $outboundTrip['price'],
            ];
            $preview['outbound_seats'] = $outboundSeats->pluck('code')->all();
            $preview['outbound_seat_ids'] = $outboundSeatIds;
            $outboundStops = $this->buildStops($outboundTrip, $data, $catalog, 'outbound_');
            $preview['outbound_pickup'] = $outboundStops['pickup'];
            $preview['outbound_dropoff'] = $outboundStops['dropoff'];
        }

        return $preview;
    }

    private function buildStops(array $trip, array $data, array $catalog, string $prefix = ''): array
    {
        $pickupIsTransfer = $data[$prefix.'pickup_mode'] === 'transfer';
        $dropoffIsTransfer = $data[$prefix.'dropoff_mode'] === 'transfer';
        $pickup = $pickupIsTransfer ? null : $this->stops->selected(
            $trip, 'pickup', $data[$prefix.'pickup_station'] ?? null, $prefix.'pickup_station',
        );
        $dropoff = $dropoffIsTransfer ? null : $this->stops->selected(
            $trip, 'dropoff', $data[$prefix.'dropoff_station'] ?? null, $prefix.'dropoff_station',
        );

        return [
            'pickup' => [
                'name'    => $pickupIsTransfer ? __('core::booking.transfer') : $pickup['name'],
                'address' => $pickupIsTransfer
                    ? $data[$prefix.'pickup_address']
                    : ($pickup['address'] ?: $this->addressFor($pickup['name'], $catalog)),
                'arrival_time' => $pickupIsTransfer
                    ? Carbon::parse($trip['departure_time'])->subMinutes(15)->toDateTimeString()
                    : $pickup['boarding_time'],
            ],
            'dropoff' => [
                'name'    => $dropoffIsTransfer ? __('core::booking.transfer') : $dropoff['name'],
                'address' => $dropoffIsTransfer
                    ? $data[$prefix.'dropoff_address']
                    : ($dropoff['address'] ?: $this->addressFor($dropoff['name'], $catalog)),
            ],
        ];
    }

    private function addressFor(string $station, array $catalog): ?string
    {
        $normalize = static fn (string $name): string => trim((string) preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            (string) preg_replace(
                '/^(?:(?:van phong|ben xe|bx|lien tinh)\s+)*/i',
                '',
                strtolower(Str::ascii($name)),
            ),
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

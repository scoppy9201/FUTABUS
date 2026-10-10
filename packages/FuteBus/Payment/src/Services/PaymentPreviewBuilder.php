<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Core\Services\BookingLocationCatalog;
use FuteBus\Core\Services\TripSearchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentPreviewBuilder
{
    public function __construct(
        private readonly TripSearchService $search,
        private readonly BookingLocationCatalog $locations,
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

        $catalog = $this->locations->all();
        $pickupIsTransfer = $data['pickup_mode'] === 'transfer';
        $dropoffIsTransfer = $data['dropoff_mode'] === 'transfer';

        return [
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
            'pickup'   => [
                'name'    => $pickupIsTransfer ? __('core::booking.transfer') : $selected['origin'],
                'address' => $pickupIsTransfer
                    ? $data['pickup_address']
                    : $this->addressFor($selected['origin'], $catalog),
                'arrival_time' => Carbon::parse($selected['departure_time'])->subMinutes(15)->toDateTimeString(),
            ],
            'dropoff' => [
                'name'    => $dropoffIsTransfer ? __('core::booking.transfer') : $selected['destination'],
                'address' => $dropoffIsTransfer
                    ? $data['dropoff_address']
                    : $this->addressFor($selected['destination'], $catalog),
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

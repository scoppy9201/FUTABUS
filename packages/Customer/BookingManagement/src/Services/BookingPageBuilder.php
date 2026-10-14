<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Services;

use FuteBus\TripSearch\Services\TripSearchService;

class BookingPageBuilder
{
    public function __construct(private readonly TripSearchService $search, private readonly BookingTripStops $stops) {}

    public function build(array $criteria, array $selection, int $trip): array
    {
        $isReturn = $criteria['trip_type'] === 'round_trip' && ($selection['direction'] ?? null) === 'return';
        abort_if($criteria['trip_type'] === 'round_trip' && ! $isReturn, 404);
        $from = $isReturn ? $criteria['destination'] : $criteria['departure'];
        $to = $isReturn ? $criteria['departure'] : $criteria['destination'];
        $date = $isReturn ? $criteria['return_date'] : $criteria['departure_date'];

        $selectedTrip = collect($this->search->search($from, $to, $date, (int) $criteria['quantity']))
            ->firstWhere('id', $trip);
        abort_if($selectedTrip === null, 404);

        $requestedSeats = collect(explode(',', (string) ($selection['seats'] ?? '')))
            ->filter(fn ($id) => ctype_digit($id))
            ->map(fn ($id) => (int) $id)
            ->all();
        $selectedSeatIds = collect($selectedTrip['seats'])
            ->filter(fn ($seat) => ! $seat['sold'] && in_array($seat['id'], $requestedSeats, true))
            ->take(5)
            ->pluck('id')
            ->all();

        $outboundTrip = null;
        $outboundSeatIds = [];
        if ($isReturn) {
            $outboundId = filter_var(($selection['outbound_trip'] ?? null), FILTER_VALIDATE_INT);
            abort_if($outboundId === false || $outboundId === null, 404);
            $outboundTrip = collect($this->search->search(
                $criteria['departure'], $criteria['destination'], $criteria['departure_date'], (int) $criteria['quantity'],
            ))->firstWhere('id', $outboundId);
            abort_if($outboundTrip === null, 404);

            $requestedOutboundSeats = collect(explode(',', (string) ($selection['outbound_seats'] ?? '')))
                ->filter(fn ($id) => ctype_digit($id))
                ->map(fn ($id) => (int) $id)
                ->all();
            $outboundSeatIds = collect($outboundTrip['seats'])
                ->filter(fn ($seat) => ! $seat['sold'] && in_array($seat['id'], $requestedOutboundSeats, true))
                ->take(5)
                ->pluck('id')
                ->all();
        }

        $stopOptions = [$selectedTrip['id'] => $this->stops->forTrip($selectedTrip)];
        if ($outboundTrip !== null) {
            $stopOptions[$outboundTrip['id']] = $this->stops->forTrip($outboundTrip);
        }

        return [
            'trip'            => $selectedTrip,
            'criteria'        => $criteria,
            'from'            => $from,
            'to'              => $to,
            'selectedSeatIds' => $selectedSeatIds,
            'outboundTrip'    => $outboundTrip,
            'outboundSeatIds' => $outboundSeatIds,
            'stopOptions'     => $stopOptions,
        ];
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\TripSearch\Http\Controllers\Api;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use FuteBus\Core\Services\TripSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class TripController extends Controller
{
    public function __invoke(TripSearchRequest $request, TripSearchService $search): JsonResponse
    {
        $criteria = $request->validated();
        $outbound = $search->search(
            $criteria['departure'],
            $criteria['destination'],
            $criteria['departure_date'],
            (int) $criteria['quantity'],
        );
        $return = $criteria['trip_type'] === 'round_trip'
            ? $search->search(
                $criteria['destination'],
                $criteria['departure'],
                $criteria['return_date'],
                (int) $criteria['quantity'],
            )
            : [];

        return response()->json([
            'data' => [
                'outbound' => array_map($this->summarize(...), $outbound),
                'return'   => array_map($this->summarize(...), $return),
            ],
        ]);
    }

    public function show(TripSearchRequest $request, TripSearchService $search, int $trip): JsonResponse
    {
        $criteria = $request->validated();
        $direction = $request->validate(['direction' => ['required', 'in:outbound,return']])['direction'];
        if ($direction === 'return' && $criteria['trip_type'] !== 'round_trip') {
            throw ValidationException::withMessages([
                'direction' => __('validation.in', ['attribute' => 'direction']),
            ]);
        }

        $from = $direction === 'return' ? $criteria['destination'] : $criteria['departure'];
        $to = $direction === 'return' ? $criteria['departure'] : $criteria['destination'];
        $date = $direction === 'return' ? $criteria['return_date'] : $criteria['departure_date'];
        $selected = collect($search->search($from, $to, $date, (int) $criteria['quantity']))
            ->firstWhere('id', $trip);
        abort_if($selected === null, 404);

        return response()->json(['data' => [
            ...$this->summarize($selected),
            'seats' => array_map(static fn (array $seat): array => [
                'id'        => $seat['id'],
                'code'      => $seat['code'],
                'available' => ! $seat['sold'],
            ], $selected['seats']),
        ]]);
    }

    private function summarize(array $trip): array
    {
        return [
            'id'              => $trip['id'],
            'departure_time'  => $trip['departure_time'],
            'arrival_time'    => $trip['arrival_time'],
            'origin'          => $trip['origin'],
            'destination'     => $trip['destination'],
            'vehicle_type'    => $trip['vehicle_type'],
            'available_seats' => $trip['available_seats'],
            'price'           => $trip['price'],
        ];
    }
}

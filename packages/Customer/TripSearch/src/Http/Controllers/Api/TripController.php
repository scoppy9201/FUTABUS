<?php

declare(strict_types=1);

namespace FuteBus\TripSearch\Http\Controllers\Api;

use FuteBus\TripSearch\Http\Requests\TripDetailRequest;
use FuteBus\TripSearch\Http\Requests\TripSearchRequest;
use FuteBus\TripSearch\Services\TripSearchService;
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

    public function show(TripDetailRequest $request, TripSearchService $search, int $trip): JsonResponse
    {
        $criteria = $request->validated();
        $direction = $criteria['direction'];
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
            'stops'           => $selected['stops'],
            'seat_columns'    => $selected['seat_columns'],
            'vehicle_details' => $selected['vehicle_details'],
            'seats'           => array_map(static fn (array $seat): array => [
                'id'        => $seat['id'],
                'code'      => $seat['code'],
                'row'       => $seat['row'],
                'column'    => $seat['column'],
                'deck'      => $seat['deck'],
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

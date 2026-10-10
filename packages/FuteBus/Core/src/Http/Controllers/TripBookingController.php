<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use FuteBus\Core\Services\TripSearchService;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class TripBookingController extends Controller
{
    public function __invoke(TripSearchRequest $request, TripSearchService $search, int $trip): View
    {
        $criteria = $request->validated();
        $isReturn = $criteria['trip_type'] === 'round_trip' && $request->query('direction') === 'return';
        $from = $isReturn ? $criteria['destination'] : $criteria['departure'];
        $to = $isReturn ? $criteria['departure'] : $criteria['destination'];
        $date = $isReturn ? $criteria['return_date'] : $criteria['departure_date'];

        $selectedTrip = collect($search->search($from, $to, $date, (int) $criteria['quantity']))
            ->firstWhere('id', $trip);
        abort_if($selectedTrip === null, 404);

        $requestedSeats = collect(explode(',', (string) $request->query('seats', '')))
            ->filter(fn ($id) => ctype_digit($id))
            ->map(fn ($id) => (int) $id)
            ->all();
        $selectedSeatIds = collect($selectedTrip['seats'])
            ->filter(fn ($seat) => ! $seat['sold'] && in_array($seat['id'], $requestedSeats, true))
            ->take(5)
            ->pluck('id')
            ->all();

        return view('BookingManagement::trip-booking', [
            'trip'            => $selectedTrip,
            'criteria'        => $criteria,
            'from'            => $from,
            'to'              => $to,
            'selectedSeatIds' => $selectedSeatIds,
        ]);
    }
}

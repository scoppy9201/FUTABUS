<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Http\Requests\TripSearchRequest;
use FuteBus\Core\Services\BookingLocationCatalog;
use FuteBus\Core\Services\TripSearchService;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class TripSearchController extends Controller
{
    public function __invoke(
        TripSearchRequest $request,
        TripSearchService $search,
        BookingLocationCatalog $locations,
    ): View {
        $criteria = $request->validated();
        $outboundTrips = $search->search(
            $criteria['departure'], $criteria['destination'], $criteria['departure_date'], (int) $criteria['quantity'],
        );
        $returnTrips = $criteria['trip_type'] === 'round_trip'
            ? $search->search($criteria['destination'], $criteria['departure'], $criteria['return_date'], (int) $criteria['quantity'])
            : [];

        return view('TripSearch::trip-search', [
            'searchCriteria'   => $criteria,
            'bookingLocations' => $locations->all(),
            'outboundTrips'    => $outboundTrips,
            'returnTrips'      => $returnTrips,
        ]);
    }
}

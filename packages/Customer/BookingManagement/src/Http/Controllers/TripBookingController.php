<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Http\Controllers;

use FuteBus\BookingManagement\Services\BookingPageBuilder;
use FuteBus\TripSearch\Http\Requests\TripSearchRequest;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class TripBookingController extends Controller
{
    public function __invoke(TripSearchRequest $request, BookingPageBuilder $builder, int $trip): View
    {
        return view('BookingManagement::trip-booking', $builder->build(
            $request->validated(), $request->only(['direction', 'seats', 'outbound_trip', 'outbound_seats']), $trip,
        ));
    }
}

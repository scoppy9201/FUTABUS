@extends('core::layouts.home')

@push('styles')
    @vite('packages/Customer/TripSearch/src/resources/css/app.css')
@endpush

@section('title', __('core::trip-search.title'))

@section('content')
    <div class="home-page home-landing trip-search-page min-h-screen bg-[#f4f4f6]">
        @include('core::partials.home.navbar')
        @include('core::partials.home.hero')

        <main class="mx-auto w-full max-w-282 px-3 pb-16 sm:px-4">
            @include('TripSearch::partials.trip-results', [
                'trips' => $outboundTrips,
                'returnTrips' => $returnTrips,
                'from' => $searchCriteria['departure'],
                'to' => $searchCriteria['destination'],
                'roundTrip' => $searchCriteria['trip_type'] === 'round_trip',
                'departureDate' => $searchCriteria['departure_date'],
                'returnDate' => $searchCriteria['return_date'] ?? null,
            ])
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

@extends('core::layouts.home')

@section('title', __('core::trip-search.title'))

@section('content')
    <div class="home-page home-landing trip-search-page min-h-screen bg-[#f4f4f6]">
        @include('core::partials.home.navbar')
        @include('core::partials.home.hero')

        <main class="mx-auto w-full max-w-282 px-3 pb-16 sm:px-4">
            @include('core::partials.home.trip-results', [
                'trips' => $outboundTrips,
                'from' => $searchCriteria['departure'],
                'to' => $searchCriteria['destination'],
                'direction' => 'outbound',
            ])

            @if ($searchCriteria['trip_type'] === 'round_trip')
                <div class="mt-10">
                    @include('core::partials.home.trip-results', [
                        'trips' => $returnTrips,
                        'from' => $searchCriteria['destination'],
                        'to' => $searchCriteria['departure'],
                        'direction' => 'return',
                    ])
                </div>
            @endif
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

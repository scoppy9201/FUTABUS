<div id="booking-{{ $vehicleLeg }}-vehicle-info" class="booking-page__vehicle-popover"
    x-show="vehicleInfoOpen === '{{ $vehicleLeg }}'" x-transition.opacity x-cloak
    @click.outside="vehicleInfoOpen = null" @keydown.escape.window="vehicleInfoOpen = null"
    role="dialog" aria-label="{{ __('core::booking.vehicle_info') }}">
    <div class="booking-page__vehicle-tabs" role="tablist" aria-label="{{ __('core::booking.vehicle_info') }}">
        @foreach (['schedule', 'details', 'amenities', 'policy'] as $tab)
            <button type="button" role="tab"
                @click="vehicleInfoTab = '{{ $tab }}'"
                :aria-selected="vehicleInfoTab === '{{ $tab }}'"
                :class="vehicleInfoTab === '{{ $tab }}' ? 'is-active' : ''">
                {{ __('core::booking.vehicle_tab_'.$tab) }}
            </button>
        @endforeach
    </div>
    <div class="booking-page__vehicle-content">
        <div x-show="vehicleInfoTab === 'schedule'" class="h-full"
            x-data="{ trip: @js(['stops' => ($vehicleLeg === 'outbound' ? $outboundTrip : $trip)['stops']]) }">
            @include('TripSearch::partials.trip-itinerary', [
                'hideItineraryNote' => true,
                'itineraryHasStops' => !empty(($vehicleLeg === 'outbound' ? $outboundTrip : $trip)['stops']),
            ])
        </div>
        <div x-show="vehicleInfoTab === 'details'">
            @include('BookingManagement::partials.booking-vehicle-details')
        </div>
        <div x-show="vehicleInfoTab === 'amenities'">
            @include('BookingManagement::partials.booking-vehicle-amenities')
        </div>
        <div x-show="vehicleInfoTab === 'policy'" class="booking-page__vehicle-policy">
            @foreach ([
                ['cancellation_policy', 'cancellation_items'],
                ['boarding_requirements', 'boarding_items'],
                ['carry_on_luggage', 'luggage_items'],
                ['children_and_pregnancy', 'children_and_pregnancy_items'],
                ['roadside_pickup', 'roadside_pickup_items'],
            ] as [$title, $items])
                <section>
                    <h3>{{ __('core::trip-search.'.$title) }}</h3>
                    <ul>
                        @foreach (__('core::trip-search.'.$items) as $item)
                            <li>@include('BookingManagement::partials.booking-linked-text', ['text' => $item])</li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
    <div class="booking-page__vehicle-note" x-show="vehicleInfoTab === 'schedule'">
        <strong>{{ __('core::booking.schedule_note_title') }}</strong>
        <p>{{ __('core::booking.schedule_note') }}</p>
    </div>
</div>

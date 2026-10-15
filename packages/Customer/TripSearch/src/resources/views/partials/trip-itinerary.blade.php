<div class="trip-itinerary">
    <div class="trip-itinerary__scroll" tabindex="0" aria-label="{{ __('core::trip-search.schedule') }}">
        <p class="sr-only">{{ __('core::trip-search.itinerary_note') }}</p>
        <ol class="trip-itinerary__list">
            <template x-for="stop in {{ $itineraryStopsExpression ?? 'trip.stops' }}" :key="stop.id">
                <li class="trip-itinerary__stop" :class="{ 'is-inactive': !stop.active }">
                    <div class="trip-itinerary__time">
                        <time x-text="stop.hour" :title="stop.date"></time>
                        <span x-show="stop.day_offset > 0" x-text="'+' + stop.day_offset"></span>
                    </div>
                    <div class="trip-itinerary__track" aria-hidden="true">
                        <span class="trip-card__origin-mark" x-show="stop.is_origin"></span>
                        <img src="{{ asset('images/trip-destination-pin.png') }}" alt=""
                            x-show="stop.is_destination && !stop.is_origin">
                        <span class="trip-card__origin-mark is-intermediate" x-show="!stop.is_origin && !stop.is_destination"></span>
                    </div>
                    <div class="trip-itinerary__location">
                        <div class="flex flex-wrap items-center gap-2">
                            <span x-text="stop.name"></span>
                            <span class="trip-itinerary__inactive-label" x-show="!stop.active">{{ __('core::trip-search.stop_inactive') }}</span>
                        </div>
                        <span class="trip-itinerary__address" x-show="stop.address" x-text="stop.address"></span>
                        <a :href="stop.map_url" target="_blank" rel="noopener noreferrer" x-show="stop.active"
                            class="inline-flex items-center gap-1 text-xs text-futa-orange hover:underline
                                focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                            {{ __('core::trip-search.view_location') }}
                            <x-heroicon-o-arrow-top-right-on-square class="size-3" aria-hidden="true" />
                        </a>
                        @if($selectableStops ?? false)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach(['pickup', 'dropoff'] as $kind)
                                    <label x-show="stopOption(trip, '{{ $kind }}', stop.id)" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                        :class="selectedTrip?.id === trip.id && String({{ $kind }}) === String(stop.id)
                                            ? 'border-futa-orange bg-futa-orange-soft text-futa-orange' : 'border-slate-200 bg-white text-slate-600'">
                                        <input type="radio" :name="'exchange-{{ $kind }}-' + trip.id" :value="stop.id"
                                            :checked="selectedTrip?.id === trip.id && String({{ $kind }}) === String(stop.id)"
                                            :disabled="busy || !canSelectStop(trip, '{{ $kind }}', stop.id)"
                                            @change="chooseStop(trip, '{{ $kind }}', stop.id)" class="accent-futa-orange">
                                        {{ __('Payment::exchange.select_'.$kind) }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </li>
            </template>
        </ol>
        @unless($itineraryHasStops ?? false)
            <div x-show="{{ $itineraryStopsExpression ?? 'trip.stops' }}.length === 0">
                <x-core::empty-state :title="__('core::booking.schedule_empty')" compact />
            </div>
        @endunless
    </div>
    @unless($hideItineraryNote ?? false)
        <div class="trip-itinerary__note">
            <strong>{{ __('core::booking.schedule_note_title') }}</strong>
            <p>{{ __('core::booking.schedule_note') }}</p>
        </div>
    @endunless
</div>

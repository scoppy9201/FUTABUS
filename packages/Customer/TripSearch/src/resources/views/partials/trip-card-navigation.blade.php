<div class="trip-card__footer">
    <div class="trip-card__links">
        @foreach(['seats' => 'choose_seat', 'schedule' => 'schedule', 'transfer' => 'transfer', 'policy' => 'policy'] as $panel => $label)
            <button type="button" @click="togglePanel(trip.id, '{{ $panel }}')"
                :disabled="{{ ($exchangeMode ?? false) ? 'busy' : 'false' }}"
                :aria-expanded="openTripId === trip.id && openPanel === '{{ $panel }}'"
                :class="{ 'is-active': openTripId === trip.id && openPanel === '{{ $panel }}' }">
                {{ __('core::trip-search.'.$label) }}
            </button>
        @endforeach
    </div>
    <button type="button"
        @click="selectTrip(trip)"
        @if($exchangeMode ?? false)
            :disabled="busy || trip.available < 1"
            :class="selectedTrip?.id === trip.id ? 'is-selected' : ''"
        @else
            :class="openTripId === trip.id && !roundTrip ? 'is-selected' : ''"
        @endif
        class="trip-card__select">
        @if($exchangeMode ?? false)
            <span x-text="selectedTrip?.id === trip.id ? @js(__('Payment::exchange.trip_selected')) : @js(__('core::trip-search.select_trip'))"></span>
        @else
            {{ __('core::trip-search.select_trip') }}
        @endif
    </button>
</div>

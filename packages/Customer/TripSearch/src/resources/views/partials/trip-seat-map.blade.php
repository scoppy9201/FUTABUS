<div class="trip-card__seat-legend" aria-label="{{ __('core::trip-search.seat_status') }}">
    <span><i class="trip-card__seat-key is-sold"></i>{{ __('core::trip-search.seat_sold') }}</span>
    <span><i class="trip-card__seat-key is-free"></i>{{ __('core::trip-search.seat_free') }}</span>
    <span><i class="trip-card__seat-key is-chosen"></i>{{ __('core::trip-search.seat_choosing') }}</span>
</div>
<div class="trip-card__decks">
    <template x-for="deck in trip.seat_decks" :key="deck">
        <div class="trip-card__deck">
            <h3 x-text="deck === 'lower' ? @js(__('core::trip-search.deck_lower')) : @js(__('core::trip-search.deck_upper'))"></h3>
            <div class="trip-card__seat-grid" :style="{ '--seat-columns': trip.seat_columns }">
                <template x-for="seat in deckSeats(trip, deck)" :key="seat.id">
                    <button type="button"
                        class="trip-card__seat"
                        :style="{ gridColumn: seat.column, gridRow: seat.row }"
                        :class="seat.sold ? 'is-sold' : (seatSelected(trip, seat) ? 'is-chosen' : 'is-free')"
                        :disabled="{{ ($exchangeMode ?? false) ? 'seat.sold || busy || trip.available < 1' : 'seat.sold' }}"
                        :aria-pressed="seatSelected(trip, seat)"
                        :aria-label="seat.code + ', ' + (seat.sold ? @js(__('core::trip-search.seat_sold')) : (seatSelected(trip, seat) ? @js(__('core::trip-search.seat_choosing')) : @js(__('core::trip-search.seat_free'))))"
                        @click="{{ ($exchangeMode ?? false) ? 'chooseSeat(trip, seat)' : 'toggleSeat(trip, seat)' }}">
                        <span class="trip-card__seat-code" x-text="seat.code"></span>
                        <span class="trip-card__seat-foot" aria-hidden="true"></span>
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>
<x-core::empty-state class="trip-card__seat-empty" x-show="trip.seats.length === 0" :title="__('core::trip-search.no_seat_map')" compact />

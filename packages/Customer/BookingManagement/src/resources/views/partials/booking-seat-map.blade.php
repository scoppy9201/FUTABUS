<div class="booking-page__seat-area">
    <div class="booking-page__seat-map">
        <template x-for="deck in @js($seatTrip['seat_decks'])" :key="deck">
            <div class="booking-page__deck">
                <h3 x-text="deck === 'lower' ? @js(__('core::booking.lower_deck')) : @js(__('core::booking.upper_deck'))"></h3>
                <div class="trip-card__seat-grid">
                    <template x-for="seat in deckSeats(deck, @js($leg))" :key="seat.id">
                        <button type="button" class="trip-card__seat"
                            :style="@js($seatTrip['demo_seat_map']) ? { gridColumn: seat.column, gridRow: seat.row } : {}"
                            :class="seat.sold ? 'is-sold' : (seatSelected(seat, @js($leg)) ? 'is-chosen' : 'is-free')"
                            :disabled="seat.sold"
                            :aria-pressed="seatSelected(seat, @js($leg))"
                            :aria-label="seat.code + ', ' + (seat.sold ? @js(__('core::booking.sold')) : (seatSelected(seat, @js($leg)) ? @js(__('core::booking.selected')) : @js(__('core::booking.available'))))"
                            @click="toggleSeat(seat, @js($leg))">
                            <span class="trip-card__seat-code" x-text="seat.code"></span>
                            <span class="trip-card__seat-foot" aria-hidden="true"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>
        <x-core::empty-state x-show="deckSeats('lower', @js($leg)).length + deckSeats('upper', @js($leg)).length === 0"
            class="text-sm text-slate-500" :title="__('core::trip-search.no_seat_map')" compact />
    </div>
    @unless ($sharedLegend ?? false)
        @include('BookingManagement::partials.booking-seat-legend')
    @endunless
</div>

<section
    aria-label="{{ $from }} - {{ $to }}"
    x-data="{
        trips: @js($trips),
        timeFilters: [],
        vehicleFilters: [],
        rowFilters: [],
        deckFilters: [],
        sortBy: 'departure',
        sortHighlights: { price: true, departure: true, seats: false },
        selectedTrip: null,
        openTripId: null,
        openPanel: null,
        selectedSeatIds: {},
        seatNotice: '',
        seatNoticeTimer: null,
        selectedSeats(trip) {
            const ids = this.selectedSeatIds[trip.id] ?? trip.demo_selected_seat_ids ?? [];
            return trip.seats.filter(seat => ids.includes(seat.id));
        },
        seatSelected(trip, seat) {
            return this.selectedSeats(trip).some(selected => selected.id === seat.id);
        },
        showSeatNotice(message) {
            this.seatNotice = message;
            clearTimeout(this.seatNoticeTimer);
            this.seatNoticeTimer = setTimeout(() => this.seatNotice = '', 3500);
        },
        toggleSeat(trip, seat) {
            if (seat.sold) return;
            const current = this.selectedSeats(trip).map(item => item.id);
            if (!current.includes(seat.id) && current.length >= 5) {
                this.showSeatNotice(@js(__('core::trip-search.seat_limit_notice')));
                return;
            }
            this.selectedSeatIds[trip.id] = current.includes(seat.id)
                ? current.filter(id => id !== seat.id)
                : [...current, seat.id];
        },
        deckSeats(trip, deck) {
            return trip.seats.filter(seat => seat.deck === deck);
        },
        togglePanel(tripId, panel) {
            if (this.openTripId === tripId && this.openPanel === panel) {
                this.openTripId = null;
                this.openPanel = null;
                return;
            }
            this.openTripId = tripId;
            this.openPanel = panel;
            this.$nextTick(() => {
                const detail = this.$el.querySelector('.trip-card.is-expanded .trip-card__detail');
                if (detail) detail.scrollTop = 0;
            });
        },
        toggleSort(sort) {
            this.sortHighlights[sort] = !this.sortHighlights[sort];
            if (this.sortHighlights[sort]) {
                this.sortBy = sort;
            } else if (this.sortBy === sort) {
                this.sortBy = ['departure', 'price', 'seats'].find(key => this.sortHighlights[key]) ?? null;
            }
        },
        toggleFilter(group, value) {
            this[group] = this[group].includes(value)
                ? this[group].filter(item => item !== value)
                : [...this[group], value];
        },
        resetFilters() {
            this.timeFilters = [];
            this.vehicleFilters = [];
            this.rowFilters = [];
            this.deckFilters = [];
        },
        hasOption(field, value) {
            return this.trips.some(trip => trip[field].includes(value));
        },
        timeSlot(hour) {
            const time = Number(hour.slice(0, 2));
            return time < 6 ? 'early' : time < 12 ? 'morning' : time < 18 ? 'afternoon' : 'evening';
        },
        vehicleGroup(type) {
            return type === 'standard' || type === 'minivan' ? 'seat' : type;
        },
        visibleTrips() {
            const filtered = this.trips.filter(trip =>
                (!this.timeFilters.length || this.timeFilters.includes(this.timeSlot(trip.departure_hour)))
                && (!this.vehicleFilters.length || this.vehicleFilters.includes(this.vehicleGroup(trip.vehicle_type)))
                && (!this.rowFilters.length || this.rowFilters.some(row => trip.row_options.includes(row)))
                && (!this.deckFilters.length || this.deckFilters.some(deck => trip.deck_options.includes(deck)))
            );
            if (!this.sortBy) return filtered;
            return filtered.sort((a, b) => {
                if (this.sortBy === 'price') return a.price - b.price || a.departure_time.localeCompare(b.departure_time);
                if (this.sortBy === 'seats') return b.available_seats - a.available_seats || a.departure_time.localeCompare(b.departure_time);
                return a.departure_time.localeCompare(b.departure_time);
            });
        },
        duration(minutes) {
            const hours = Math.floor(minutes / 60);
            const remaining = Math.round(minutes % 60);
            return hours + ' {{ __('core::trip-search.hours') }}' + (remaining ? ' ' + remaining + ' {{ __('core::trip-search.minutes') }}' : '');
        },
        vehicleLabel(type) {
            return type === 'limousine' ? 'Limousine'
                : type === 'sleeper' ? @js(__('core::trip-search.sleeper'))
                : @js(__('core::trip-search.seat'));
        },
        money(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
        },
        clockDuration(minutes) {
            return String(Math.floor(minutes / 60)).padStart(2, '0') + ':'
                + String(Math.round(minutes % 60)).padStart(2, '0') + ' h';
        },
        stationLabel(value) {
            return value.replace(/^Bến xe\s+/i, '');
        },
        activeTrip() {
            return this.openPanel ? this.trips.find(trip => trip.id === this.openTripId) ?? null : null;
        },
        tripDate(value) {
            const date = new Date(value.replace(' ', 'T'));
            const weekdays = @js(app()->getLocale() === 'vi' ? ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'] : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            return weekdays[date.getDay()] + ', '
                + String(date.getDate()).padStart(2, '0') + '/'
                + String(date.getMonth() + 1).padStart(2, '0') + '/'
                + date.getFullYear();
        },
    }"
    class="trip-search-results grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]"
>
    <div class="trip-seat-notice" x-show="seatNotice" x-transition.opacity x-cloak role="alert" x-text="seatNotice"></div>
    <div class="trip-search-results__aside self-start space-y-4">
        <template x-if="activeTrip()">
            <div class="trip-selection-summary">
                <h2>{{ __('core::trip-search.your_trip') }}</h2>
                <div class="trip-selection-summary__content">
                    <div class="trip-selection-summary__route">
                        <span class="trip-selection-summary__count">1</span>
                        <div class="min-w-0">
                            <p x-text="tripDate(activeTrip().departure_time)"></p>
                            <strong x-text="stationLabel(activeTrip().origin) + ' - ' + stationLabel(activeTrip().destination)"></strong>
                        </div>
                    </div>
                    <div class="trip-selection-summary__timeline">
                        <time x-text="activeTrip().departure_hour"></time>
                        <span class="trip-card__origin-mark" aria-hidden="true"></span>
                        <span class="trip-selection-summary__line" aria-hidden="true"></span>
                        <span class="trip-selection-summary__duration" x-text="clockDuration(activeTrip().duration_minutes)"></span>
                        <span class="trip-selection-summary__line" aria-hidden="true"></span>
                        <img src="{{ asset('images/trip-destination-pin.png') }}" alt="" aria-hidden="true">
                        <time x-text="activeTrip().arrival_hour"></time>
                    </div>
                    <p class="trip-selection-summary__seats" x-show="selectedSeats(activeTrip()).length > 0">
                        {{ __('core::trip-search.seats_label') }}: <span x-text="selectedSeats(activeTrip()).map(seat => seat.code).join(', ')"></span>
                    </p>
                </div>
            </div>
        </template>
        <aside class="overflow-hidden rounded-xl bg-white shadow-[0_2px_7px_rgba(15,23,42,.18)]">
        <div class="flex items-center justify-between px-4 py-4">
            <h2 class="text-sm font-bold uppercase text-gray-950">{{ __('core::trip-search.filters') }}</h2>
            <button type="button" @click="resetFilters()" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#ef5222] hover:underline">
                <span>{{ __('core::trip-search.clear_filters') }}</span>
                <x-heroicon-o-trash class="size-5 shrink-0" aria-hidden="true" />
            </button>
        </div>
        <div class="border-b border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.departure_time') }}</h3>
            @foreach (['early', 'morning', 'afternoon', 'evening'] as $slot)
                <label class="mb-2 flex cursor-pointer items-center gap-2 text-sm text-gray-700 last:mb-0" :class="trips.length === 0 ? 'cursor-not-allowed opacity-40' : ''">
                    <input type="checkbox" value="{{ $slot }}"
                        @change="toggleFilter('timeFilters', '{{ $slot }}')"
                        :checked="timeFilters.includes('{{ $slot }}')"
                        :disabled="trips.length === 0"
                        class="trip-filter-time-checkbox size-4 appearance-none rounded-full border border-gray-300 bg-white align-middle transition checked:border-[#ef5222] checked:bg-[#ef5222] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]">
                    <span>{{ __('core::trip-search.time_'.$slot) }}</span>
                </label>
            @endforeach
        </div>
        <div class="px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.vehicle') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['seat', 'sleeper', 'limousine'] as $vehicle)
                    <button type="button"
                        @click="toggleFilter('vehicleFilters', '{{ $vehicle }}')"
                        :disabled="trips.length === 0"
                        :aria-pressed="vehicleFilters.includes('{{ $vehicle }}')"
                        :class="vehicleFilters.includes('{{ $vehicle }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'"
                        class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.'.$vehicle) }}</button>
                @endforeach
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.seat_row') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['front', 'middle', 'back'] as $row)
                    <button type="button"
                        @click="toggleFilter('rowFilters', '{{ $row }}')"
                        :disabled="!hasOption('row_options', '{{ $row }}')"
                        :aria-pressed="rowFilters.includes('{{ $row }}')"
                        :class="rowFilters.includes('{{ $row }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'"
                        class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.row_'.$row) }}</button>
                @endforeach
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5">
            <h3 class="mb-3 text-sm font-semibold">{{ __('core::trip-search.deck') }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach (['upper', 'lower'] as $deck)
                    <button type="button"
                        @click="toggleFilter('deckFilters', '{{ $deck }}')"
                        :disabled="!hasOption('deck_options', '{{ $deck }}')"
                        :aria-pressed="deckFilters.includes('{{ $deck }}')"
                        :class="deckFilters.includes('{{ $deck }}') ? 'border-[#ef5222] bg-[#fff3ed] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-800'"
                        class="rounded-md border px-3 py-2 text-sm transition hover:border-[#ef5222] disabled:cursor-not-allowed disabled:opacity-40">{{ __('core::trip-search.deck_'.$deck) }}</button>
                @endforeach
            </div>
        </div>
        </aside>
    </div>

    <div class="trip-search-results__list min-w-0">
        <h2 class="mb-3 text-xl font-semibold text-gray-950">{{ $from }} - {{ $to }}</h2>
        <div class="mb-6 flex flex-wrap gap-2.5">
            @foreach (['price', 'departure', 'seats'] as $sort)
                <button type="button"
                    @click="toggleSort('{{ $sort }}')"
                    :aria-pressed="sortHighlights['{{ $sort }}']"
                    :class="sortHighlights['{{ $sort }}'] ? 'border-[#ffd8c8] bg-[#fff6f2] text-[#ef5222]' : 'border-gray-200 bg-white text-gray-900'"
                    class="inline-flex items-center gap-2 rounded-md border px-3.5 py-2 text-sm font-medium transition hover:border-[#ef5222]">
                    @if ($sort === 'price')
                        <x-heroicon-o-banknotes class="size-5 shrink-0" aria-hidden="true" />
                    @elseif ($sort === 'departure')
                        <x-heroicon-o-clock class="size-5 shrink-0" aria-hidden="true" />
                    @else
                        <x-heroicon-o-ticket class="size-5 shrink-0" aria-hidden="true" />
                    @endif
                    <span>{{ __('core::trip-search.sort_'.$sort) }}</span>
                </button>
            @endforeach
        </div>

        <div x-show="visibleTrips().length === 0" class="min-h-96 rounded-xl bg-transparent py-16 text-center text-gray-500">
            <p class="text-lg font-semibold">{{ __('core::trip-search.no_results') }}</p>
            <p class="mt-2 text-sm">{{ __('core::trip-search.try_another') }}</p>
        </div>

        <div class="space-y-5" x-show="visibleTrips().length > 0">
            <template x-for="trip in visibleTrips()" :key="trip.id">
                <div class="space-y-5">
                <article class="trip-card" :class="{ 'is-expanded': openTripId === trip.id && openPanel !== null }">
                    <div class="trip-card__body">
                        <div class="trip-card__journey">
                            <div class="trip-card__timeline">
                                <time class="trip-card__time" x-text="trip.departure_hour"></time>
                                <div class="trip-card__track" aria-hidden="true">
                                    <span class="trip-card__origin-mark"></span>
                                    <span class="trip-card__track-line"></span>
                                    <span class="trip-card__duration">
                                        <span x-text="clockDuration(trip.duration_minutes) + (trip.distance_km ? ' - ' + trip.distance_km + 'Km' : '')"></span>
                                        <small>(Asia/Ho_Chi_Minh)</small>
                                    </span>
                                    <img class="trip-card__destination-mark" src="{{ asset('images/trip-destination-pin.png') }}" alt="">
                                </div>
                                <time class="trip-card__time" x-text="trip.arrival_hour"></time>
                            </div>
                            <div class="trip-card__stations">
                                <p x-text="stationLabel(trip.origin)"></p>
                                <p x-text="stationLabel(trip.destination)"></p>
                            </div>
                            <p class="trip-card__notice">{{ __('core::trip-search.trip_notice') }}</p>
                        </div>
                        <div class="trip-card__summary">
                            <p class="trip-card__meta">
                                <span x-text="vehicleLabel(trip.vehicle_type)"></span>
                                <span class="trip-card__seats" x-text="trip.available_seats + ' ' + @js(__('core::trip-search.available_seats'))"></span>
                            </p>
                            <p class="trip-card__price" x-text="money(trip.price)"></p>
                        </div>
                    </div>
                    <div class="trip-card__footer">
                        <div class="trip-card__links">
                            <button type="button" @click="togglePanel(trip.id, 'seats')" :aria-expanded="openTripId === trip.id && openPanel === 'seats'" :class="{ 'is-active': openTripId === trip.id && openPanel === 'seats' }">{{ __('core::trip-search.choose_seat') }}</button>
                            <button type="button" @click="togglePanel(trip.id, 'schedule')" :aria-expanded="openTripId === trip.id && openPanel === 'schedule'" :class="{ 'is-active': openTripId === trip.id && openPanel === 'schedule' }">{{ __('core::trip-search.schedule') }}</button>
                            <button type="button" @click="togglePanel(trip.id, 'transfer')" :aria-expanded="openTripId === trip.id && openPanel === 'transfer'" :class="{ 'is-active': openTripId === trip.id && openPanel === 'transfer' }">{{ __('core::trip-search.transfer') }}</button>
                            <button type="button" @click="togglePanel(trip.id, 'policy')" :aria-expanded="openTripId === trip.id && openPanel === 'policy'" :class="{ 'is-active': openTripId === trip.id && openPanel === 'policy' }">{{ __('core::trip-search.policy') }}</button>
                        </div>
                        <button type="button"
                            @click="selectedTrip = selectedTrip === trip.id ? null : trip.id"
                            :aria-pressed="selectedTrip === trip.id"
                            :class="selectedTrip === trip.id || openTripId === trip.id ? 'is-selected' : ''"
                            class="trip-card__select"
                            x-text="selectedTrip === trip.id ? @js(__('core::trip-search.selected')) : @js(__('core::trip-search.select_trip'))"></button>
                    </div>
                    <div class="trip-card__detail" :class="{ 'is-seats': openPanel === 'seats' }" x-show="openTripId === trip.id && openPanel !== null" x-cloak>
                        <div x-show="openPanel === 'seats'" class="trip-card__seat-panel">
                            <div class="trip-card__seat-legend" aria-label="{{ __('core::trip-search.seat_status') }}">
                                <span><i class="trip-card__seat-key is-sold"></i>{{ __('core::trip-search.seat_sold') }}</span>
                                <span><i class="trip-card__seat-key is-free"></i>{{ __('core::trip-search.seat_free') }}</span>
                                <span><i class="trip-card__seat-key is-chosen"></i>{{ __('core::trip-search.seat_choosing') }}</span>
                            </div>
                            <div class="trip-card__decks">
                                <template x-for="deck in trip.seat_decks" :key="deck">
                                    <div class="trip-card__deck">
                                        <h3 x-text="deck === 'lower' ? @js(__('core::trip-search.deck_lower')) : @js(__('core::trip-search.deck_upper'))"></h3>
                                        <div class="trip-card__seat-grid">
                                            <template x-for="seat in deckSeats(trip, deck)" :key="seat.id">
                                                <button type="button"
                                                    class="trip-card__seat"
                                                    :style="trip.demo_seat_map ? { gridColumn: seat.column, gridRow: seat.row } : {}"
                                                    :class="seat.sold ? 'is-sold' : (seatSelected(trip, seat) ? 'is-chosen' : 'is-free')"
                                                    :disabled="seat.sold"
                                                    :aria-pressed="seatSelected(trip, seat)"
                                                    :aria-label="seat.code + ', ' + (seat.sold ? @js(__('core::trip-search.seat_sold')) : (seatSelected(trip, seat) ? @js(__('core::trip-search.seat_choosing')) : @js(__('core::trip-search.seat_free'))))"
                                                    @click="toggleSeat(trip, seat)">
                                                    <span class="trip-card__seat-code" x-text="seat.code"></span>
                                                    <span class="trip-card__seat-foot" aria-hidden="true"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p class="trip-card__seat-empty" x-show="trip.seats.length === 0">{{ __('core::trip-search.no_seat_map') }}</p>
                            <div class="trip-card__seat-checkout">
                                <div class="trip-card__seat-order">
                                    <span x-text="selectedSeats(trip).length + ' ' + @js(__('core::trip-search.tickets'))"></span>
                                    <strong x-text="selectedSeats(trip).map(seat => seat.code).join(', ') || '—'"></strong>
                                </div>
                                <div class="trip-card__seat-payment">
                                    <span>{{ __('core::trip-search.total_price') }}</span>
                                    <strong x-text="money(selectedSeats(trip).length * trip.price)"></strong>
                                </div>
                                <button type="button" @click="selectedSeats(trip).length ? selectedTrip = trip.id : showSeatNotice(@js(__('core::trip-search.select_seat_notice')))" :disabled="trip.seats.length === 0">{{ __('core::trip-search.confirm_seats') }}</button>
                            </div>
                        </div>
                        <div x-show="openPanel === 'schedule'" class="trip-card__detail-content">
                            <h3>{{ __('core::trip-search.schedule') }}</h3>
                            <p x-text="stationLabel(trip.origin) + ' ' + trip.departure_hour + ' → ' + stationLabel(trip.destination) + ' ' + trip.arrival_hour + ' · ' + clockDuration(trip.duration_minutes)"></p>
                        </div>
                        <div x-show="openPanel === 'transfer'" class="trip-card__detail-content trip-card__transfer-content">
                            <h3>{{ __('core::trip-search.transfer_information') }}</h3>
                            @foreach (__('core::trip-search.transfer_items') as $item)
                                <p class="trip-card__transfer-item">- {{ $item['label'] }} : <em>{{ $item['detail'] }}</em></p>
                            @endforeach
                        </div>
                        <div x-show="openPanel === 'policy'" class="trip-card__detail-content">
                            <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
                            <ul>
                                @foreach (__('core::trip-search.cancellation_items') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <h3>{{ __('core::trip-search.boarding_requirements') }}</h3>
                            <ul>
                                @foreach (__('core::trip-search.boarding_items') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <h3>{{ __('core::trip-search.carry_on_luggage') }}</h3>
                            <ul>
                                @foreach (__('core::trip-search.luggage_items') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <h3>{{ __('core::trip-search.children_and_pregnancy') }}</h3>
                            <ul>
                                @foreach (__('core::trip-search.children_and_pregnancy_items') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <h3>{{ __('core::trip-search.roadside_pickup') }}</h3>
                            <ul>
                                @foreach (__('core::trip-search.roadside_pickup_items') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </article>
                @if ($direction === 'outbound')
                    @guest
                    <aside class="trip-login-prompt" x-show="trip.id === visibleTrips()[0]?.id">
                        <div class="trip-login-prompt__copy">
                            <h3>{{ __('core::trip-search.login_prompt_title') }}</h3>
                            <p>{{ __('core::trip-search.login_prompt_description') }}</p>
                            <a href="{{ route('login') }}">{{ __('core::trip-search.login_prompt_action') }}</a>
                        </div>
                        <img src="{{ asset('images/auth/member-login-benefits.png') }}" alt="" loading="lazy">
                    </aside>
                    @endguest
                @endif
                </div>
            </template>
        </div>
    </div>
</section>

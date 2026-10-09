@extends('core::layouts.home')

@section('title', __('core::booking.page_title'))

@section('content')
    @php
        $origin = $trip['origin'];
        $destination = $trip['destination'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $seatLimit = 5;
    @endphp
    <div class="booking-page" x-data="{
        seats: @js($trip['seats']),
        selectedIds: @js($selectedSeatIds),
        customerName: @js(auth()->user()?->name ?? ''),
        customerPhone: @js(auth()->user()?->phone ?? ''),
        customerEmail: @js(auth()->user()?->email ?? ''),
        pickupAddress: '',
        dropoffAddress: '',
        fare: @js($trip['price']),
        limit: @js($seatLimit),
        pickupMode: 'station',
        dropoffMode: 'station',
        notify(message) {
            window.FutaNotify?.show(message, { tone: 'warning' });
        },
        vehicleInfoOpen: false,
        vehicleInfoTab: 'schedule',
        tripDetailOpen: false,
        policyTooltipOpen: false,
        priceTooltipOpen: false,
        termsModalOpen: false,
        termsScrolling: false,
        termsScrollTimer: null,
        savedPageOverflow: null,
        openTripDetail() {
            if (this.tripDetailOpen) return;
            this.savedPageOverflow = {
                html: document.documentElement.style.overflow,
                body: document.body.style.overflow,
            };
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            this.tripDetailOpen = true;
            this.$nextTick(() => this.$refs.tripDetailClose.focus());
        },
        closeTripDetail() {
            if (!this.tripDetailOpen) return;
            this.tripDetailOpen = false;
            this.policyTooltipOpen = false;
            document.documentElement.style.overflow = this.savedPageOverflow?.html ?? '';
            document.body.style.overflow = this.savedPageOverflow?.body ?? '';
            this.savedPageOverflow = null;
            this.$nextTick(() => this.$refs.tripDetailTrigger.focus());
        },
        showTermsScrollbar() {
            this.termsScrolling = true;
            clearTimeout(this.termsScrollTimer);
            this.termsScrollTimer = setTimeout(() => { this.termsScrolling = false; }, 700);
        },
        openTermsModal() {
            if (this.termsModalOpen) return;
            this.savedPageOverflow = {
                html: document.documentElement.style.overflow,
                body: document.body.style.overflow,
            };
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            this.termsModalOpen = true;
            this.$nextTick(() => this.$refs.termsModalClose.focus());
        },
        closeTermsModal() {
            if (!this.termsModalOpen) return;
            this.termsModalOpen = false;
            clearTimeout(this.termsScrollTimer);
            this.termsScrolling = false;
            document.documentElement.style.overflow = this.savedPageOverflow?.html ?? '';
            document.body.style.overflow = this.savedPageOverflow?.body ?? '';
            this.savedPageOverflow = null;
            this.$nextTick(() => this.$refs.termsModalTrigger.focus());
        },
        selectedSeats() {
            return this.seats.filter(seat => this.selectedIds.includes(seat.id));
        },
        deckSeats(deck) {
            return this.seats.filter(seat => seat.deck === deck);
        },
        toggleSeat(seat) {
            if (seat.sold) return;
            if (this.selectedIds.includes(seat.id)) {
                this.selectedIds = this.selectedIds.filter(id => id !== seat.id);
                return;
            }
            if (this.selectedIds.length >= this.limit) {
                this.notify(@js(__('core::booking.seat_limit', ['count' => $seatLimit])));
                return;
            }
            this.selectedIds = [...this.selectedIds, seat.id];
        },
        money(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        },
        continueBooking() {
            if (!this.selectedIds.length) {
                this.notify(@js(__('core::booking.select_seat')));
                return;
            }
            if (!this.$refs.bookingForm.reportValidity()) return;
            this.notify(@js(__('core::booking.payment_pending')));
        },
    }">
        <div class="booking-page__banner">
            @include('core::partials.home.navbar')
            <div class="booking-page__hero-inner">
                <a class="booking-page__back" href="{{ route('trip-search', $criteria) }}">
                    <span class="booking-page__back-icon"><x-heroicon-o-arrow-left class="size-4" aria-hidden="true" /></span>
                    {{ __('core::booking.back') }}
                </a>
                <div class="booking-page__heading">
                    <h1 class="booking-page__route">
                        <span>{{ $origin }}</span>
                        <span class="booking-page__route-separator" aria-hidden="true">–</span>
                        <span>{{ $destination }}</span>
                    </h1>
                    <p class="booking-page__departure">{{ $departure->locale(app()->getLocale())->translatedFormat('l, d/m') }}</p>
                </div>
            </div>
        </div>

        <main class="booking-page__layout">
            <div class="booking-page__primary">
                <section class="booking-panel booking-page__seats" aria-labelledby="booking-seats-title">
                    <div class="booking-panel__header">
                        <h2 id="booking-seats-title">{{ __('core::booking.choose_seats') }}</h2>
                        <button type="button" class="booking-page__vehicle-link"
                            @click.stop="vehicleInfoOpen = !vehicleInfoOpen; vehicleInfoTab = 'schedule'"
                            :aria-expanded="vehicleInfoOpen" aria-controls="booking-vehicle-info"
                            aria-haspopup="dialog">{{ __('core::booking.vehicle_info') }}</button>
                    </div>
                    <div class="booking-page__seat-area">
                        <div class="booking-page__seat-map">
                            <template x-for="deck in @js($trip['seat_decks'])" :key="deck">
                                <div class="booking-page__deck">
                                    <h3 x-text="deck === 'lower' ? @js(__('core::booking.lower_deck')) : @js(__('core::booking.upper_deck'))"></h3>
                                    <div class="trip-card__seat-grid">
                                        <template x-for="seat in deckSeats(deck)" :key="seat.id">
                                            <button type="button" class="trip-card__seat"
                                                :style="@js($trip['demo_seat_map']) ? { gridColumn: seat.column, gridRow: seat.row } : {}"
                                                :class="seat.sold ? 'is-sold' : (selectedIds.includes(seat.id) ? 'is-chosen' : 'is-free')"
                                                :disabled="seat.sold"
                                                :aria-pressed="selectedIds.includes(seat.id)"
                                                :aria-label="seat.code + ', ' + (seat.sold ? @js(__('core::booking.sold')) : (selectedIds.includes(seat.id) ? @js(__('core::booking.selected')) : @js(__('core::booking.available'))))"
                                                @click="toggleSeat(seat)">
                                                <span class="trip-card__seat-code" x-text="seat.code"></span>
                                                <span class="trip-card__seat-foot" aria-hidden="true"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <p x-show="seats.length === 0" class="text-sm text-slate-500">{{ __('core::trip-search.no_seat_map') }}</p>
                        </div>
                        <div class="booking-page__legend" aria-label="{{ __('core::trip-search.seat_status') }}">
                            <span><i class="trip-card__seat-key is-sold"></i>{{ __('core::booking.sold') }}</span>
                            <span><i class="trip-card__seat-key is-free"></i>{{ __('core::booking.available') }}</span>
                            <span><i class="trip-card__seat-key is-chosen"></i>{{ __('core::booking.selected') }}</span>
                        </div>
                    </div>
                    <div id="booking-vehicle-info" class="booking-page__vehicle-popover"
                        x-show="vehicleInfoOpen" x-transition.opacity x-cloak
                        @click.outside="vehicleInfoOpen = false" @keydown.escape.window="vehicleInfoOpen = false"
                        role="dialog" aria-label="{{ __('core::booking.vehicle_info') }}">
                        <div class="booking-page__vehicle-tabs" role="tablist" aria-label="{{ __('core::booking.vehicle_info') }}">
                            @foreach (['schedule', 'media', 'amenities', 'policy'] as $tab)
                                <button type="button" role="tab"
                                    @click="vehicleInfoTab = '{{ $tab }}'"
                                    :aria-selected="vehicleInfoTab === '{{ $tab }}'"
                                    :class="vehicleInfoTab === '{{ $tab }}' ? 'is-active' : ''">
                                    {{ __('core::booking.vehicle_tab_'.$tab) }}
                                </button>
                            @endforeach
                        </div>
                        <div class="booking-page__vehicle-content">
                            <div x-show="vehicleInfoTab === 'schedule'" class="booking-page__vehicle-empty">
                                <p>{{ __('core::booking.schedule_empty') }}</p>
                            </div>
                            <div x-show="vehicleInfoTab === 'media'" class="booking-page__vehicle-empty">
                                <p>{{ __('core::booking.media_empty') }}</p>
                            </div>
                            <div x-show="vehicleInfoTab === 'amenities'" class="booking-page__vehicle-empty">
                                <p>{{ __('core::booking.amenities_empty') }}</p>
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
                                                <li>@include('core::partials.booking-linked-text', ['text' => $item])</li>
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
                </section>

                <form class="booking-page__form" x-ref="bookingForm" @submit.prevent="continueBooking()">
                    <section class="booking-panel" aria-labelledby="booking-customer-title">
                        <div class="booking-page__customer-grid">
                            <div class="booking-page__fields">
                                <h2 id="booking-customer-title">{{ __('core::booking.customer_info') }}</h2>
                                @foreach ([
                                    ['key' => 'full_name', 'name' => 'name', 'model' => 'customerName', 'ref' => 'customerNameInput', 'type' => 'text', 'autocomplete' => 'name'],
                                    ['key' => 'phone', 'name' => 'phone', 'model' => 'customerPhone', 'ref' => 'customerPhoneInput', 'type' => 'tel', 'autocomplete' => 'tel'],
                                    ['key' => 'email', 'name' => 'email', 'model' => 'customerEmail', 'ref' => 'customerEmailInput', 'type' => 'email', 'autocomplete' => 'email'],
                                ] as $field)
                                    <div class="booking-page__field">
                                        <label for="booking-customer-{{ $field['name'] }}">
                                            {{ __('core::booking.'.$field['key']) }} <b>*</b>
                                        </label>
                                        <div class="booking-page__input-wrap">
                                            <input id="booking-customer-{{ $field['name'] }}"
                                                name="{{ $field['name'] }}" type="{{ $field['type'] }}"
                                                autocomplete="{{ $field['autocomplete'] }}" required
                                                x-model="{{ $field['model'] }}" x-ref="{{ $field['ref'] }}">
                                            <button type="button" class="booking-page__clear-input"
                                                x-show="{{ $field['model'] }}.length > 0" x-cloak
                                                @click="{{ $field['model'] }} = ''; $refs.{{ $field['ref'] }}.focus()"
                                                aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.'.$field['key'])]) }}">
                                                <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="booking-page__terms">
                                <h3>{{ __('core::booking.terms_title') }}</h3>
                                <p class="booking-page__member-note">{{ __('core::booking.member_note') }}</p>
                                @foreach (__('core::booking.terms_notes') as $note)
                                    <p class="booking-page__terms-note"><span>(*) </span>@include('core::partials.booking-linked-text', ['text' => $note])</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="booking-page__accept">
                            <input id="booking-accept-terms" type="checkbox" required>
                            <span>
                                <button type="button" class="booking-page__terms-link"
                                    x-ref="termsModalTrigger" @click="openTermsModal()"
                                    aria-haspopup="dialog" aria-controls="booking-terms-modal">
                                    {{ __('core::booking.terms_accept_link') }}
                                </button>
                                <label for="booking-accept-terms">{{ __('core::booking.terms_accept_rest') }}</label>
                            </span>
                        </div>
                    </section>

                    <section class="booking-panel" aria-labelledby="booking-pickup-title">
                        <div class="booking-panel__header">
                            <h2 id="booking-pickup-title">{{ __('core::booking.pickup_title') }}</h2>
                            <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}" alt="" aria-hidden="true">
                        </div>
                        <div class="booking-page__pickup-grid">
                            <fieldset>
                                <legend>{{ __('core::booking.pickup') }}</legend>
                                <div class="booking-page__radio-row">
                                    <label><input type="radio" name="pickup_mode" value="station" x-model="pickupMode"> {{ __('core::booking.station') }}</label>
                                    <label><input type="radio" name="pickup_mode" value="transfer" x-model="pickupMode"> {{ __('core::booking.transfer') }}</label>
                                </div>
                                <div x-show="pickupMode === 'station'" class="booking-page__select-wrap">
                                    <select name="pickup_station" aria-label="{{ __('core::booking.pickup') }}">
                                        <option>{{ $origin }}</option>
                                    </select>
                                </div>
                                <div x-show="pickupMode === 'transfer'" class="booking-page__input-wrap">
                                    <input :required="pickupMode === 'transfer'" name="pickup_address"
                                        x-model="pickupAddress" x-ref="pickupAddressInput"
                                        placeholder="{{ __('core::booking.transfer_address') }}">
                                    <button type="button" class="booking-page__clear-input"
                                        x-show="pickupAddress.length > 0" x-cloak
                                        @click="pickupAddress = ''; $refs.pickupAddressInput.focus()"
                                        aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                                        <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                                    </button>
                                </div>
                                <p x-show="pickupMode === 'station'" class="booking-page__boarding-reminder">
                                    {{ __('core::booking.arrive_at') }} <strong>{{ $origin }}</strong>
                                    <em>{{ __('core::booking.before_time') }} {{ $departure->copy()->subMinutes(15)->format('H:i d/m/Y') }}</em>
                                    {{ __('core::booking.boarding_help') }}
                                </p>
                            </fieldset>
                            <fieldset>
                                <legend>{{ __('core::booking.dropoff') }}</legend>
                                <div class="booking-page__radio-row">
                                    <label><input type="radio" name="dropoff_mode" value="station" x-model="dropoffMode"> {{ __('core::booking.station') }}</label>
                                    <label><input type="radio" name="dropoff_mode" value="transfer" x-model="dropoffMode"> {{ __('core::booking.transfer') }}</label>
                                </div>
                                <div x-show="dropoffMode === 'station'" class="booking-page__select-wrap">
                                    <select name="dropoff_station" aria-label="{{ __('core::booking.dropoff') }}">
                                        <option>{{ $destination }}</option>
                                    </select>
                                </div>
                                <div x-show="dropoffMode === 'transfer'" class="booking-page__input-wrap">
                                    <input :required="dropoffMode === 'transfer'" name="dropoff_address"
                                        x-model="dropoffAddress" x-ref="dropoffAddressInput"
                                        placeholder="{{ __('core::booking.transfer_address') }}">
                                    <button type="button" class="booking-page__clear-input"
                                        x-show="dropoffAddress.length > 0" x-cloak
                                        @click="dropoffAddress = ''; $refs.dropoffAddressInput.focus()"
                                        aria-label="{{ __('core::booking.clear_field', ['field' => __('core::booking.transfer_address')]) }}">
                                        <x-heroicon-o-x-mark class="size-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </fieldset>
                        </div>
                    </section>
                    <div class="booking-page__actions">
                        <div><span>FUTAPAY</span><strong x-text="money(selectedIds.length * fare)"></strong></div>
                        <a href="{{ route('trip-search', $criteria) }}">{{ __('core::booking.cancel') }}</a>
                        <button type="submit">{{ __('core::booking.pay') }}</button>
                    </div>
                </form>
            </div>

            <aside class="booking-page__aside" aria-label="{{ __('core::booking.trip_info') }}">
                <section id="booking-trip-info" class="booking-panel booking-page__summary">
                    <div class="booking-panel__header">
                        <h2>{{ __('core::booking.trip_info') }}</h2>
                        <button type="button" class="booking-page__detail-link" x-ref="tripDetailTrigger"
                            @click="openTripDetail()" aria-haspopup="dialog"
                            :aria-expanded="tripDetailOpen" aria-controls="booking-trip-detail-modal">
                            {{ __('core::booking.detail') }}
                        </button>
                    </div>
                    @include('core::partials.booking-trip-summary')
                </section>
                <section class="booking-panel booking-page__summary booking-page__price-summary">
                    <div class="booking-panel__header">
                        <h2>{{ __('core::booking.price_detail') }}</h2>
                        <button type="button" class="booking-page__info-button"
                            @click.stop="priceTooltipOpen = !priceTooltipOpen"
                            :aria-expanded="priceTooltipOpen" aria-controls="booking-price-tooltip"
                            aria-label="{{ __('core::trip-search.cancellation_policy') }}">
                            <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}"
                                alt="" aria-hidden="true">
                        </button>
                    </div>
                    <div id="booking-price-tooltip" class="booking-page__price-tooltip"
                        x-show="priceTooltipOpen" x-transition.opacity x-cloak
                        @click.outside="priceTooltipOpen = false" @keydown.escape.window="priceTooltipOpen = false"
                        role="tooltip">
                        <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
                        <ul>
                            @foreach (__('core::trip-search.cancellation_items') as $item)
                                <li>@include('core::partials.booking-linked-text', ['text' => $item])</li>
                            @endforeach
                        </ul>
                    </div>
                    <dl>
                        <div><dt>{{ __('core::booking.fare') }}</dt><dd x-text="money(selectedIds.length * fare)"></dd></div>
                        <div><dt>{{ __('core::booking.payment_fee') }}</dt><dd>0đ</dd></div>
                        <div class="booking-page__total"><dt>{{ __('core::booking.total') }}</dt><dd x-text="money(selectedIds.length * fare)"></dd></div>
                    </dl>
                </section>
            </aside>
        </main>
        <div id="booking-trip-detail-modal" class="booking-page__modal-backdrop"
            x-show="tripDetailOpen" x-transition.opacity x-cloak
            @click.self="closeTripDetail()" @keydown.escape.window="if (tripDetailOpen) closeTripDetail()"
            role="presentation">
            <div class="booking-page__modal" role="dialog" aria-modal="true"
                aria-labelledby="booking-trip-detail-title">
                <div class="booking-page__modal-heading">
                    <h2 id="booking-trip-detail-title">
                        {{ __('core::booking.trip_details_title', ['count' => 1]) }}
                    </h2>
                    <button type="button" class="booking-page__info-button"
                        @click.stop="policyTooltipOpen = !policyTooltipOpen"
                        :aria-expanded="policyTooltipOpen" aria-controls="booking-policy-tooltip"
                        aria-label="{{ __('core::trip-search.cancellation_policy') }}">
                        <img class="booking-page__info-icon" src="{{ asset('icons/booking-info.png') }}"
                            alt="" aria-hidden="true">
                    </button>
                    <div id="booking-policy-tooltip" class="booking-page__policy-tooltip"
                        x-show="policyTooltipOpen" x-transition.opacity x-cloak
                        @click.outside="policyTooltipOpen = false" role="tooltip">
                        <h3>{{ __('core::trip-search.cancellation_policy') }}</h3>
                        <ul>
                            @foreach (__('core::trip-search.cancellation_items') as $item)
                                <li>@include('core::partials.booking-linked-text', ['text' => $item])</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="booking-page__modal-close" x-ref="tripDetailClose"
                        @click="closeTripDetail()" aria-label="{{ __('core::booking.close') }}">
                        <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                    </button>
                </div>
                <div class="booking-panel booking-page__summary booking-page__modal-summary">
                    @include('core::partials.booking-trip-summary')
                </div>
            </div>
        </div>
        <div id="booking-terms-modal" class="booking-page__modal-backdrop booking-page__terms-backdrop"
            x-show="termsModalOpen" x-transition.opacity x-cloak
            @click.self="closeTermsModal()" @keydown.escape.window="if (termsModalOpen) closeTermsModal()"
            role="presentation">
            <div class="booking-page__terms-dialog" role="dialog" aria-modal="true"
                aria-labelledby="booking-terms-title">
                <div class="booking-page__terms-dialog-heading">
                    <h2 id="booking-terms-title">{{ __('core::booking.customer_rights_title') }}</h2>
                    <button type="button" class="booking-page__modal-close" x-ref="termsModalClose"
                        @click="closeTermsModal()" aria-label="{{ __('core::booking.close') }}">
                        <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                    </button>
                </div>
                <div class="booking-page__terms-dialog-body" tabindex="0"
                    :class="{ 'is-scrolling': termsScrolling }" @scroll.passive="showTermsScrollbar()">
                    <ol>
                        @foreach (__('core::booking.customer_rights') as $clause)
                            <li>
                                @include('core::partials.booking-linked-text', ['text' => $clause['body']])
                                @if (!empty($clause['note']))
                                    <p>@include('core::partials.booking-linked-text', ['text' => $clause['note']])</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
        @include('core::partials.home.footer')
    </div>
@endsection

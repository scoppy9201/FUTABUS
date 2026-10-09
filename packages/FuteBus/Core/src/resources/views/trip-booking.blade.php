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
        captchaOpen: false,
        captchaDragging: false,
        captchaVerified: false,
        captchaError: false,
        captchaSliderOffset: 0,
        captchaSliderStart: 0,
        captchaPointerStart: 0,
        captchaOffset: 0,
        captchaTarget: 180,
        captchaTop: 52,
        captchaImageWidth: 360,
        captchaImageHeight: 160,
        captchaSuccessTimer: null,
        captchaImages: @js([
            asset('images/popular-routes/da-lat.png'),
            asset('images/popular-routes/da-nang.png'),
            asset('images/popular-routes/ho-chi-minh-city.png'),
        ]),
        captchaImageIndex: -1,
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
        openCaptcha() {
            if (this.captchaOpen) return;
            this.savedPageOverflow = {
                html: document.documentElement.style.overflow,
                body: document.body.style.overflow,
            };
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            this.captchaOpen = true;
            this.$nextTick(() => {
                this.resetCaptcha();
                this.$refs.captchaHandle.focus();
            });
        },
        resetCaptcha() {
            clearTimeout(this.captchaSuccessTimer);
            this.captchaDragging = false;
            this.captchaVerified = false;
            this.captchaError = false;
            this.captchaSliderOffset = 0;
            this.captchaOffset = 0;
            this.captchaImageIndex = (this.captchaImageIndex + 1) % this.captchaImages.length;
            this.captchaImageWidth = this.$refs.captchaImage.clientWidth;
            this.captchaImageHeight = this.$refs.captchaImage.clientHeight;
            const travel = this.captchaImageWidth - 52;
            this.captchaTarget = Math.round(travel * (0.55 + Math.random() * 0.25));
            this.captchaTop = Math.round(24 + Math.random() * (this.captchaImageHeight - 86));
            this.$refs.captchaHandle.focus();
        },
        closeCaptcha() {
            if (!this.captchaOpen) return;
            clearTimeout(this.captchaSuccessTimer);
            this.captchaDragging = false;
            this.captchaOpen = false;
            document.documentElement.style.overflow = this.savedPageOverflow?.html ?? '';
            document.body.style.overflow = this.savedPageOverflow?.body ?? '';
            this.savedPageOverflow = null;
            this.$nextTick(() => this.$refs.captchaTrigger.focus());
        },
        setCaptchaSlider(offset) {
            const travel = Math.max(1, this.$refs.captchaTrack.clientWidth - this.$refs.captchaHandle.clientWidth);
            this.captchaSliderOffset = Math.max(0, Math.min(travel, offset));
            this.captchaOffset = Math.round(this.captchaSliderOffset / travel * (this.captchaImageWidth - 52));
            this.captchaError = false;
        },
        startCaptcha(event) {
            if (this.captchaVerified) return;
            this.captchaDragging = true;
            this.captchaPointerStart = event.clientX;
            this.captchaSliderStart = this.captchaSliderOffset;
            event.currentTarget.setPointerCapture(event.pointerId);
        },
        moveCaptcha(event) {
            if (!this.captchaDragging) return;
            this.setCaptchaSlider(this.captchaSliderStart + event.clientX - this.captchaPointerStart);
        },
        finishCaptcha() {
            if (!this.captchaDragging) return;
            this.captchaDragging = false;
            this.verifyCaptcha();
        },
        verifyCaptcha() {
            if (this.captchaVerified) return;
            if (Math.abs(this.captchaOffset - this.captchaTarget) > 8) {
                this.setCaptchaSlider(0);
                this.captchaError = true;
                return;
            }
            this.captchaVerified = true;
            this.captchaError = false;
            this.captchaSuccessTimer = setTimeout(() => {
                this.closeCaptcha();
                this.$nextTick(() => this.validateBookingAfterCaptcha());
            }, 500);
        },
        phoneError() {
            const value = this.customerPhone.trim();
            return value !== '' && !/^(?:0[35789]\d{8}|\+84[35789]\d{8})$/.test(value);
        },
        emailError() {
            const value = this.customerEmail.trim();
            return value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        },
        validateBookingAfterCaptcha() {
            if (!this.$refs.acceptTerms.checked) {
                this.notify(@js(__('core::booking.accept_terms_required')));
                return;
            }
            if (!this.selectedIds.length) {
                this.notify(@js(__('core::booking.select_seat')));
                return;
            }
            if (this.phoneError()) {
                this.$refs.customerPhoneInput.focus();
                return;
            }
            if (this.emailError()) {
                this.$refs.customerEmailInput.focus();
                return;
            }
            if (!this.$refs.bookingForm.reportValidity()) return;
            this.notify(@js(__('core::booking.payment_pending')));
        },
        continueBooking() {
            this.openCaptcha();
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

                @include('core::partials.booking-form')
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
        @include('core::partials.booking-trip-detail-modal')
        @include('core::partials.booking-terms-modal')
        @include('core::partials.booking-captcha-modal')
        @include('core::partials.home.footer')
    </div>
@endsection

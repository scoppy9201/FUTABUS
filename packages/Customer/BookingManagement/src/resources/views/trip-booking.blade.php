@extends('core::layouts.home')

@push('styles')
    @vite('packages/Customer/BookingManagement/src/resources/css/app.css')
@endpush

@section('title', __('core::booking.page_title'))

@section('content')
    @php
        $origin = $trip['origin'];
        $destination = $trip['destination'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $outboundDeparture = $outboundTrip ? \Illuminate\Support\Carbon::parse($outboundTrip['departure_time']) : null;
        $seatLimit = 5;
    @endphp
    <div class="booking-page" x-data="{
        seats: @js($trip['seats']),
        selectedIds: @js($selectedSeatIds),
        outboundSeats: @js($outboundTrip['seats'] ?? []),
        outboundSelectedIds: @js($outboundSeatIds),
        customerName: @js(auth()->user()?->name ?? ''),
        customerPhone: @js(auth()->user()?->phone ?? ''),
        customerEmail: @js(auth()->user()?->email ?? ''),
        pickupAddress: '',
        dropoffAddress: '',
        outboundPickupAddress: '',
        outboundDropoffAddress: '',
        fare: @js($trip['price']),
        outboundFare: @js($outboundTrip['price'] ?? 0),
        roundTrip: @js($outboundTrip !== null),
        limit: @js($seatLimit),
        pickupMode: 'station',
        dropoffMode: 'station',
        outboundPickupMode: 'station',
        outboundDropoffMode: 'station',
        notify(message) {
            window.FutaNotify?.show(message, { tone: 'warning' });
        },
        vehicleInfoOpen: null,
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
        openTripDetail(event) {
            if (this.tripDetailOpen) return;
            this.tripDetailTrigger = event?.currentTarget ?? this.$refs.tripDetailTrigger;
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
            this.$nextTick(() => this.tripDetailTrigger?.focus());
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
        selectedOutboundSeats() {
            return this.outboundSeats.filter(seat => this.outboundSelectedIds.includes(seat.id));
        },
        deckSeats(deck, leg = 'return') {
            const seats = leg === 'outbound' ? this.outboundSeats : this.seats;
            return seats.filter(seat => seat.deck === deck);
        },
        seatSelected(seat, leg = 'return') {
            return (leg === 'outbound' ? this.outboundSelectedIds : this.selectedIds).includes(seat.id);
        },
        toggleSeat(seat, leg = 'return') {
            if (seat.sold) return;
            const selected = leg === 'outbound' ? this.outboundSelectedIds : this.selectedIds;
            if (selected.includes(seat.id)) {
                if (leg === 'outbound') this.outboundSelectedIds = selected.filter(id => id !== seat.id);
                else this.selectedIds = selected.filter(id => id !== seat.id);
                return;
            }
            if (selected.length >= this.limit) {
                this.notify(@js(__('core::booking.seat_limit', ['count' => $seatLimit])));
                return;
            }
            if (leg === 'outbound') this.outboundSelectedIds = [...selected, seat.id];
            else this.selectedIds = [...selected, seat.id];
        },
        total() {
            return this.selectedIds.length * this.fare + this.outboundSelectedIds.length * this.outboundFare;
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
            if (!this.selectedIds.length || (this.roundTrip && !this.outboundSelectedIds.length)) {
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
            this.$refs.bookingForm.submit();
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
                        <span>{{ $outboundTrip['origin'] ?? $origin }}</span>
                        <span class="booking-page__route-separator" aria-hidden="true">–</span>
                        <span>{{ $outboundTrip['destination'] ?? $destination }}</span>
                    </h1>
                    <p class="booking-page__departure">
                        @if ($outboundDeparture)
                            {{ $outboundDeparture->locale(app()->getLocale())->translatedFormat('l, d/m') }} -
                        @endif
                        {{ $departure->locale(app()->getLocale())->translatedFormat('l, d/m') }}
                    </p>
                </div>
            </div>
        </div>

        <main class="booking-page__layout">
            <div class="booking-page__primary">
                @if ($outboundTrip)
                    <div class="booking-page__round-trip-legs">
                        <section class="booking-panel booking-page__seats" aria-labelledby="booking-outbound-seats-title">
                            <div class="booking-panel__header booking-page__leg-header">
                                <div>
                                    <h2 id="booking-outbound-seats-title">{{ __('core::booking.choose_seats') }}</h2>
                                    <p>{{ __('core::booking.outbound_leg') }} - {{ \Illuminate\Support\Carbon::parse($outboundTrip['departure_time'])->locale(app()->getLocale())->translatedFormat('l, d/m') }}</p>
                                </div>
                                <button type="button" class="booking-page__vehicle-link"
                                    @click.stop="vehicleInfoOpen = vehicleInfoOpen === 'outbound' ? null : 'outbound'; vehicleInfoTab = 'schedule'"
                                    :aria-expanded="vehicleInfoOpen === 'outbound'" aria-controls="booking-outbound-vehicle-info"
                                    aria-haspopup="dialog">{{ __('core::booking.vehicle_info') }}</button>
                            </div>
                            @include('BookingManagement::partials.booking-seat-map', ['seatTrip' => $outboundTrip, 'leg' => 'outbound', 'sharedLegend' => true])
                            @include('BookingManagement::partials.booking-vehicle-popover', ['vehicleLeg' => 'outbound'])
                        </section>
                @endif
                <section class="booking-panel booking-page__seats" aria-labelledby="booking-seats-title">
                    <div class="booking-panel__header">
                        <div>
                            <h2 id="booking-seats-title">{{ __('core::booking.choose_seats') }}</h2>
                            @if ($outboundTrip)
                                <p>{{ __('core::booking.return_leg') }} - {{ $departure->locale(app()->getLocale())->translatedFormat('l, d/m') }}</p>
                            @endif
                        </div>
                        <button type="button" class="booking-page__vehicle-link"
                            @click.stop="vehicleInfoOpen = vehicleInfoOpen === 'return' ? null : 'return'; vehicleInfoTab = 'schedule'"
                            :aria-expanded="vehicleInfoOpen === 'return'" aria-controls="booking-return-vehicle-info"
                            aria-haspopup="dialog">{{ __('core::booking.vehicle_info') }}</button>
                    </div>
                    @include('BookingManagement::partials.booking-seat-map', ['seatTrip' => $trip, 'leg' => 'return', 'sharedLegend' => (bool) $outboundTrip])
                    @include('BookingManagement::partials.booking-vehicle-popover', ['vehicleLeg' => 'return'])
                </section>
                @if ($outboundTrip)
                    <div class="booking-page__round-trip-legend">
                        @include('BookingManagement::partials.booking-seat-legend')
                    </div>
                    </div>
                @endif

                @include('BookingManagement::partials.booking-form')
            </div>

            <aside class="booking-page__aside" aria-label="{{ __('core::booking.trip_info') }}">
                @if ($outboundTrip)
                    <section class="booking-panel booking-page__summary">
                        <div class="booking-panel__header">
                            <h2>{{ __('core::booking.trip_info') }}<span class="sr-only"> · {{ __('core::booking.outbound_leg') }}</span></h2>
                            <button type="button" class="booking-page__detail-link"
                                @click="openTripDetail($event)" aria-haspopup="dialog"
                                :aria-expanded="tripDetailOpen" aria-controls="booking-trip-detail-modal">
                                {{ __('core::booking.detail') }}
                            </button>
                        </div>
                        @include('BookingManagement::partials.booking-trip-summary', ['summaryTrip' => $outboundTrip, 'summaryLeg' => 'outbound'])
                    </section>
                @endif
                <section id="booking-trip-info" class="booking-panel booking-page__summary">
                    <div class="booking-panel__header">
                        <h2>{{ __('core::booking.trip_info') }}@if ($outboundTrip)<span class="sr-only"> · {{ __('core::booking.return_leg') }}</span>@endif</h2>
                        <button type="button" class="booking-page__detail-link" x-ref="tripDetailTrigger"
                            @click="openTripDetail($event)" aria-haspopup="dialog"
                            :aria-expanded="tripDetailOpen" aria-controls="booking-trip-detail-modal">
                            {{ __('core::booking.detail') }}
                        </button>
                    </div>
                    @include('BookingManagement::partials.booking-trip-summary')
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
                                <li>@include('BookingManagement::partials.booking-linked-text', ['text' => $item])</li>
                            @endforeach
                        </ul>
                    </div>
                    <dl>
                        @if ($outboundTrip)
                            <div><dt>{{ __('core::booking.outbound_fare') }}</dt><dd x-text="money(outboundSelectedIds.length * outboundFare)"></dd></div>
                            <div><dt>{{ __('core::booking.return_fare') }}</dt><dd x-text="money(selectedIds.length * fare)"></dd></div>
                        @else
                            <div><dt>{{ __('core::booking.fare') }}</dt><dd x-text="money(selectedIds.length * fare)"></dd></div>
                        @endif
                        <div><dt>{{ __('core::booking.payment_fee') }}</dt><dd>0đ</dd></div>
                        <div class="booking-page__total"><dt>{{ __('core::booking.total') }}</dt><dd x-text="money(total())"></dd></div>
                    </dl>
                </section>
            </aside>
        </main>
        @include('BookingManagement::partials.booking-trip-detail-modal')
        @include('BookingManagement::partials.booking-terms-modal')
        @include('BookingManagement::partials.booking-captcha-modal')
        @include('core::partials.home.footer')
    </div>
@endsection

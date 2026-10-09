@extends('core::layouts.home')

@section('title', __('core::booking.payment_page_title'))

@section('content')
    @php
        $trip = $preview['trip'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $total = count($preview['seats']) * $trip['fare'];
        $formattedTotal = number_format($total, 0, ',', '.').'đ';
        $bookingParams = array_merge(
            ['trip' => $trip['id']],
            $preview['criteria'],
            ['direction' => $preview['direction'], 'seats' => implode(',', $preview['seat_ids'])]
        );
        $methods = [
            ['name' => __('core::booking.payment_sepay'), 'image' => 'images/sepay.png', 'class' => 'sepay'],
            ['name' => 'FUTAPay', 'image' => 'images/auth/White Brushstroke F on Forest Green.png', 'class' => 'futapay'],
            ['name' => 'ZaloPay', 'image' => 'images/zalopay.png', 'class' => 'zalopay', 'has_note' => true],
            ['name' => 'VNPay', 'image' => 'images/vnpay.png', 'class' => 'vnpay', 'has_note' => true],
            ['name' => 'ShopeePay', 'image' => 'images/shopeepay.png', 'class' => 'shopeepay', 'has_note' => true],
            ['name' => 'MoMo', 'image' => 'images/momo.png', 'class' => 'momo', 'has_note' => true],
            ['name' => 'Viettel Money', 'image' => 'images/vettelmoney.png', 'class' => 'viettel'],
            ['name' => 'MB Bank', 'image' => 'images/mbbank.png', 'class' => 'mbbank', 'has_note' => true],
            ['name' => __('core::booking.payment_atm'), 'image' => 'images/napas.png', 'class' => 'atm'],
            ['name' => __('core::booking.payment_card'), 'image' => 'images/visa.png', 'class' => 'card'],
        ];
    @endphp
    <div class="payment-page" x-data="{
        detailOpen: false,
        tripTooltipOpen: false,
        priceTooltipOpen: false,
        paymentMethod: 'sepay',
        remainingSeconds: @js(max(0, $preview['created_at'] + 600 - now()->timestamp)),
        countdown: null,
        savedPageOverflow: null,
        openTripDetail() {
            if (this.detailOpen) return;
            this.savedPageOverflow = {
                html: document.documentElement.style.overflow,
                body: document.body.style.overflow,
            };
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            this.detailOpen = true;
            this.$nextTick(() => this.$refs.tripDetailClose.focus());
        },
        closeTripDetail() {
            if (!this.detailOpen) return;
            this.detailOpen = false;
            document.documentElement.style.overflow = this.savedPageOverflow?.html ?? '';
            document.body.style.overflow = this.savedPageOverflow?.body ?? '';
            this.savedPageOverflow = null;
            this.$nextTick(() => this.$refs.tripDetailTrigger.focus());
        },
        init() {
            if (this.remainingSeconds <= 0) {
                window.location.replace(@js(route('home').'#trip-search'));
                return;
            }
            this.countdown = setInterval(() => {
                this.remainingSeconds = Math.max(0, this.remainingSeconds - 1);
                if (this.remainingSeconds === 0) {
                    clearInterval(this.countdown);
                    window.location.replace(@js(route('home').'#trip-search'));
                }
            }, 1000);
        },
        destroy() {
            clearInterval(this.countdown);
            if (this.detailOpen) {
                document.documentElement.style.overflow = this.savedPageOverflow?.html ?? '';
                document.body.style.overflow = this.savedPageOverflow?.body ?? '';
            }
        },
        countdownText() {
            const minutes = String(Math.floor(this.remainingSeconds / 60)).padStart(2, '0');
            const seconds = String(this.remainingSeconds % 60).padStart(2, '0');
            return minutes + ' : ' + seconds;
        },
    }">
        <div class="booking-page__banner">
            @include('core::partials.home.navbar')
            <div class="booking-page__hero-inner">
                <a class="booking-page__back" href="{{ route('trip-booking.show', $bookingParams) }}">
                    <span class="booking-page__back-icon">
                        <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
                    </span>
                    {{ __('core::booking.payment_back') }}
                </a>
                <div class="booking-page__heading">
                    <h1 class="booking-page__route">
                        <span>{{ $trip['origin'] }}</span>
                        <span class="booking-page__route-separator" aria-hidden="true">–</span>
                        <span>{{ $trip['destination'] }}</span>
                    </h1>
                    <p class="booking-page__departure">
                        {{ $departure->locale(app()->getLocale())->translatedFormat('l, d/m') }}
                    </p>
                </div>
            </div>
        </div>

        <main class="payment-page__layout">
            <section class="payment-page__methods" aria-labelledby="payment-methods-title">
                <h2 id="payment-methods-title">{{ __('core::booking.payment_methods_title') }}</h2>
                <div class="payment-page__method-list">
                    @foreach ($methods as $method)
                        @if (in_array($method['class'], ['sepay', 'futapay'], true))
                            <button type="button" class="payment-page__method"
                                :class="{ 'is-active': paymentMethod === '{{ $method['class'] }}' }"
                                :aria-pressed="paymentMethod === '{{ $method['class'] }}'"
                                @click="paymentMethod = '{{ $method['class'] }}'">
                                <span class="payment-page__radio" aria-hidden="true"></span>
                                <img class="payment-page__method-logo is-{{ $method['class'] }}"
                                    src="{{ asset($method['image']) }}" alt="" aria-hidden="true">
                                <span class="payment-page__method-copy"><strong>{{ $method['name'] }}</strong></span>
                            </button>
                        @else
                            <button type="button" class="payment-page__method {{ $method['class'] === 'atm' ? 'is-separated' : '' }}"
                                @click="window.FutaNotify?.show(@js(__('core::booking.payment_unsupported')), { tone: 'info' })">
                                <span class="payment-page__radio" aria-hidden="true"></span>
                                <img class="payment-page__method-logo is-{{ $method['class'] }}"
                                    src="{{ asset($method['image']) }}" alt="" aria-hidden="true">
                                <span class="payment-page__method-copy">
                                    <strong>{{ $method['name'] }}</strong>
                                    @if ($method['has_note'] ?? false)
                                        <small>{{ __('core::booking.payment_unsupported') }}</small>
                                    @endif
                                </span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </section>

            @include('core::partials.payment-preview-qr')

            <aside class="payment-page__aside">
                <section class="payment-page__summary-card">
                    <h2>{{ __('core::booking.payment_customer_title') }}</h2>
                    <dl>
                        <div><dt>{{ __('core::booking.full_name') }}</dt><dd>{{ $preview['customer']['name'] }}</dd></div>
                        <div><dt>{{ __('core::booking.phone') }}</dt><dd>{{ $preview['customer']['phone'] }}</dd></div>
                        <div><dt>{{ __('core::booking.email') }}</dt><dd>{{ $preview['customer']['email'] }}</dd></div>
                    </dl>
                </section>
                <section class="payment-page__summary-card">
                    <div class="payment-page__card-heading">
                        <h2>{{ __('core::booking.trip_info') }}</h2>
                        @include('core::partials.payment-preview-trip-tooltip')
                        <button type="button" class="payment-page__detail-link" x-ref="tripDetailTrigger" @click="openTripDetail()">
                            {{ __('core::booking.detail') }}
                        </button>
                    </div>
                    @include('core::partials.payment-preview-trip-summary')
                </section>
                <section class="payment-page__summary-card">
                    <div class="payment-page__card-heading">
                        <h2>{{ __('core::booking.price_detail') }}</h2>
                        @include('core::partials.payment-preview-price-tooltip')
                    </div>
                    <dl>
                        <div><dt>{{ __('core::booking.fare') }}</dt><dd>{{ $formattedTotal }}</dd></div>
                        <div><dt>{{ __('core::booking.payment_fee') }}</dt><dd>0đ</dd></div>
                        <div class="payment-page__total-row">
                            <dt>{{ __('core::booking.total') }}</dt><dd>{{ $formattedTotal }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </main>

        <div class="booking-page__modal-backdrop" x-show="detailOpen" x-cloak
            @click.self="closeTripDetail()" @keydown.escape.window="closeTripDetail()">
            <div class="booking-page__modal" role="dialog" aria-modal="true"
                aria-labelledby="payment-trip-detail-title">
                <div class="booking-page__modal-heading">
                    <h2 id="payment-trip-detail-title">{{ __('core::booking.trip_details_title', ['count' => 1]) }}</h2>
                    <button type="button" class="booking-page__modal-close" x-ref="tripDetailClose" @click="closeTripDetail()"
                        aria-label="{{ __('core::booking.close') }}">
                        <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                    </button>
                </div>
                <div class="payment-page__modal-content">
                    @include('core::partials.payment-preview-trip-summary')
                </div>
            </div>
        </div>
        @include('core::partials.home.footer')
    </div>
@endsection

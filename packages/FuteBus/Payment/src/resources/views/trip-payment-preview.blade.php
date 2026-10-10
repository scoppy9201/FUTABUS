@extends('core::layouts.home')

@push('styles')
    @vite('packages/FuteBus/Payment/src/resources/css/app.css')
@endpush

@push('scripts')
    @vite('packages/FuteBus/Payment/src/resources/js/app.js')
@endpush

@section('title', __('Payment::payment.payment_page_title'))

@section('content')
    @php
        $trip = $preview['trip'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $total = count($preview['seats']) * $trip['fare'];
        $formattedTotal = number_format($total, 0, ',', '.').'đ';
        $methods = [
            ['name' => __('Payment::payment.payment_sepay'), 'image' => 'images/sepay.png', 'class' => 'sepay'],
            ['name' => 'FUTAPay', 'image' => 'images/auth/White Brushstroke F on Forest Green.png', 'class' => 'futapay'],
            ['name' => 'ZaloPay', 'image' => 'images/zalopay.png', 'class' => 'zalopay', 'has_note' => true],
            ['name' => 'VNPay', 'image' => 'images/vnpay.png', 'class' => 'vnpay', 'has_note' => true],
            ['name' => 'ShopeePay', 'image' => 'images/shopeepay.png', 'class' => 'shopeepay', 'has_note' => true],
            ['name' => 'MoMo', 'image' => 'images/momo.png', 'class' => 'momo', 'has_note' => true],
            ['name' => 'Viettel Money', 'image' => 'images/vettelmoney.png', 'class' => 'viettel'],
            ['name' => 'MB Bank', 'image' => 'images/mbbank.png', 'class' => 'mbbank', 'has_note' => true],
            ['name' => __('Payment::payment.payment_atm'), 'image' => 'images/napas.png', 'class' => 'atm'],
            ['name' => __('Payment::payment.payment_card'), 'image' => 'images/visa.png', 'class' => 'card'],
        ];
    @endphp
    <div class="payment-page" x-data="{
        detailOpen: false,
        tripTooltipOpen: false,
        priceTooltipOpen: false,
        paymentMethod: 'sepay',
        paymentEnabled: @js($paymentEnabled),
        remainingSeconds: @js(max(0, $preview['created_at'] + 600 - now()->timestamp)),
        countdown: null,
        statusTimer: null,
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
        async refreshPaymentState() {
            if (!this.paymentEnabled) return false;
            try {
                const response = await fetch(@js(route('trip-payment-preview.status', ['draft' => $preview['token']])), {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) return false;
                const result = await response.json();
                if (result.status === 'paid' || result.status === 'needs_review') {
                    window.location.replace(@js(route('trip-payment-preview.show', ['draft' => $preview['token']])));
                    return true;
                }
                if (result.status === 'expired') {
                    window.location.replace(@js(route('home').'#trip-search'));
                    return true;
                }
            } catch (error) {
                return false;
            }
            return false;
        },
        init() {
            if (this.paymentEnabled) {
                this.statusTimer = setInterval(() => this.refreshPaymentState(), 3000);
            }
            if (this.remainingSeconds <= 0) {
                this.refreshPaymentState().then((handled) => {
                    if (!handled) window.location.replace(@js(route('home').'#trip-search'));
                });
                return;
            }
            this.countdown = setInterval(async () => {
                this.remainingSeconds = Math.max(0, this.remainingSeconds - 1);
                if (this.remainingSeconds === 0) {
                    clearInterval(this.countdown);
                    clearInterval(this.statusTimer);
                    const handled = await this.refreshPaymentState();
                    if (!handled) window.location.replace(@js(route('home').'#trip-search'));
                }
            }, 1000);
        },
        destroy() {
            clearInterval(this.countdown);
            clearInterval(this.statusTimer);
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
                <form method="POST" action="{{ route('trip-payment-preview.cancel', ['draft' => $preview['token']]) }}"
                    data-confirm
                    data-confirm-title="{{ __('Payment::payment.payment_back_confirm_title') }}"
                    data-confirm-message="{{ __('Payment::payment.payment_back_confirm_message') }}">
                    @csrf
                    <button type="submit" class="booking-page__back cursor-pointer">
                        <span class="booking-page__back-icon">
                            <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
                        </span>
                        {{ __('Payment::payment.payment_back') }}
                    </button>
                </form>
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
                <h2 id="payment-methods-title" class="mb-5 text-[21px] font-semibold text-gray-900">{{ __('Payment::payment.payment_methods_title') }}</h2>
                <div class="grid gap-1">
                    @foreach ($methods as $method)
                        @if (in_array($method['class'], ['sepay', 'futapay'], true))
                            <button type="button" class="payment-page__method group grid min-h-15.75 w-full cursor-pointer grid-cols-[24px_40px_minmax(0,1fr)] items-center gap-4
                                rounded-lg py-1.75 pr-0.5 text-left text-[#172033] hover:bg-futa-orange-soft focus-visible:outline-2
                                focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                                :class="{ 'is-active': paymentMethod === '{{ $method['class'] }}' }"
                                :aria-pressed="paymentMethod === '{{ $method['class'] }}'"
                                @click="paymentMethod = '{{ $method['class'] }}'">
                                <span class="block size-4.25 rounded-full border border-[#cbd5df] group-[.is-active]:border-futa-orange group-[.is-active]:bg-futa-orange group-[.is-active]:shadow-[inset_0_0_0_4px_#fff]" aria-hidden="true"></span>
                                <img
                                    @class(['block size-10 rounded-[5px] bg-white object-cover',
                                        'ml-[-6px] w-13! object-contain' => $method['class'] === 'sepay',
                                        'border border-[#e7eaf0] object-contain' => in_array($method['class'], ['atm',
                                        'card'], true),
                                        'object-[center_31%]' => $method['class'] === 'vnpay'])
                                    src="{{ asset($method['image']) }}" alt="" aria-hidden="true">
                                <span class="grid min-w-0 content-center gap-0.5"><strong class="text-base leading-[1.3] font-semibold text-gray-900">{{ $method['name'] }}</strong></span>
                            </button>
                        @else
                            <button type="button"
                                @class(['payment-page__method group grid min-h-15.75 w-full cursor-pointer grid-cols-[24px_40px_minmax(0,1fr)] items-center gap-4
                                    rounded-lg py-1.75 pr-0.5 text-left text-[#172033] hover:bg-futa-orange-soft focus-visible:outline-2
                                    focus-visible:outline-offset-2 focus-visible:outline-futa-orange',
                                    'mt-3.5 border-t border-[#e2e6eb] pt-5.5 rounded-none' => $method['class'] === 'atm'])
                                @click="window.FutaNotify?.show(@js(__('Payment::payment.payment_unsupported')), { tone: 'info' })">
                                <span class="block size-4.25 rounded-full border border-[#cbd5df] group-[.is-active]:border-futa-orange group-[.is-active]:bg-futa-orange group-[.is-active]:shadow-[inset_0_0_0_4px_#fff]" aria-hidden="true"></span>
                                <img
                                    @class(['block size-10 rounded-[5px] bg-white object-cover',
                                        'ml-[-6px] w-13! object-contain' => $method['class'] === 'sepay',
                                        'border border-[#e7eaf0] object-contain' => in_array($method['class'], ['atm',
                                        'card'], true),
                                        'object-[center_31%]' => $method['class'] === 'vnpay'])
                                    src="{{ asset($method['image']) }}" alt="" aria-hidden="true">
                                <span class="grid min-w-0 content-center gap-0.5">
                                    <strong class="text-base leading-[1.3] font-semibold text-gray-900">{{ $method['name'] }}</strong>
                                    @if ($method['has_note'] ?? false)
                                        <small class="text-xs leading-[1.25] font-normal text-futa-orange">{{ __('Payment::payment.payment_unsupported') }}</small>
                                    @endif
                                </span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </section>

            @include('Payment::partials.payment-preview-qr')

            <aside class="payment-page__aside">
                <section class="payment-page__summary-card">
                    <h2>{{ __('Payment::payment.payment_customer_title') }}</h2>
                    <dl>
                        <div><dt>{{ __('core::booking.full_name') }}</dt><dd>{{ $preview['customer']['name'] }}</dd></div>
                        <div><dt>{{ __('core::booking.phone') }}</dt><dd>{{ $preview['customer']['phone'] }}</dd></div>
                        <div><dt>{{ __('core::booking.email') }}</dt><dd>{{ $preview['customer']['email'] }}</dd></div>
                    </dl>
                </section>
                <section class="payment-page__summary-card">
                    <div class="payment-page__card-heading">
                        <h2>{{ __('core::booking.trip_info') }}</h2>
                        @include('Payment::partials.payment-preview-trip-tooltip')
                        <button type="button" class="payment-page__detail-link" x-ref="tripDetailTrigger" @click="openTripDetail()">
                            {{ __('core::booking.detail') }}
                        </button>
                    </div>
                    @include('Payment::partials.payment-preview-trip-summary')
                </section>
                <section class="payment-page__summary-card">
                    <div class="payment-page__card-heading">
                        <h2>{{ __('core::booking.price_detail') }}</h2>
                        @include('Payment::partials.payment-preview-price-tooltip')
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
                    @include('Payment::partials.payment-preview-trip-summary')
                </div>
            </div>
        </div>
        @include('core::partials.home.footer')
    </div>
@endsection

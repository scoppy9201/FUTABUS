    @php
        $trip = $preview['trip'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $total = count($preview['seats']) * $trip['fare']
            + count($preview['outbound_seats'] ?? []) * ($preview['outbound_trip']['fare'] ?? 0);
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
    <div
        class="payment-page"
        x-init="$nextTick(() => startPaymentClock())"
        x-data="staffPayment(@js(['remaining' => max(0,
            \Illuminate\Support\Carbon::parse($expiresAt)->timestamp - now()->timestamp),
            'enabled' => $paymentEnabled,
            'statusUrl' => route('api.v1.staff.ticket-sales.show',
            ['sale' => $preview['token']]),
            'cashUrl' => route('api.v1.staff.ticket-sales.cash',
            ['sale' => $preview['token']]),
            'paymentUrl' => route('staff.tickets.payment',
            ['sale' => $preview['token']]),
            'backUrl' => route('staff.tickets.sale',
            $preview['criteria']),
            'csrf' => csrf_token(),
            'error' => __('StaffTickets::sale.error')]))"
        @submit.window="if ($event.target.matches('[data-cash-payment]') && !$event.defaultPrevented) { $event.preventDefault(); collectCash(); }">
        @include('StaffTickets::partials.route-heading', ['routeTrip' => $preview['outbound_trip'] ?? $trip, 'paymentHeading' => true, 'secondDeparture' => isset($preview['outbound_trip']) ? $departure : null])

        <main class="payment-page__layout">
            <section class="payment-page__methods" aria-labelledby="payment-methods-title">
                <h2 id="payment-methods-title" class="mb-5 text-[21px] font-semibold text-gray-900">{{ __('Payment::payment.payment_methods_title') }}</h2>
                <div class="grid gap-1">
                    <button
                        type="button"
                        class="payment-page__method group grid min-h-15.75 w-full grid-cols-[24px_40px_minmax(0,1fr)] items-center gap-4 rounded-lg py-2 text-left hover:bg-futa-orange-soft"
                        :class="{ 'is-active': paymentMethod === 'cash' }"
                        :aria-pressed="paymentMethod === 'cash'"
                        @click="paymentMethod = 'cash'">
                        <span class="block size-4 rounded-full border border-slate-300 group-[.is-active]:border-futa-orange group-[.is-active]:bg-futa-orange group-[.is-active]:shadow-[inset_0_0_0_4px_#fff]" aria-hidden="true"></span>
                        <span class="grid size-10 place-items-center rounded-lg bg-green-50 text-futa-green"><x-heroicon-o-banknotes class="size-7" /></span>
                        <strong class="text-base font-semibold text-gray-900">{{ __('StaffTickets::sale.cash') }}</strong>
                    </button>
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
                                        <small class="text-xs leading-tight font-normal text-futa-orange">{{ __('Payment::payment.payment_unsupported') }}</small>
                                    @endif
                                </span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </section>

            <div class="min-w-0">
                <div x-show="paymentMethod !== 'cash'">@include('Payment::partials.payment-preview-qr')</div>
                <section x-show="paymentMethod === 'cash'" x-cloak class="grid justify-items-center gap-4 text-center">
                    <p class="text-lg font-medium text-slate-500">{{ __('Payment::payment.payment_total_label') }}</p>
                    <h2 class="text-4xl font-bold text-futa-orange">{{ $formattedTotal }}</h2>
                    <div class="w-full rounded-2xl bg-green-50 p-6">
                        <x-heroicon-o-banknotes class="mx-auto mb-4 size-16 text-futa-green" />
                        <h3 class="text-xl font-bold text-futa-green">{{ __('StaffTickets::sale.cash') }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('StaffTickets::sale.cash_note') }}</p>
                        <p class="my-4 text-sm text-futa-orange"><span>{{ __('Payment::payment.payment_preview_time') }}</span> <strong x-text="countdownText()"></strong></p>
                        <form
                            method="POST"
                            action="{{ route('api.v1.staff.ticket-sales.cash', ['sale' => $preview['token']]) }}" data-cash-payment data-confirm
                            data-confirm-title="{{ __('StaffTickets::sale.cash_confirm_title') }}"
                            data-confirm-message="{{ __('StaffTickets::sale.cash_confirm_message', ['amount' => $formattedTotal]) }}">
                            @csrf
                            <input type="hidden" name="received" value="1">
                            <button type="submit" :disabled="collectingCash || remainingSeconds <= 0" class="rounded-lg bg-futa-orange px-5 py-3 font-semibold text-white disabled:opacity-50">{{ __('StaffTickets::sale.cash_confirm_title') }}</button>
                        </form>
                    </div>
                </section>
            </div>

            <aside class="payment-page__aside">
                <section class="payment-page__summary-card">
                    <h2>{{ __('Payment::payment.payment_customer_title') }}</h2>
                    <dl>
                        <div><dt>{{ __('core::booking.full_name') }}</dt><dd>{{ $preview['customer']['name'] }}</dd></div>
                        <div><dt>{{ __('core::booking.phone') }}</dt><dd>{{ $preview['customer']['phone'] }}</dd></div>
                        <div><dt>{{ __('core::booking.email') }}</dt><dd>{{ $preview['customer']['email'] }}</dd></div>
                    </dl>
                </section>
                @if (isset($preview['outbound_trip']))
                    <section class="payment-page__summary-card">
                        <h2>{{ __('core::booking.outbound_leg') }}</h2>
                        @include('Payment::partials.payment-preview-trip-summary', ['summaryLeg' => 'outbound'])
                    </section>
                @endif
                <section class="payment-page__summary-card">
                    <div class="payment-page__card-heading">
                        <h2>{{ __('core::booking.trip_info') }}@if (isset($preview['outbound_trip'])) · {{ __('core::booking.return_leg') }}@endif</h2>
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
                        @if (isset($preview['outbound_trip']))
                            <div><dt>{{ __('core::booking.outbound_fare') }}</dt><dd>{{ number_format(count($preview['outbound_seats']) * $preview['outbound_trip']['fare'], 0, ',', '.') }}đ</dd></div>
                            <div><dt>{{ __('core::booking.return_fare') }}</dt><dd>{{ number_format(count($preview['seats']) * $trip['fare'], 0, ',', '.') }}đ</dd></div>
                        @else
                            <div><dt>{{ __('core::booking.fare') }}</dt><dd>{{ $formattedTotal }}</dd></div>
                        @endif
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
                    <h2 id="payment-trip-detail-title">{{ __('core::booking.trip_details_title', ['count' => isset($preview['outbound_trip']) ? 2 : 1]) }}</h2>
                    <button type="button" class="booking-page__modal-close" x-ref="tripDetailClose" @click="closeTripDetail()"
                        aria-label="{{ __('core::booking.close') }}">
                        <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                    </button>
                </div>
                <div class="payment-page__modal-content">
                    @if (isset($preview['outbound_trip']))
                        @include('Payment::partials.payment-preview-trip-summary', ['summaryLeg' => 'outbound'])
                    @endif
                    @include('Payment::partials.payment-preview-trip-summary')
                </div>
            </div>
        </div>

    </div>

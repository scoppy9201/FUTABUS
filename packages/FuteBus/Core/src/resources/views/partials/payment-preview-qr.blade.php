<section class="payment-page__qr" aria-labelledby="payment-qr-title">
    <p class="payment-page__total-label">{{ __('core::booking.payment_total_label') }}</p>
    <h2 id="payment-qr-title" class="payment-page__total">{{ $formattedTotal }}</h2>
    <div class="payment-page__qr-card">
        <p class="payment-page__preview-time" x-show="remainingSeconds > 0">
            {{ __('core::booking.payment_preview_time') }}
            <strong x-text="countdownText()"></strong>
        </p>
        <div class="payment-page__qr-frame">
            <div x-show="remainingSeconds === 0" class="payment-page__qr-empty" role="status">
                <strong>{{ __('core::booking.payment_expired') }}</strong>
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'sepay'">
                @if ($sePayQrUrl)
                    <img class="payment-page__qr-image" src="{{ $sePayQrUrl }}"
                        alt="{{ __('core::booking.payment_sepay_qr_alt') }}">
                @else
                    <div class="payment-page__qr-empty" role="status">
                        <img class="payment-page__sepay-logo" src="{{ asset('images/sepay.png') }}" alt="" aria-hidden="true">
                        <strong>{{ __('core::booking.payment_sepay_unavailable') }}</strong>
                    </div>
                @endif
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'futapay'" x-cloak>
                <div class="payment-page__qr-empty" role="status">
                    <img src="{{ asset('images/auth/White Brushstroke F on Forest Green.png') }}"
                        alt="" aria-hidden="true">
                    <strong>{{ __('core::booking.payment_futapay_unavailable') }}</strong>
                </div>
            </div>
        </div>
        <div x-show="paymentMethod === 'sepay'">
            <h3>{{ __('core::booking.payment_guide_title') }}</h3>
            <ol>
                @foreach (__('core::booking.payment_guide_steps') as $step)
                    <li>
                        <span class="payment-page__qr-step-text">
                            @if ($loop->iteration === 2)
                                {{ __('core::booking.payment_scan_before') }}
                                <img class="payment-page__scan-icon" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
                                {{ __('core::booking.payment_scan_after') }}
                            @else
                                {{ $step }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
        <div x-show="paymentMethod === 'futapay'" x-cloak>
            <h3>{{ __('core::booking.payment_futapay_guide_title') }}</h3>
            <ol>
                @foreach (__('core::booking.payment_futapay_guide_steps') as $step)
                    <li>
                        <span class="payment-page__qr-step-text">
                            @if ($loop->iteration === 2)
                                {{ __('core::booking.payment_scan_before') }}
                                <img class="payment-page__scan-icon" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
                                {{ __('core::booking.payment_scan_after') }}
                            @else
                                {{ $step }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

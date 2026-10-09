<section class="payment-page__qr" aria-labelledby="payment-qr-title">
    <p class="payment-page__total-label">{{ __('Payment::payment.payment_total_label') }}</p>
    <h2 id="payment-qr-title" class="payment-page__total">{{ $formattedTotal }}</h2>
    <div class="payment-page__qr-card">
        <p class="payment-page__preview-time" x-show="remainingSeconds > 0">
            {{ __($paymentEnabled ? 'Payment::payment.payment_hold_time' : 'Payment::payment.payment_preview_time') }}
            <strong x-text="countdownText()"></strong>
        </p>
        <div class="payment-page__qr-frame">
            <div x-show="remainingSeconds === 0" class="payment-page__qr-empty" role="status">
                <strong>{{ __('Payment::payment.payment_expired') }}</strong>
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'sepay'">
                @if ($sePayQrUrl)
                    <img class="payment-page__qr-image" src="{{ $sePayQrUrl }}"
                        alt="{{ __('Payment::payment.payment_sepay_qr_alt') }}">
                @else
                    <div class="payment-page__qr-empty" role="status">
                        <img class="payment-page__sepay-logo" src="{{ asset('images/sepay.png') }}" alt="" aria-hidden="true">
                        <strong>{{ __($paymentCanActivate
                            ? 'Payment::payment.payment_create_qr_prompt'
                            : 'Payment::payment.payment_sepay_unavailable') }}</strong>
                        @if ($paymentCanActivate)
                            <form method="POST" action="{{ route('trip-payment-preview.activate', ['draft' => $preview['token']]) }}">
                                @csrf
                                <button class="payment-page__activate" type="submit">
                                    {{ __('Payment::payment.payment_create_qr') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'futapay'" x-cloak>
                <div class="payment-page__qr-empty" role="status">
                    <img src="{{ asset('images/auth/White Brushstroke F on Forest Green.png') }}"
                        alt="" aria-hidden="true">
                    <strong>{{ __('Payment::payment.payment_futapay_unavailable') }}</strong>
                </div>
            </div>
        </div>
        <div x-show="paymentMethod === 'sepay'">
            <h3>{{ __('Payment::payment.payment_guide_title') }}</h3>
            <ol>
                @foreach (__('Payment::payment.payment_guide_steps') as $step)
                    <li>
                        <span class="payment-page__qr-step-text">
                            @if ($loop->iteration === 2)
                                {{ __('Payment::payment.payment_scan_before') }}
                                <img class="payment-page__scan-icon" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
                                {{ __('Payment::payment.payment_scan_after') }}
                            @else
                                {{ $step }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
        <div x-show="paymentMethod === 'futapay'" x-cloak>
            <h3>{{ __('Payment::payment.payment_futapay_guide_title') }}</h3>
            <ol>
                @foreach (__('Payment::payment.payment_futapay_guide_steps') as $step)
                    <li>
                        <span class="payment-page__qr-step-text">
                            @if ($loop->iteration === 2)
                                {{ __('Payment::payment.payment_scan_before') }}
                                <img class="payment-page__scan-icon" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
                                {{ __('Payment::payment.payment_scan_after') }}
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

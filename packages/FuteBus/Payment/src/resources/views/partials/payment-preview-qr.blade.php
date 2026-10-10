<section class="grid min-w-0 content-start justify-items-center text-center" aria-labelledby="payment-qr-title">
    <p class="text-lg font-medium text-slate-500">{{ __('Payment::payment.payment_total_label') }}</p>
    <h2 id="payment-qr-title" class="mb-5 text-[clamp(42px,3.2vw,54px)] leading-[1.12] font-bold text-futa-orange sm:whitespace-nowrap">{{ $formattedTotal }}</h2>
    <div class="w-full max-w-100 rounded-2xl bg-[#f9f9fb] px-4 pt-5 pb-6 text-left">
        <p class="mb-4.5 text-center text-[15px] text-[#e59200]" x-show="remainingSeconds > 0">
            {{ __($paymentEnabled ? 'Payment::payment.payment_hold_time' : 'Payment::payment.payment_preview_time') }}
            <strong class="font-bold" x-text="countdownText()"></strong>
        </p>
        <div class="mb-4 rounded-[10px] bg-white p-3.5 sm:min-h-86">
            <div x-show="remainingSeconds === 0" class="flex min-h-67.5 flex-col items-center justify-center gap-4.5 rounded-[9px] border border-dashed border-[#bbcad5] p-6.25 text-center text-[#39576d] sm:min-h-79" role="status">
                <strong class="max-w-60 text-[15px] leading-normal">{{ __('Payment::payment.payment_expired') }}</strong>
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'sepay'">
                @if ($sePayQrUrl)
                    <img class="mx-auto block h-auto min-h-67.5 w-full max-w-80 object-contain sm:h-79" src="{{ $sePayQrUrl }}"
                        alt="{{ __('Payment::payment.payment_sepay_qr_alt') }}">
                @else
                    <div class="flex min-h-67.5 flex-col items-center justify-center gap-4.5 rounded-[9px] border border-dashed border-[#bbcad5] p-6.25 text-center text-[#39576d] sm:min-h-79" role="status">
                        <img class="h-15 w-24 rounded-xl object-contain" src="{{ asset('images/sepay.png') }}" alt="" aria-hidden="true">
                        <strong class="max-w-60 text-[15px] leading-normal">{{ __($paymentCanActivate
                            ? 'Payment::payment.payment_create_qr_prompt'
                            : 'Payment::payment.payment_sepay_unavailable') }}</strong>
                        @if ($paymentCanActivate)
                            <form method="POST" action="{{ route('trip-payment-preview.activate', ['draft' => $preview['token']]) }}">
                                @csrf
                                <button class="cursor-pointer rounded-lg bg-futa-orange px-4.5 py-2.5 text-[15px] font-bold text-white hover:bg-futa-orange-dark focus-visible:bg-futa-orange-dark" type="submit">
                                    {{ __('Payment::payment.payment_create_qr') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
            <div x-show="remainingSeconds > 0 && paymentMethod === 'futapay'" x-cloak>
                <div class="flex min-h-67.5 flex-col items-center justify-center gap-4.5 rounded-[9px] border border-dashed border-[#bbcad5] p-6.25 text-center text-[#39576d] sm:min-h-79" role="status">
                    <img class="size-15 rounded-xl object-cover" src="{{ asset('images/auth/White Brushstroke F on Forest Green.png') }}"
                        alt="" aria-hidden="true">
                    <strong class="max-w-60 text-[15px] leading-normal">{{ __('Payment::payment.payment_futapay_unavailable') }}</strong>
                </div>
            </div>
        </div>
        <div x-show="paymentMethod === 'sepay'">
            <h3 class="mb-3.5 text-center text-[17px] font-semibold text-futa-green">{{ __('Payment::payment.payment_guide_title') }}</h3>
            <ol class="grid list-none gap-3 p-0">
                @foreach (__('Payment::payment.payment_guide_steps') as $step)
                    <li class="flex items-start gap-2.5 text-base leading-normal font-medium text-gray-900">
                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-gray-500 text-xs text-white">{{ $loop->iteration }}</span>
                        <span class="min-w-0">
                            @if ($loop->iteration === 2)
                                {{ __('Payment::payment.payment_scan_before') }}
                                <img class="mx-0.75 inline-block size-5 align-[-4px]" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
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
            <h3 class="mb-3.5 text-center text-[17px] font-semibold text-futa-green">{{ __('Payment::payment.payment_futapay_guide_title') }}</h3>
            <ol class="grid list-none gap-3 p-0">
                @foreach (__('Payment::payment.payment_futapay_guide_steps') as $step)
                    <li class="flex items-start gap-2.5 text-base leading-normal font-medium text-gray-900">
                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-gray-500 text-xs text-white">{{ $loop->iteration }}</span>
                        <span class="min-w-0">
                            @if ($loop->iteration === 2)
                                {{ __('Payment::payment.payment_scan_before') }}
                                <img class="mx-0.75 inline-block size-5 align-[-4px]" src="{{ asset('vendor/blade-heroicons/o-qr-code.svg') }}" alt="" aria-hidden="true">
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

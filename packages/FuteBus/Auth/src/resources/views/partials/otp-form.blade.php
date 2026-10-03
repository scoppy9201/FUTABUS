<form
    @unless($registration ?? false) x-cloak x-show="step === 'otp'" @endunless
    class="flex flex-col pt-5 sm:pt-4"
    action="{{ ($registration ?? false) ? route('register.email.verify') : '#' }}"
    method="post"
    @if($registration ?? false) @submit="if (!submitOtp()) $event.preventDefault()" @else @submit.prevent="submitOtp" @endif
>
    @csrf
    <p class="text-center text-[13px] leading-5 text-gray-900">
        {{ __('Auth::app.otp.sent_prefix') }}
        <strong class="font-medium text-[#007b59]" x-text="email"></strong>
    </p>

    <div class="mt-6 flex justify-center gap-3 sm:gap-4" @paste="handleOtpPaste">
        <template x-for="(_, index) in otp" :key="index">
            <input
                class="size-10 rounded-md border border-[#ffab92] bg-[#fff7f5] text-center text-lg font-semibold text-gray-900 outline-none transition focus:border-[#ef5222] focus:ring-3 focus:ring-[#ef5222]/10"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="1"
                data-otp-input
                :aria-label="@js(__('Auth::app.otp.digit')) + ' ' + (index + 1)"
                x-model="otp[index]"
                @input="handleOtpInput(index, $event)"
                @keydown="handleOtpKeydown(index, $event)"
            >
        </template>
    </div>
    @if($registration ?? false)<input type="hidden" name="otp" :value="otp.join('')">@endif

    <button type="submit" class="mt-12 h-11 rounded-full bg-[#ef5222] text-sm font-bold text-white transition hover:bg-[#d94317] active:scale-[.99]">
        {{ __('Auth::app.otp.continue') }}
    </button>

    <p class="mt-6 flex min-h-5 items-center justify-center gap-1 text-center text-[13px] text-[#8d8d9b]">
        <span>{{ __('Auth::app.otp.countdown') }}:</span>
        <strong x-show="secondsRemaining > 0" class="font-semibold text-[#25324b]" x-text="formattedCountdown()">01:57</strong>
        <button
            x-cloak
            x-show="{{ ($registration ?? false) ? 'resendRemaining === 0' : 'secondsRemaining === 0' }}"
            type="button"
            class="font-semibold text-[#ef5222] transition hover:text-[#d94317] hover:underline"
            @click="{{ ($registration ?? false) ? '$refs.resendForm.submit()' : 'resendOtp()' }}"
        >{{ __('Auth::app.otp.resend') }}</button>
    </p>
</form>
@if($registration ?? false)
    <form x-ref="resendForm" action="{{ route('register.email.resend') }}" method="post">@csrf</form>
@endif

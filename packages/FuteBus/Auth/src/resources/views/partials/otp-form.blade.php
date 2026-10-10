<form class="flex flex-col pt-5 sm:pt-4" action="{{ $otpAction }}" method="post" @submit="if (!submitOtp()) $event.preventDefault()">
    @csrf
    <p class="text-center text-[13px] leading-5 text-gray-900">
        {{ $sentPrefix }} <strong class="font-medium text-[#007b59]">{{ $sentTo }}</strong>
    </p>

    <div class="mt-6 flex justify-center gap-3 sm:gap-4" @paste="handleOtpPaste">
        <template x-for="(_, index) in otp" :key="index">
            <input
                class="size-10 rounded-md border border-futa-orange/40 bg-futa-orange-soft text-center text-lg font-semibold text-gray-900 outline-none transition focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10"
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
    <input type="hidden" name="otp" :value="otp.join('')">

    <button type="submit" class="mt-12 h-11 rounded-full bg-futa-orange text-sm font-bold text-white transition hover:bg-futa-orange-dark active:scale-[.99]">
        {{ __('Auth::app.otp.continue') }}
    </button>

    <p class="mt-6 flex min-h-5 items-center justify-center gap-1 text-center text-[13px] text-[#8d8d9b]">
        <span>{{ __('Auth::app.otp.countdown') }}:</span>
        <strong x-show="secondsRemaining > 0" class="font-semibold text-[#25324b]" x-text="formattedCountdown()">00:00</strong>
        <button
            x-cloak
            x-show="resendRemaining === 0"
            type="button"
            class="font-semibold text-futa-orange transition hover:text-futa-orange-dark hover:underline"
            @click="$refs.resendForm.submit()"
        >{{ __('Auth::app.otp.resend') }}</button>
    </p>
</form>
<form x-ref="resendForm" action="{{ $resendAction }}" method="post">@csrf</form>

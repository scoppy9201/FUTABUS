@extends('Auth::layouts.auth')

@section('title', __('Auth::app.forgot_password.page_title'))

@section('form')
    <div x-data="authOtp(@js($otpExpiresAt), @js($otpResendAt))">
        <h1 id="auth-title" class="mb-8 text-center text-[22px] leading-tight font-semibold sm:text-[25px]">
            {{ match ($step) {
                'otp' => __('Auth::app.password_recovery.otp_heading'),
                'password' => __('Auth::app.password_recovery.password_heading'),
                default => __('Auth::app.forgot_password.heading'),
            } }}
        </h1>

        @if(session('status'))
            <p role="status" class="mb-5 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <div role="alert" class="mb-5 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if($step === 'email')
            <p class="mb-6 text-center text-sm text-gray-700">{{ __('Auth::app.password_recovery.intro') }}</p>
            <form class="flex flex-col gap-6" action="{{ route('password.email') }}" method="post">
                @csrf
                @include('Auth::partials.email-field')
                <button type="submit" class="h-11 rounded-full bg-futa-orange text-sm font-bold text-white transition hover:bg-futa-orange-dark">
                    {{ __('Auth::app.forgot_password.send_code') }}
                </button>
                <a href="{{ route('login') }}" class="-mt-3 self-end text-[13px] font-medium text-gray-900 transition hover:text-futa-orange">
                    {{ __('Auth::app.forgot_password.back') }}
                </a>
            </form>
        @elseif($step === 'otp')
            @include('Auth::partials.otp-form', [
                'otpAction' => route('password.email.verify'),
                'resendAction' => route('password.email.resend'),
                'sentTo' => $recoveryEmail,
                'sentPrefix' => __('Auth::app.password_recovery.sent_to'),
            ])
        @else
            <p class="mb-5 text-center text-sm text-gray-700">{{ $recoveryEmail }}</p>
            <form class="flex flex-col gap-4" action="{{ route('password.update') }}" method="post">
                @csrf
                <label class="text-sm font-medium" for="new-password">{{ __('Auth::app.password_recovery.new_password') }}</label>
                <input id="new-password" class="h-10 rounded-md border border-futa-orange/40 bg-futa-orange-soft px-3" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label class="text-sm font-medium" for="confirm-new-password">{{ __('Auth::app.password_recovery.confirm_password') }}</label>
                <input id="confirm-new-password" class="h-10 rounded-md border border-futa-orange/40 bg-futa-orange-soft px-3" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                <button type="submit" class="mt-3 h-11 rounded-full bg-futa-orange text-sm font-bold text-white transition hover:bg-futa-orange-dark">
                    {{ __('Auth::app.password_recovery.reset_button') }}
                </button>
            </form>
        @endif
    </div>
@endsection

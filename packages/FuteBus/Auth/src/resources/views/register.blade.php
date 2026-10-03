@extends('Auth::layouts.auth')

@section('title', __('Auth::app.register.page_title'))

@section('form')
    <div x-data="authRegistration(@js($step), @js($otpExpiresAt), @js($otpResendAt))">
        <h1 id="auth-title" class="mb-4 text-center text-[22px] leading-tight font-semibold sm:mb-7 sm:text-[25px]">
            {{ match ($step) {
                'email_otp' => __('Auth::app.otp.heading'),
                'password' => __('Auth::app.registration_flow.password_heading'),
                'profile' => __('Auth::app.registration_flow.profile_heading'),
                default => __('Auth::app.register.heading'),
            } }}
        </h1>

        @include('Auth::partials.auth-tabs', ['active' => 'register'])

        @if(session('status'))
            <p role="status" class="mt-5 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <div role="alert" class="mt-5 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if($step === 'email')
            <form class="flex flex-col gap-6 pt-7 sm:gap-7.5 sm:pt-10" action="{{ route('register.email') }}" method="post">
                @csrf
                @include('Auth::partials.email-field')
                @include('Auth::partials.terms-consent')
                <button type="submit" class="h-11 rounded-full bg-[#ef5222] text-sm font-bold text-white transition hover:bg-[#d94317]">{{ __('Auth::app.register.submit') }}</button>
            </form>
        @elseif($step === 'email_otp')
            @include('Auth::partials.otp-form', ['registration' => true])
        @elseif($step === 'password')
            <p class="mt-5 text-center text-sm text-gray-700">{{ __('Auth::app.registration_flow.verified_email') }} <strong class="text-[#007b59]">{{ $registrationEmail }}</strong></p>
            <form class="flex flex-col gap-4 pt-6" action="{{ route('register.password') }}" method="post">
                @csrf
                <label class="text-sm font-medium" for="registration-password">{{ __('Auth::app.fields.password') }}</label>
                <input id="registration-password" class="h-10 rounded-md border border-[#ffab92] bg-[#fff7f5] px-3" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label class="text-sm font-medium" for="registration-password-confirmation">{{ __('Auth::app.registration_flow.password_confirmation') }}</label>
                <input id="registration-password-confirmation" class="h-10 rounded-md border border-[#ffab92] bg-[#fff7f5] px-3" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                <button type="submit" class="mt-3 h-11 rounded-full bg-[#ef5222] text-sm font-bold text-white transition hover:bg-[#d94317]">{{ __('Auth::app.otp.continue') }}</button>
            </form>
        @else
            <form class="flex flex-col gap-4 pt-6" action="{{ route('register.profile') }}" method="post">
                @csrf
                <label class="text-sm font-medium" for="registration-name">{{ __('Auth::app.registration_flow.name') }}</label>
                <input id="registration-name" class="h-10 rounded-md border border-[#ffab92] bg-[#fff7f5] px-3" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                <label class="text-sm font-medium" for="registration-phone">{{ __('Auth::app.registration_flow.phone') }}</label>
                <input id="registration-phone" class="h-10 rounded-md border border-[#ffab92] bg-[#fff7f5] px-3" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="{{ __('Auth::app.registration_flow.phone_example') }}" required>
                <button type="submit" class="mt-3 h-11 rounded-full bg-[#ef5222] text-sm font-bold text-white transition hover:bg-[#d94317]">{{ __('Auth::app.registration_flow.finish') }}</button>
            </form>
        @endif
    </div>
@endsection

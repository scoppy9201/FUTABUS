@extends('Auth::layouts.auth')

@section('title', __('Auth::app.login.page_title'))

@section('form')
    <div x-data="{ showPassword: false }">
        <h1 id="auth-title" class="mb-4 text-center text-[22px] leading-tight font-semibold sm:mb-7 sm:text-[25px]">{{ __('Auth::app.login.heading') }}</h1>

        @include('Auth::partials.auth-tabs', ['active' => 'login'])

        @if(session('status'))<p role="status" class="mt-5 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>@endif
        @if($errors->any())<p role="alert" class="mt-5 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>@endif
        <form class="flex flex-col gap-6 pt-7 sm:gap-7.5 sm:pt-10" action="{{ route('login.store') }}" method="post">
            @csrf
            @include('Auth::partials.email-field')

            <label class="flex h-10 items-center rounded-md border border-futa-orange/40 bg-futa-orange-soft text-[#999] transition focus-within:border-futa-orange focus-within:ring-3 focus-within:ring-futa-orange/10">
                <span class="sr-only">{{ __('Auth::app.fields.password') }}</span>
                <x-heroicon-o-lock-closed class="ml-3 size-5.25 shrink-0" />
                <input class="h-full min-w-0 flex-1 bg-transparent px-3 text-base text-gray-900 outline-none placeholder:text-[#b9b9b9]" :type="showPassword ? 'text' : 'password'" name="password" autocomplete="current-password" placeholder="{{ __('Auth::app.fields.password_placeholder') }}" required>
                <button type="button" class="grid h-full w-10.5 shrink-0 place-items-center text-[#999]" @click="showPassword = !showPassword" :aria-label="showPassword ? @js(__('Auth::app.password_visibility.hide')) : @js(__('Auth::app.password_visibility.show'))">
                    <x-heroicon-o-eye-slash x-show="!showPassword" class="size-5" />
                    <x-heroicon-o-eye x-show="showPassword" class="size-5" style="display: none" />
                </button>
            </label>

            <button type="submit" class="h-11 rounded-full bg-futa-orange text-sm font-bold text-white transition hover:bg-futa-orange-dark active:scale-[.99]">{{ __('Auth::app.login.submit') }}</button>
            <a href="{{ route('password.request') }}" class="-mt-2 text-[13px] text-futa-orange">{{ __('Auth::app.login.forgot_password') }}</a>
        </form>
    </div>
@endsection

@extends('core::layouts.home')

@section('title', __('Profile::tickets.detail_title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-282 px-4 py-10 sm:px-6 lg:px-0">
            <a href="{{ route('profile.tickets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline">
                <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
                {{ __('Profile::tickets.back_to_history') }}
            </a>
            <div class="mx-auto mt-5 max-w-120 overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                @include('Profile::partials.ticket-journey')
            </div>
        </main>

        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection

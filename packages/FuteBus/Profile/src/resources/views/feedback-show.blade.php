@extends('core::layouts.home')

@section('title', __('Profile::feedback.detail_title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')
        <main class="mx-auto grid w-full max-w-282 items-start gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-0">
            @include('Profile::partials.account-sidebar')
            <section class="min-w-0">
                <a href="{{ route('profile.feedback.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline">
                    <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />{{ __('Profile::feedback.back') }}
                </a>
                <h1 class="mt-4 text-3xl font-semibold text-gray-950">{{ __('Profile::feedback.detail_title') }}</h1>
                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    @include('Profile::partials.feedback-detail')
                </div>
            </section>
        </main>
        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection

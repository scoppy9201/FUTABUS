@extends('core::layouts.home')

@section('title', __('core::app.about.title'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-11 sm:px-6 lg:px-0">
            <article class="mx-auto max-w-285 text-gray-950">
                <header class="text-center">
                    <h1 class="text-[36px] font-extrabold uppercase leading-tight text-[#ef5222]">
                        {{ __('core::app.about.heading') }}
                    </h1>
                    <p class="mt-3 text-2xl font-extrabold">
                        “{{ __('core::app.about.slogan') }}”
                    </p>
                </header>

                <div class="mt-6 space-y-4 text-base font-semibold leading-6.5">
                    <p>{{ __('core::app.about.introduction') }}</p>
                    <p>{{ __('core::app.about.history') }}</p>
                </div>

                <button
                    type="button"
                    class="mx-auto mt-12 flex items-center gap-2 text-base font-semibold text-gray-400 transition-colors hover:text-[#ef5222]"
                >
                    <span>{{ __('core::app.about.read_more') }}</span>
                    <x-heroicon-o-chevron-down class="size-5" />
                </button>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

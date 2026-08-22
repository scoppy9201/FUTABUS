@extends('core::layouts.home')

@section('title', __('core::refund.meta.title'))
@section('meta_description', __('core::refund.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-base font-semibold leading-6.5 text-gray-950">
                <header class="mb-7 text-center">
                    <p class="text-[34px] font-extrabold uppercase leading-tight text-[#ef5222]">
                        {{ __('core::refund.brand') }}
                    </p>
                    <h1 class="mt-3 text-[30px] font-extrabold uppercase leading-tight">
                        {{ __('core::refund.heading') }}
                    </h1>
                </header>

                <section>
                    <h2 class="text-xl font-extrabold">{{ __('core::refund.transaction_errors.title') }}</h2>
                    <p class="mt-4">{{ __('core::refund.transaction_errors.introduction') }}</p>
                    <ul class="mt-4 list-disc space-y-4 pl-5">
                        @foreach(__('core::refund.transaction_errors.items') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="mt-7">
                    <h2 class="text-xl font-extrabold">{{ __('core::refund.processing.title') }}</h2>
                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-[140px_1fr]">
                        @foreach(__('core::refund.processing.channels') as $channel)
                            <dt class="font-extrabold">{{ $channel['name'] }}</dt>
                            <dd>{{ $channel['time'] }}</dd>
                        @endforeach
                    </dl>
                </section>

                <section class="mt-7">
                    <h2 class="text-xl font-extrabold">{{ __('core::refund.changes.title') }}</h2>
                    <ul class="mt-4 list-disc space-y-4 pl-5">
                        @foreach(__('core::refund.changes.items') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

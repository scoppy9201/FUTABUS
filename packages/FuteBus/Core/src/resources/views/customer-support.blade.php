@extends('core::layouts.home')

@section('title', __('core::customer-support.meta.title'))
@section('meta_description', __('core::customer-support.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-9 sm:px-6 lg:px-0">
            <article class="text-base font-medium leading-7 text-gray-950">
                <header class="text-center">
                    <p class="text-[30px] font-extrabold uppercase leading-tight text-futa-orange sm:text-[36px]">
                        {{ __('core::customer-support.brand') }}
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold uppercase leading-tight sm:text-[30px]">
                        {{ __('core::customer-support.heading') }}
                    </h1>
                    <p class="mt-4 font-extrabold uppercase">{{ __('core::customer-support.slogan') }}</p>
                </header>

                <div class="mt-5 space-y-4">
                    @foreach(__('core::customer-support.introduction') as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <p class="mt-5 font-bold italic">{{ __('core::customer-support.channels_heading') }}</p>

                <ul class="mt-4 list-disc space-y-3 pl-5">
                    @foreach(__('core::customer-support.channels') as $channel)
                        <li>
                            {{ $channel['label'] }}:
                            <a
                                href="{{ $channel['route'] ? route($channel['route']) : '#' }}"
                                class="text-futa-orange hover:underline"
                            >
                                {{ $channel['link'] }}
                            </a>
                        </li>
                    @endforeach

                    <li>
                        {{ __('core::customer-support.questions.label') }}
                        <ul class="mt-3 list-disc space-y-3 pl-5">
                            <li>
                                <a href="#" class="text-futa-orange hover:underline">
                                    {{ __('core::customer-support.questions.form_link') }}
                                </a>.
                                {{ __('core::customer-support.questions.form_description') }}
                            </li>
                            <li>{{ __('core::customer-support.questions.chat') }}</li>
                        </ul>
                    </li>
                </ul>

                <p class="mt-4 font-bold italic">
                    {{ __('core::customer-support.hotline.prefix') }}
                    <a href="tel:19006067" class="font-extrabold text-futa-orange hover:underline">1900 6067</a>.
                </p>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

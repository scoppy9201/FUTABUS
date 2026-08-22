@extends('core::layouts.home')

@section('title', __('core::transaction-conditions.meta.title'))
@section('meta_description', __('core::transaction-conditions.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-[15px] font-semibold leading-7 text-gray-950 sm:text-base">
                <header class="mb-7 text-center">
                    <p class="text-[30px] font-extrabold uppercase leading-tight text-[#ef5222] sm:text-[34px]">
                        {{ __('core::transaction-conditions.brand') }}
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold uppercase leading-tight sm:text-[30px]">
                        {{ __('core::transaction-conditions.heading') }}
                    </h1>
                </header>

                <section>
                    <h2 class="text-xl font-extrabold">{{ __('core::transaction-conditions.article.title') }}</h2>
                    <p class="mt-4 font-extrabold">{{ __('core::transaction-conditions.article.introduction') }}</p>

                    <div class="mt-4 space-y-4">
                        @foreach(__('core::transaction-conditions.article.notes') as $note)
                            <p>
                                <strong>(*)</strong>
                                {{ $note }}
                            </p>
                        @endforeach
                    </div>

                    <h3 class="mt-5 font-extrabold italic">
                        {{ __('core::transaction-conditions.article.customer_rights_title') }}
                    </h3>
                    <ul class="mt-4 list-disc space-y-4 pl-5">
                        @foreach(__('core::transaction-conditions.article.customer_rights') as $item)
                            <li>
                                @if(is_array($item))
                                    @foreach($item as $part)
                                        @if(($part['type'] ?? 'text') === 'phone')
                                            <a href="tel:{{ $part['value'] }}"
                                                class="font-extrabold text-[#ef5222] hover:underline">
                                                {{ $part['text'] }}
                                            </a>
                                        @elseif(($part['type'] ?? 'text') === 'highlight')
                                            <strong class="text-[#ef5222]">{{ $part['text'] }}</strong>
                                        @else
                                            {{ $part['text'] }}
                                        @endif
                                    @endforeach
                                @else
                                    {{ $item }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

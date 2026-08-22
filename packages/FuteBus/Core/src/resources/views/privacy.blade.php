@extends('core::layouts.home')

@section('title', __('core::privacy.meta.title'))
@section('meta_description', __('core::privacy.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-base font-semibold leading-6.5 text-gray-950">
                <header class="mb-7 text-center">
                    <p class="text-[34px] font-extrabold uppercase leading-tight text-[#ef5222]">
                        {{ __('core::privacy.brand') }}
                    </p>
                    <h1 class="mt-3 text-[30px] font-extrabold uppercase leading-tight">
                        {{ __('core::privacy.heading') }}
                    </h1>
                </header>

                <section>
                    <h2 class="text-xl font-extrabold">{{ __('core::privacy.general.title') }}</h2>
                    <ol class="mt-4 list-decimal space-y-4 pl-5 marker:font-extrabold">
                        @foreach(__('core::privacy.general.items') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </section>

                <section class="mt-7">
                    <h2 class="text-xl font-extrabold">{{ __('core::privacy.policy.title') }}</h2>

                    <section class="mt-5">
                        <h3 class="font-extrabold italic">{{ __('core::privacy.policy.collection.title') }}</h3>
                        <p class="mt-2">{{ __('core::privacy.policy.collection.introduction') }}</p>
                        <ul class="mt-4 list-disc space-y-4 pl-5">
                            @foreach(__('core::privacy.policy.collection.items') as $item)
                                <li><strong>{{ $item['label'] }}</strong> {{ $item['text'] }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.purposes.title') }}</h3>
                        <p class="mt-2">{{ __('core::privacy.policy.purposes.introduction') }}</p>
                        <ul class="mt-4 list-disc space-y-2 pl-5">
                            @foreach(__('core::privacy.policy.purposes.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.sharing.title') }}</h3>
                        <p class="mt-2">{{ __('core::privacy.policy.sharing.introduction') }}</p>
                        <ul class="mt-4 list-disc space-y-4 pl-5">
                            @foreach(__('core::privacy.policy.sharing.items') as $item)
                                <li><strong>{{ $item['label'] }}</strong> {{ $item['text'] }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.security.title') }}</h3>
                        @foreach(__('core::privacy.policy.security.groups') as $group)
                            <h4 class="mt-3 font-extrabold">{{ $group['title'] }}</h4>
                            <ul class="mt-3 list-disc space-y-2 pl-5">
                                @foreach($group['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endforeach
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.retention.title') }}</h3>
                        @foreach(__('core::privacy.policy.retention.paragraphs') as $paragraph)
                            <p class="mt-2">{{ $paragraph }}</p>
                        @endforeach
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.rights.title') }}</h3>
                        <p class="mt-2">{{ __('core::privacy.policy.rights.introduction') }}</p>
                        <ul class="mt-4 list-disc space-y-4 pl-5">
                            @foreach(__('core::privacy.policy.rights.items') as $item)
                                <li><strong>{{ $item['label'] }}</strong> {{ $item['text'] }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.membership.title') }}</h3>
                        <p class="mt-2">{{ __('core::privacy.policy.membership.introduction') }}</p>
                        <ul class="mt-3 list-disc space-y-2 pl-5">
                            @foreach(__('core::privacy.policy.membership.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="mt-6">
                        <h3 class="font-extrabold">{{ __('core::privacy.policy.contact.title') }}</h3>
                        <p class="mt-2">
                            {{ __('core::privacy.policy.contact.prefix') }}
                            <a href="mailto:hotro@futabus.vn" class="text-[#ef5222] hover:underline">hotro@futabus.vn</a>
                            {{ __('core::privacy.policy.contact.or') }}
                            <a href="tel:19006067" class="text-[#ef5222] hover:underline">1900 6067</a>.
                        </p>
                    </section>
                </section>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

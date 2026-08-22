@extends('core::layouts.home')

@section('title', __('core::app.about.title'))

@section('content')
    @php
        $aboutSections = [
            ['futabus', 'images/about/futabus-fleet.png', 'left'],
            ['land', 'images/about/futa-land-da-nang.png', 'right'],
            ['express', 'images/about/futa-express-delivery.png', 'left'],
            ['city_bus', 'images/about/futa-city-bus-fleet.png', 'right'],
            ['advertising', 'images/about/futa-advertising.png', 'left'],
            ['rest_stop', 'images/about/phuc-loc-rest-stop.png', 'right'],
            ['application', 'images/about/futa-application.png', 'left'],
        ];
    @endphp

    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main
            class="mx-auto w-full max-w-285 px-4 py-11 sm:px-6 lg:px-0"
            x-data="{
                expanded: false,
                collapse() {
                    this.$refs.aboutTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(() => { this.expanded = false }, 500);
                }
            }"
        >
            <article x-ref="aboutTop" class="mx-auto scroll-mt-4 text-gray-950">
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
                    x-show="!expanded"
                    @click="expanded = true"
                    class="mx-auto mt-12 flex items-center gap-2 text-base font-semibold text-gray-400 transition-colors hover:text-[#ef5222]"
                >
                    <span>{{ __('core::app.about.read_more') }}</span>
                    <x-heroicon-o-chevron-down class="size-5" />
                </button>

                <div
                    class="grid transition-[grid-template-rows] duration-500 ease-in-out"
                    :class="expanded ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    :aria-hidden="(!expanded).toString()"
                >
                    <div class="min-h-0 overflow-hidden">
                        <div
                            class="space-y-20 pt-14 transition duration-500 ease-out"
                            :class="expanded ? 'translate-y-0 opacity-100' : '-translate-y-3 opacity-0'"
                        >
                            @foreach($aboutSections as [$key, $image, $imagePosition])
                                <section class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12">
                                    <div class="{{ $imagePosition === 'right' ? 'lg:order-2' : '' }}">
                                        <img
                                            src="{{ asset($image) }}"
                                            alt="{{ __('core::app.about.sections.' . $key . '.image_alt') }}"
                                            class="mx-auto max-h-90 w-full rounded-xl object-contain"
                                            loading="lazy"
                                        >
                                    </div>
                                    <div class="{{ $imagePosition === 'right' ? 'lg:order-1' : '' }}">
                                        <h2 class="text-[38px] font-extrabold uppercase leading-tight text-[#ef5222]">
                                            {{ __('core::app.about.sections.' . $key . '.title') }}
                                        </h2>
                                        <div class="mt-5 space-y-4 text-base font-semibold leading-6.5">
                                            @foreach(__('core::app.about.sections.' . $key . '.paragraphs') as $paragraph)
                                                <p>{{ $paragraph }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                </section>
                            @endforeach

                            <button
                                type="button"
                                @click="collapse()"
                                class="mx-auto flex items-center gap-2 text-base font-semibold text-gray-400 transition-colors hover:text-[#ef5222]"
                            >
                                <span>{{ __('core::app.about.show_less') }}</span>
                                <x-heroicon-o-chevron-up class="size-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

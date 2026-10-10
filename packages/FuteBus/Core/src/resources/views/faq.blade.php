@extends('core::layouts.home')

@section('title', __('core::faq.meta.title'))
@section('meta_description', __('core::faq.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="px-3 py-6 sm:px-6">
            <section class="mx-auto w-full max-w-282 rounded-2xl border border-slate-300 px-4 pb-10 pt-5 sm:px-6">
                <header class="text-center">
                    <h1 class="text-2xl font-extrabold uppercase leading-tight text-futa-orange sm:text-[27px]">
                        {{ __('core::faq.heading') }}
                    </h1>
                    <p class="mt-5 text-base font-semibold text-gray-950 sm:text-lg">
                        {{ __('core::faq.subtitle') }}
                    </p>
                </header>

                <img
                    src="{{ asset('images/faq/customer-support.png') }}"
                    alt="{{ __('core::faq.hero_alt') }}"
                    class="mx-auto mt-7 h-auto w-full max-w-155 object-contain sm:mt-9"
                >

                <h2 class="mt-8 text-center text-[28px] font-extrabold text-futa-green sm:text-[32px]">
                    {{ __('core::faq.popular_topics') }}
                </h2>

                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach($categories as $category)
                        <article
                            class="flex min-h-80 flex-col rounded-2xl bg-white px-7 pb-7 pt-8 shadow-[0_8px_30px_rgba(15,23,42,.10)]"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ asset($category->image) }}"
                                    alt=""
                                    class="size-12 shrink-0 object-contain"
                                    aria-hidden="true"
                                >
                                <h3 class="text-[22px] font-extrabold uppercase leading-tight text-gray-950 sm:text-2xl">
                                    {{ $category->localized('name') }}
                                </h3>
                            </div>
                            <p class="mt-5 text-center text-base font-semibold leading-7 text-gray-950 sm:text-lg">
                                {{ $category->localized('description') }}
                            </p>
                            <a
                                href="{{ route('faq-category', $category) }}"
                                class="mt-auto inline-flex h-12 w-full items-center justify-center rounded-full bg-futa-orange
                                    px-5 text-center text-base font-extrabold text-white shadow-sm transition duration-200
                                    hover:-translate-y-0.5 hover:bg-futa-orange hover:shadow-md focus:outline-none
                                    focus:ring-3 focus:ring-futa-orange/25"
                            >
                                {{ __('core::faq.view_all') }}
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

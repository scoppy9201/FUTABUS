@extends('core::layouts.home')

@section('title', __('core::pricing.meta.title'))
@section('meta_description', __('core::pricing.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="min-h-110 text-base font-semibold leading-6.5 text-gray-950">
                <header class="mb-7 text-center">
                    <p class="text-[34px] font-extrabold uppercase leading-tight text-[#ef5222]">
                        {{ __('core::pricing.brand') }}
                    </p>
                    <h1 class="mt-3 text-[30px] font-extrabold uppercase leading-tight">
                        {{ __('core::pricing.heading') }}
                    </h1>
                </header>

                @foreach(__('core::pricing.sections') as $section)
                    <section class="mt-7 first:mt-0">
                        <h2 class="text-xl font-extrabold">{{ $section['title'] }}</h2>
                        @if(isset($section['introduction']))
                            <p class="mt-3">{{ $section['introduction'] }}</p>
                        @endif
                        <ul class="mt-4 list-disc space-y-4 pl-5">
                            @foreach($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

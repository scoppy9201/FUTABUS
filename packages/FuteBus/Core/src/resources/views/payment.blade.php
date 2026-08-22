@extends('core::layouts.home')

@section('title', __('core::payment.meta.title'))
@section('meta_description', __('core::payment.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="min-h-110 text-base font-semibold leading-6.5 text-gray-950">
                <header class="mb-7 text-center">
                    <p class="text-[34px] font-extrabold uppercase leading-tight text-[#ef5222]">
                        {{ __('core::payment.brand') }}
                    </p>
                    <h1 class="mt-3 text-[30px] font-extrabold uppercase leading-tight">
                        {{ __('core::payment.heading') }}
                    </h1>
                </header>

                @foreach(__('core::payment.sections') as $section)
                    <section class="mt-7 first:mt-0">
                        <h2 class="text-xl font-extrabold">{{ $section['title'] }}</h2>
                        @if(isset($section['introduction']))
                            <p class="mt-3">{{ $section['introduction'] }}</p>
                        @endif
                        <ol class="mt-4 list-decimal space-y-4 pl-5 marker:font-extrabold">
                            @foreach($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    </section>
                @endforeach
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

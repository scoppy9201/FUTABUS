@extends('core::layouts.home')

@section('title', $promotion['title'].' - FUTA Bus Lines')

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-274 px-4 pt-9 pb-16 sm:px-5">
            <article>
                <h1 class="text-[23px] font-extrabold uppercase leading-[1.35] text-gray-950 sm:text-2xl">
                    {{ $promotion['title'] }}
                </h1>

                @if (!empty($promotion['published_at']))
                    <p class="mt-4 text-xs text-gray-400">Ngày đăng: {{ $promotion['published_at'] }}</p>
                @endif

                @if (!empty($promotion['intro']))
                    <p class="mt-6 text-base italic leading-6 text-gray-950">{{ $promotion['intro'] }}</p>
                @endif

                <img
                    src="{{ asset($promotion['image']) }}"
                    alt="{{ $promotion['title'] }}"
                    class="mx-auto mt-10 h-auto w-full max-w-262.5"
                >

                @if ($bodyHtml !== null)
                    {!! $bodyHtml !!}
                @endif
            </article>
            @include('core::partials.home.related-articles')
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

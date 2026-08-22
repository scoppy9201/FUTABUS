@extends('core::layouts.home')

@section('title', __('core::service-conditions.meta.title'))
@section('meta_description', __('core::service-conditions.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-[15px] font-semibold leading-7 text-gray-950 sm:text-base">
                <header class="mb-7 text-center">
                    <p class="text-[30px] font-extrabold uppercase leading-tight text-[#ef5222] sm:text-[34px]">
                        {{ __('core::service-conditions.brand') }}
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold uppercase leading-tight sm:text-[30px]">
                        {{ __('core::service-conditions.heading') }}
                    </h1>
                </header>

                @foreach(__('core::service-conditions.articles') as $article)
                    <section class="mt-6 first:mt-0">
                        <h2 class="text-xl font-extrabold">{{ $article['title'] }}</h2>
                        @if(isset($article['introduction']))
                            <p class="mt-4 font-extrabold">{{ $article['introduction'] }}</p>
                        @endif
                        <div class="mt-4 space-y-4">
                            @foreach($article['items'] as $item)
                                <p class="{{ $article['style'] === 'bullets' ? 'relative pl-4 before:absolute before:left-0 before:content-[\'•\']' : '' }}">
                                    @if($article['style'] === 'notes')
                                        <strong>(*)</strong>
                                    @endif
                                    {{ $item }}
                                </p>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </article>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

@extends('core::layouts.home')

@section('title', __('core::terms.meta.title'))
@section('meta_description', __('core::terms.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0">
            <article class="text-[15px] font-semibold leading-7 text-gray-950 sm:text-base">
                <header class="mb-8 text-center">
                    <p class="text-[30px] font-extrabold uppercase leading-tight text-[#ef5222] sm:text-[34px]">
                        {{ __('core::terms.brand') }}
                    </p>
                    <h1 class="mt-3 text-2xl font-extrabold uppercase leading-tight sm:text-[30px]">
                        {{ __('core::terms.heading') }}
                    </h1>
                </header>

                @foreach(__('core::terms.chapters') as $chapter)
                    <section class="mt-9 first:mt-0">
                        <h2 class="text-xl font-extrabold uppercase">{{ $chapter['title'] }}</h2>

                        @foreach($chapter['articles'] as $article)
                            <section class="mt-6">
                                <h3 class="text-lg font-extrabold">{{ $article['title'] }}</h3>
                                @if(isset($article['introduction']))
                                    <p class="mt-3">{{ $article['introduction'] }}</p>
                                @endif
                                <ol class="mt-4 space-y-4">
                                    @foreach($article['items'] as $item)
                                        <li class="flex gap-2">
                                            <span class="shrink-0 font-extrabold">{{ $loop->iteration }}.</span>
                                            <div>
                                                @if(isset($item['label']))
                                                    <strong>{{ $item['label'] }}</strong>
                                                @endif
                                                {{ $item['text'] }}
                                                @if(isset($item['details']))
                                                    <ul class="mt-2 list-disc space-y-2 pl-5">
                                                        @foreach($item['details'] as $detail)
                                                            <li>{{ $detail }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endforeach
                    </section>
                @endforeach

                <div class="mt-8">
                    <p>{{ __('core::terms.contact.prefix') }}</p>
                    <p>
                        <a class="font-extrabold italic" href="mailto:hotro@futabus.vn">hotro@futabus.vn</a>
                        {{ __('core::terms.contact.or') }}
                        <a class="font-extrabold italic" href="tel:19006067">1900 6067</a>.
                    </p>
                </div>
            </article>
        </main>

        <section class="pb-10 pt-7" aria-labelledby="terms-quality-heading">
            <div class="mx-auto w-full max-w-285 px-4 sm:px-6 lg:px-0">
                <div class="flex items-center gap-5">
                    <span class="h-px flex-1 bg-gray-200" aria-hidden="true"></span>
                    <h2 id="terms-quality-heading"
                        class="shrink-0 text-center text-[28px] font-extrabold uppercase leading-tight text-[#e9521d] sm:text-[40px]">
                        {{ __('core::terms.quality.title') }}
                    </h2>
                    <span class="h-px flex-1 bg-gray-200" aria-hidden="true"></span>
                </div>
                <div class="mt-6 grid gap-8 md:grid-cols-3 md:gap-14">
                    @foreach(__('core::terms.quality.stats') as $stat)
                        <article class="grid grid-cols-[52px_1fr] gap-x-5 gap-y-4">
                            <img src="{{ asset($stat['image']) }}" alt="{{ $stat['image_alt'] }}"
                                class="size-12 rounded-full object-contain">
                            <p class="self-center whitespace-nowrap text-[32px] font-extrabold leading-none text-[#e9521d] sm:text-[44px]">
                                {{ $stat['value'] }}
                            </p>
                            <div class="col-start-2">
                                <h3 class="font-extrabold text-[#00613d]">{{ $stat['label'] }}</h3>
                                <p class="mt-1.5 max-w-64 font-medium leading-6 text-slate-600">{{ $stat['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @include('core::partials.home.footer')
    </div>
@endsection

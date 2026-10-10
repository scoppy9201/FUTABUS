@extends('core::layouts.home')

@section('title', __('core::news.meta.title'))
@section('meta_description', __('core::news.meta.description'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main>
            <section class="border-b border-gray-100 bg-gray-50" aria-label="{{ __('core::news.all') }}">
                <div class="mx-auto flex w-full max-w-282 flex-col gap-4 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-0">
                    <nav class="scrollbar-hidden flex min-w-0 items-center gap-7 overflow-x-auto" aria-label="{{ __('core::news.all') }}">
                        <a href="{{ route('news', array_filter(['q' => $search])) }}" class="inline-flex shrink-0 items-center gap-2 py-2 text-sm font-extrabold {{ $category === '' ? 'text-futa-orange' : 'text-gray-700 hover:text-futa-orange' }}">
                            <x-heroicon-s-book-open class="size-5" />{{ __('core::news.all') }}
                        </a>
                        @foreach($categories as $newsCategory)
                            <a href="{{ route('news', array_filter(['category' => $newsCategory, 'q' => $search])) }}" class="shrink-0 py-2 text-sm font-extrabold whitespace-nowrap {{ $category === $newsCategory ? 'text-futa-orange' : 'text-gray-700 hover:text-futa-orange' }}">
                                {{ __('core::news.categories.'.$newsCategory) }}
                            </a>
                        @endforeach
                    </nav>
                    <form action="{{ route('news') }}" method="GET" class="relative w-full lg:max-w-84">
                        @if($category !== '')<input type="hidden" name="category" value="{{ $category }}">@endif
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('core::news.search') }}" aria-label="{{ __('core::news.search') }}" class="h-11 w-full rounded-full border border-gray-300 bg-white pl-12 pr-4 text-sm outline-none placeholder:text-gray-400
                            focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10">
                    </form>
                </div>
            </section>

            <div class="mx-auto w-full max-w-282 px-4 py-8 sm:px-6 lg:px-0 lg:py-10">
                @if($featuredArticles->isNotEmpty())
                    <section aria-labelledby="featured-news-title">
                        <div class="flex items-center gap-6">
                            <h1 id="featured-news-title" class="shrink-0 text-2xl font-extrabold text-futa-green sm:text-3xl">{{ __('core::news.featured') }}</h1>
                            <span class="h-px flex-1 bg-futa-green"></span>
                        </div>
                        @php
                            $leadArticle = $featuredArticles->first();
                        @endphp
                        <div class="mt-5 grid gap-5 lg:grid-cols-2">
                            <article class="group">
                                <a href="{{ route('promotion-article', $leadArticle['slug']) }}" class="block">
                                    <div class="aspect-599/337 overflow-hidden rounded-xl bg-futa-orange-soft">
                                        <img src="{{ asset($leadArticle['image']) }}" alt="{{ $leadArticle['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    </div>
                                    <h2 class="mt-3 line-clamp-2 text-xl font-extrabold uppercase leading-7 text-gray-950 group-hover:text-futa-orange">{{ $leadArticle['title'] }}</h2>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-[#64748b]">{{ $leadArticle['intro'] }}</p>
                                    <time class="mt-2 block text-xs text-[#64748b]">{{ $leadArticle['published_at'] }}</time>
                                </a>
                            </article>
                            <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
                                @foreach($featuredArticles->skip(1) as $article)
                                    <article class="group min-w-0">
                                        <a href="{{ route('promotion-article', $article['slug']) }}" class="block">
                                            <div class="aspect-599/337 overflow-hidden rounded-xl bg-futa-orange-soft">
                                                <img src="{{ asset($article['image']) }}" alt="{{ $article['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                            </div>
                                            <h3 class="mt-2 line-clamp-2 text-sm font-semibold uppercase leading-5 text-gray-950 group-hover:text-futa-orange">{{ \Illuminate\Support\Str::limit($article['title'], 62, '...') }}</h3>
                                            <time class="mt-1 block text-xs text-[#64748b]">{{ $article['published_at'] }}</time>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                @if($spotlightArticles->isNotEmpty())
                    <section class="mt-10" aria-labelledby="spotlight-title">
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="flex min-h-48 flex-col items-center justify-center rounded-xl bg-linear-to-br from-futa-orange to-futa-orange-dark px-5 text-center text-white">
                                <h2 id="spotlight-title" class="text-2xl font-extrabold">{{ __('core::news.spotlight') }}</h2>
                                <p class="mt-3 text-sm font-semibold">{{ __('core::news.spotlight_topic') }}</p>
                            </div>
                            @foreach($spotlightArticles as $article)
                                <article class="group min-w-0">
                                    <a href="{{ route('promotion-article', $article['slug']) }}" class="block">
                                        <div class="aspect-599/337 overflow-hidden rounded-xl bg-futa-orange-soft">
                                            <img src="{{ asset($article['image']) }}" alt="{{ $article['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                        </div>
                                        <h3 class="mt-2 line-clamp-2 text-sm font-semibold uppercase leading-5 text-gray-950 group-hover:text-futa-orange">{{ \Illuminate\Support\Str::limit($article['title'], 62, '...') }}</h3>
                                        <time class="mt-1 block text-xs text-[#64748b]">{{ $article['published_at'] }}</time>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="mt-11" aria-labelledby="all-news-title">
                    <div class="flex items-center gap-6">
                        <h2 id="all-news-title" class="shrink-0 text-2xl font-extrabold text-futa-green sm:text-3xl">{{ __('core::news.all_news') }}</h2>
                        <span class="h-px flex-1 bg-futa-green"></span>
                    </div>
                    @if($articles->isEmpty())
                        <p class="mt-6 rounded-xl border border-gray-200 bg-gray-50 px-5 py-12 text-center text-sm text-gray-500">{{ __('core::news.empty') }}</p>
                    @else
                        <div class="mt-6 grid gap-x-6 gap-y-5 lg:grid-cols-2">
                            @foreach($articles as $article)
                                <article class="group min-w-0">
                                    <a href="{{ route('promotion-article', $article['slug']) }}" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                                        <div class="aspect-599/337 overflow-hidden rounded-xl bg-futa-orange-soft">
                                            <img src="{{ asset($article['image']) }}" alt="{{ $article['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                        </div>
                                        <div class="flex min-w-0 flex-col">
                                            <h3 class="line-clamp-2 text-sm font-semibold uppercase leading-5 text-gray-950 group-hover:text-futa-orange" title="{{ $article['title'] }}">{{ \Illuminate\Support\Str::limit($article['title'], 65, '...') }}</h3>
                                            <p class="mt-1 line-clamp-3 text-sm leading-5 text-[#64748b]">{{ $article['intro'] }}</p>
                                            <time class="mt-auto pt-1 text-xs text-[#64748b]">{{ $article['published_at'] }}</time>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>

                        @if($articles->hasPages())
                            @php
                                $currentPage = $articles->currentPage();
                                $lastPage = $articles->lastPage();
                                $displayPages = collect([...range(1, min(5, $lastPage)), $currentPage - 1, $currentPage, $currentPage + 1, $lastPage - 1, $lastPage])
                                    ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values();
                            @endphp
                            <nav class="mt-9 flex items-center justify-center gap-2" aria-label="{{ __('core::news.pagination') }}">
                                @if($articles->onFirstPage())
                                    <span class="grid size-8 place-items-center rounded border border-gray-200 text-gray-300"><x-heroicon-o-chevron-left class="size-3.5" /></span>
                                @else
                                    <a href="{{ $articles->previousPageUrl() }}"
                                        rel="prev"
                                        aria-label="{{ __('core::news.previous_page') }}"
                                        class="grid size-8 place-items-center rounded border border-gray-300 text-gray-700 hover:border-futa-orange"><x-heroicon-o-chevron-left class="size-3.5" /></a>
                                @endif
                                @foreach($displayPages as $page)
                                    @if(! $loop->first && $page > $displayPages[$loop->index - 1] + 1)
                                        <span class="grid size-8 place-items-center text-gray-500">...</span>
                                    @endif
                                    <a href="{{ $articles->url($page) }}" @if($page === $currentPage) aria-current="page" @endif class="grid size-8 place-items-center rounded border text-sm font-semibold {{ $page === $currentPage ? 'border-futa-orange
                                        bg-futa-orange text-white' : 'border-gray-300 bg-white text-gray-700 hover:border-futa-orange' }}">{{ $page }}</a>
                                @endforeach
                                @if($articles->hasMorePages())
                                    <a href="{{ $articles->nextPageUrl() }}" rel="next" aria-label="{{ __('core::news.next_page') }}" class="grid size-8 place-items-center rounded border border-gray-300 text-gray-700 hover:border-futa-orange"><x-heroicon-o-chevron-right class="size-3.5" /></a>
                                @else
                                    <span class="grid size-8 place-items-center rounded border border-gray-200 text-gray-300"><x-heroicon-o-chevron-right class="size-3.5" /></span>
                                @endif
                            </nav>
                        @endif
                    @endif
                </section>
            </div>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection

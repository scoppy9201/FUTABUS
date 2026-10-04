<section class="bg-[#fff8f5] py-10 sm:py-12" aria-labelledby="latest-news-heading">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <div class="relative text-center">
            <h2 id="latest-news-heading" class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                {{ __('core::app.home.latest_news.title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4a342e] sm:text-base">{{ __('core::app.home.latest_news.subtitle') }}</p>
            <a href="{{ route('news') }}" class="mt-3 inline-flex text-sm font-medium text-[#ef5222] transition-colors hover:text-[#d94316] sm:absolute sm:right-0 sm:top-8 sm:mt-0">
                {{ __('core::app.home.latest_news.view_all') }}
            </a>
        </div>

        @if($newsArticles->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-orange-200 bg-white px-6 py-10 text-center text-sm text-gray-500">
                {{ __('core::app.home.latest_news.empty') }}
            </div>
        @else
            @php($newsPages = $newsArticles->chunk(3))
            <div class="mt-7 sm:mt-8" x-data="{
                active: 0,
                totalPages: {{ $newsPages->count() }},
                timer: null,
                init() { this.start(); },
                destroy() { clearInterval(this.timer); },
                start() {
                    clearInterval(this.timer);
                    if (this.totalPages > 1) {
                        this.timer = setInterval(() => { this.active = (this.active + 1) % this.totalPages; }, 5000);
                    }
                },
                goTo(page) { this.active = page; this.start(); },
            }">
                <div class="overflow-hidden">
                    <div class="flex items-start transition-transform duration-500 ease-out" :style="`transform: translateX(-${active * 100}%)`">
                        @foreach($newsPages as $page)
                            <div class="grid w-full shrink-0 grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($page as $article)
                                    <article class="group min-w-0">
                                        <a href="{{ route('promotion-article', $article['slug']) }}" class="block">
                                            <div class="aspect-599/337 overflow-hidden rounded-xl border border-gray-200 bg-white">
                                                <img src="{{ asset($article['image']) }}" alt="{{ __('core::app.home.latest_news.image_alt', ['title' => $article['title']]) }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                            </div>
                                            <h3 class="mt-3 line-clamp-2 min-h-10 text-base font-medium uppercase leading-5 text-gray-950 transition-colors group-hover:text-[#ef5222]" title="{{ $article['title'] }}">{{ \Illuminate\Support\Str::limit($article['title'], 72, '...') }}</h3>
                                            <div class="mt-2 flex items-center justify-between gap-4">
                                                <time class="text-sm text-[#64748b]" datetime="{{ substr($article['published_at'], 12, 4) }}-{{ substr($article['published_at'], 9, 2) }}-{{ substr($article['published_at'], 6, 2) }}">{{ substr($article['published_at'], 6) }}</time>
                                                <span class="inline-flex items-center gap-1 text-sm font-medium text-[#ef5222]">
                                                    {{ __('core::app.home.latest_news.details') }}
                                                    <x-heroicon-o-chevron-right class="size-4" />
                                                </span>
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex justify-center gap-2" aria-label="{{ __('core::app.home.latest_news.pages') }}">
                    @for($page = 0; $page < max(5, $newsPages->count()); $page++)
                        <button type="button"
                            class="h-2.5 rounded-full transition-all duration-300 disabled:cursor-default"
                            :class="active === {{ $page }} ? 'w-7 bg-[#ef5222]' : 'w-2.5 bg-gray-300'"
                            @click="goTo({{ $page }})"
                            :aria-current="active === {{ $page }} ? 'page' : null"
                            aria-label="{{ __('core::app.home.latest_news.page', ['number' => $page + 1]) }}"
                            @disabled($page >= $newsPages->count())
                        ></button>
                    @endfor
                </div>
            </div>
        @endif
    </div>
</section>

@if($relatedArticles->isNotEmpty())
    <section class="mt-24" aria-labelledby="related-articles-heading">
        <div class="flex items-center gap-5">
            <h2 id="related-articles-heading" class="shrink-0 text-2xl font-extrabold text-[#00613d] sm:text-[27px]">
                {{ __('core::app.home.latest_news.related_title') }}
            </h2>
            <span class="h-px flex-1 bg-[#00613d]" aria-hidden="true"></span>
            <a href="{{ $relatedViewAllUrl }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-[#ef5222] hover:text-[#d94316]">
                {{ __('core::app.home.latest_news.view_all') }}
                <x-heroicon-o-chevron-right class="size-4" />
            </a>
        </div>

        <div class="mt-8 grid gap-x-6 gap-y-4 lg:grid-cols-2">
            @foreach($relatedArticles as $related)
                <article class="min-w-0">
                    <a href="{{ route('promotion-article', $related['slug']) }}" class="group grid gap-4 sm:grid-cols-2 lg:gap-4">
                        <div class="aspect-599/337 overflow-hidden rounded-lg bg-orange-50">
                            <img src="{{ asset($related['image']) }}" alt="{{ $related['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <h3 class="line-clamp-2 text-base font-medium uppercase leading-5 text-gray-950 group-hover:text-[#ef5222]" title="{{ $related['title'] }}">{{ \Illuminate\Support\Str::limit($related['title'], 68, '...') }}</h3>
                            <p class="mt-1 line-clamp-3 text-sm leading-6 text-[#64748b]">{{ $related['intro'] }}</p>
                            <time class="mt-auto pt-1 text-xs text-[#64748b]" datetime="{{ substr($related['published_at'], 12, 4) }}-{{ substr($related['published_at'], 9, 2) }}-{{ substr($related['published_at'], 6, 2) }}">{{ $related['published_at'] }}</time>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </section>
@endif

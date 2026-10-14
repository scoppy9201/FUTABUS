@props(['paginator' => null, 'meta' => 'meta', 'navigate' => null, 'busy' => 'false'])

<div {{ $attributes->class(['flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500']) }} data-operations-pagination>
    @if($paginator)
        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $windowPage = min($last, max(1, $current));
            $pages = array_unique(array_merge([1, $last], range(max(1, $windowPage - 1), min($last, $windowPage + 1))));
            if ($last <= 5) $pages = range(1, $last);
            sort($pages);
            $previous = 0;
        @endphp
        <p>{{ __('core::pagination.showing', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}</p>
        <nav aria-label="{{ __('core::pagination.label') }}" class="flex items-center gap-1">
            @if($paginator->previousPageUrl())
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('core::pagination.previous') }}"
                    class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-futa-orange hover:text-futa-orange"><x-heroicon-o-chevron-left class="size-4" /></a>
            @else
                <span aria-disabled="true" aria-label="{{ __('core::pagination.previous') }}" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300"><x-heroicon-o-chevron-left class="size-4" /></span>
            @endif
            @foreach($pages as $page)
                @if($previous && $page > $previous + 1)<span aria-hidden="true" class="px-1">…</span>@endif
                @if($page === $current)
                    <span aria-current="page" class="grid size-8 place-items-center rounded-lg bg-futa-orange font-bold text-white shadow-sm">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" aria-label="{{ __('core::pagination.page', ['page' => $page]) }}"
                        class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white font-semibold text-slate-600 hover:border-futa-orange hover:text-futa-orange">{{ $page }}</a>
                @endif
                @php($previous = $page)
            @endforeach
            @if($paginator->nextPageUrl())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('core::pagination.next') }}"
                    class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-futa-orange hover:text-futa-orange"><x-heroicon-o-chevron-right class="size-4" /></a>
            @else
                <span aria-disabled="true" aria-label="{{ __('core::pagination.next') }}" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300"><x-heroicon-o-chevron-right class="size-4" /></span>
            @endif
        </nav>
    @else
        <p>
            {{ __('core::pagination.showing_prefix') }}
            <span x-text="{{ $meta }}.from ?? 0"></span>–<span x-text="{{ $meta }}.to ?? 0"></span>
            / <span x-text="{{ $meta }}.total ?? 0"></span> {{ __('core::pagination.records') }}
        </p>
        <nav aria-label="{{ __('core::pagination.label') }}" class="flex items-center gap-1">
            <button type="button" @click="{{ $navigate }}({{ $meta }}.current_page - 1)" :disabled="{{ $busy }} || {{ $meta }}.current_page <= 1"
                aria-label="{{ __('core::pagination.previous') }}"
                class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-futa-orange hover:text-futa-orange disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-300">
                <x-heroicon-o-chevron-left class="size-4" />
            </button>
            <template x-for="(page, index) in window.FutaPagination.pages({{ $meta }}.current_page, {{ $meta }}.last_page)" :key="index">
                <span>
                    <template x-if="page === null"><span aria-hidden="true" class="px-1">…</span></template>
                    <template x-if="page !== null">
                        <button type="button" @click="{{ $navigate }}(page)" :disabled="{{ $busy }} || page === {{ $meta }}.current_page"
                            :aria-current="page === {{ $meta }}.current_page ? 'page' : null"
                            :aria-label="@js(__('core::pagination.page_prefix')) + ' ' + page" x-text="page"
                            :class="page === {{ $meta }}.current_page ? 'border-futa-orange bg-futa-orange text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-futa-orange hover:text-futa-orange'"
                            class="grid size-8 place-items-center rounded-lg border font-semibold disabled:cursor-default"></button>
                    </template>
                </span>
            </template>
            <button type="button" @click="{{ $navigate }}({{ $meta }}.current_page + 1)" :disabled="{{ $busy }} || {{ $meta }}.current_page >= {{ $meta }}.last_page"
                aria-label="{{ __('core::pagination.next') }}"
                class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-futa-orange hover:text-futa-orange disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-300">
                <x-heroicon-o-chevron-right class="size-4" />
            </button>
        </nav>
    @endif
</div>

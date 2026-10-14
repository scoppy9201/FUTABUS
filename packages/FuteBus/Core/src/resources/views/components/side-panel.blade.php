@props(['id', 'title', 'description' => null])

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" {{ $attributes->class(['futa-side-panel text-left']) }}>
    <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5">
        <div class="min-w-0">
            <h2 id="{{ $id }}-title" class="text-xl font-extrabold tracking-tight text-slate-950">{{ $title }}</h2>
            @if($description)<p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>@endif
        </div>
        <form method="dialog" data-no-loader>
            <button type="submit" aria-label="{{ __('core::side-panel.close') }}" class="rounded-full p-2 text-slate-500 hover:bg-slate-100 focus-visible:outline-futa-orange">
                <x-heroicon-o-x-mark class="size-5" />
            </button>
        </form>
    </header>
    <div class="flex min-h-0 flex-1 flex-col">{{ $slot }}</div>
</dialog>

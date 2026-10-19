<section class="futa-portal-quick-lookup" aria-label="{{ __('core::portal.lookup_title') }}">
    <div class="flex items-center gap-2.5">
        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-futa-green/5 text-futa-green">
            <x-heroicon-o-magnifying-glass class="size-5" />
        </span>
        <div>
            <h2 class="text-sm font-semibold text-slate-800">{{ __('core::portal.lookup_title') }}</h2>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('core::portal.lookup_intro') }}</p>
        </div>
    </div>
    @if(auth()->user()->hasPermissionTo('booking.view'))
        <form action="{{ route($lookupRoute) }}" method="get" class="mt-3 space-y-2">
            <label for="portal-quick-lookup" class="sr-only">{{ __('core::portal.lookup_placeholder') }}</label>
            <input id="portal-quick-lookup" name="search" type="search" required maxlength="100" autocomplete="off"
                placeholder="{{ __('core::portal.lookup_placeholder') }}"
                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-futa-orange focus:outline-2 focus:outline-futa-orange/15">
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-futa-orange px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                {{ __('core::portal.lookup_submit') }}
                <x-heroicon-o-arrow-right class="size-4" />
            </button>
        </form>
    @else
        <p class="mt-3 flex items-start gap-2 text-xs leading-5 text-slate-500">
            <x-heroicon-o-lock-closed class="mt-0.5 size-4 shrink-0" />
            {{ __('core::portal.lookup_denied') }}
        </p>
    @endif
</section>

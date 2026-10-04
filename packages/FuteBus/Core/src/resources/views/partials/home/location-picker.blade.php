<div
    class="relative min-w-0"
    :class="locationOpen === '{{ $field }}' ? 'z-50' : 'z-auto'"
    @click.outside="if (locationOpen === '{{ $field }}') locationOpen = null"
    @keydown.escape.window="if (locationOpen === '{{ $field }}') { locationOpen = null; $refs.{{ $field }}Trigger.focus({ preventScroll: true }); }"
>
    <label class="mb-2 ml-4 block text-sm font-bold text-gray-900">{{ $label }}</label>
    <input type="hidden" name="{{ $field }}" :value="{{ $field }}">
    <button
        x-ref="{{ $field }}Trigger"
        type="button"
        @click="openLocation('{{ $field }}')"
        :aria-expanded="locationOpen === '{{ $field }}'"
        aria-haspopup="dialog"
        class="flex h-16.75 w-full items-center rounded-[10px] border border-gray-300 bg-white px-4.5 text-base text-gray-900 outline-none transition hover:border-[#ff8a65] focus:border-[#ff8a65] focus:ring-3 focus:ring-[#ef5222]/10"
    >
        <span
            class="w-full truncate"
            :class="{{ $field }} ? 'text-left font-semibold text-gray-900' : 'text-center text-gray-400'"
            x-text="{{ $field }} || @js($placeholder)"
        ></span>
    </button>

    <div
        x-cloak
        x-show="locationOpen === '{{ $field }}'"
        x-transition:enter="transition-[opacity,transform] duration-220 ease-out"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-[.96]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition-[opacity,transform] duration-150 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0 scale-[.98]"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
        role="dialog"
        aria-label="{{ $label }}"
        class="absolute {{ $field === 'destination' ? 'right-0' : 'left-0' }} -top-4 z-50 w-100 max-w-[calc(100vw-32px)] rounded-xl bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,.19)]"
    >
        <label for="hero-{{ $field }}-search" class="mb-2 block px-4 text-sm font-medium text-gray-900">{{ $label }}</label>
        <div class="relative">
            <input
                id="hero-{{ $field }}-search"
                x-ref="{{ $field }}Search"
                type="text"
                x-model="locationQuery"
                @input="onLocationQueryChange()"
                @keydown.arrow-down.prevent="moveLocationHighlight(1)"
                @keydown.arrow-up.prevent="moveLocationHighlight(-1)"
                @keydown.enter.prevent="activateHighlightedLocation()"
                placeholder="{{ $placeholder }}"
                role="combobox"
                aria-autocomplete="list"
                aria-controls="hero-{{ $field }}-options"
                :aria-expanded="locationRowCount() > 0"
                class="h-16.75 w-full rounded-[10px] border border-[#ff8a65] bg-white px-4.5 pr-10 text-base text-gray-900 outline-none ring-3 ring-[#ef5222]/10 placeholder:text-gray-400"
            >
            <button
                x-show="locationQuery.length > 0"
                type="button"
                @click="locationQuery = ''; onLocationQueryChange(); $refs.{{ $field }}Search.focus()"
                aria-label="{{ __('core::app.home.hero.clear_location') }}"
                class="absolute right-3 top-1/2 grid size-5 -translate-y-1/2 place-items-center rounded-full bg-gray-300 text-xs font-bold leading-none text-white transition hover:bg-gray-400"
            >&times;</button>
        </div>

        <div
            id="hero-{{ $field }}-options"
            x-show="locationRowCount() > 0"
            class="hero-location-results mt-3 max-h-[min(400px,calc(100vh-190px))] overflow-y-auto overscroll-contain"
        >
            <div x-show="visibleProvinces().length > 0" role="listbox" aria-label="{{ __('core::app.home.hero.provinces_heading') }}">
                <p class="sticky top-0 z-10 border-b border-gray-200 bg-white px-2 py-2 text-sm font-semibold uppercase text-gray-900">
                    {{ __('core::app.home.hero.provinces_heading') }}
                </p>
                <div>
                    <template x-for="(province, index) in visibleProvinces()" :key="province">
                        <button
                            type="button"
                            role="option"
                            :aria-selected="highlightedLocation === index"
                            @mouseenter="highlightedLocation = index"
                            @click="chooseLocation(province)"
                            class="block w-full border-b border-gray-200 px-2 py-3 text-left text-sm transition-colors"
                            :class="highlightedLocation === index ? 'bg-[#fff3ed] text-[#ef5222]' : 'text-gray-900 hover:bg-orange-50'"
                            x-text="province"
                        ></button>
                    </template>
                </div>
            </div>

            <div x-show="visibleAreas().length > 0" class="border-t-4 border-gray-100">
                <template x-for="(area, index) in visibleAreas()" :key="area.name">
                    <div class="border-b border-gray-200">
                        <button
                            type="button"
                            @mouseenter="highlightedLocation = visibleProvinces().length + index"
                            @click="toggleLocationArea(area)"
                            class="flex w-full items-center justify-between gap-2 px-2 py-3 text-left transition-colors hover:bg-orange-50"
                            :class="highlightedLocation === visibleProvinces().length + index ? 'bg-[#fffaf7]' : ''"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-gray-900" x-text="area.name"></span>
                                <span class="mt-1 block truncate text-xs text-slate-500" x-text="area.province"></span>
                            </span>
                            <span class="flex shrink-0 items-center gap-3 text-xs text-slate-500">
                                <span x-text="area.office_count + ' ' + (area.office_count === 1 ? @js(__('core::app.home.hero.office_singular')) : @js(__('core::app.home.hero.office_plural')))"></span>
                                <svg x-show="area.offices.length > 0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4 text-gray-800 transition-transform" :class="expandedArea === area.name ? 'rotate-180' : ''" aria-hidden="true"><path d="m5 7.5 5 5 5-5" /></svg>
                            </span>
                        </button>
                        <div x-show="expandedArea === area.name && area.offices.length > 0" class="border-t border-gray-100 bg-white">
                            <template x-for="office in area.offices" :key="office.name + office.address">
                                <button
                                    type="button"
                                    @click="chooseLocation(office.name)"
                                    class="flex w-full items-start gap-3 px-2 py-3 text-left transition-colors hover:bg-[#fff7f2]"
                                >
                                    <x-heroicon-o-home class="mt-0.5 size-5 shrink-0 text-[#f59b23]" />
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-900" x-text="office.name"></span>
                                        <span class="mt-0.5 block truncate text-xs text-slate-500" x-text="office.address"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

        </div>
        <p x-show="locationQuery.trim() && locationRowCount() === 0" class="px-2 pt-4 pb-3 text-center text-sm font-medium text-slate-500">
            {{ __('core::app.home.hero.location_not_found') }}
        </p>
    </div>
</div>

<div class="relative" x-data="{ open: false, init() {}, destroy() {} }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
        aria-controls="staff-header-language-menu"
        class="flex items-center gap-1.5 rounded-lg px-1.5 py-1 text-sm font-bold text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-futa-orange"
        aria-label="{{ __('core::app.home.navbar.language_selector') }}">
        <span class="inline-flex size-6 items-center justify-center overflow-hidden rounded-full">
            <img src="{{ asset('icons/flags/'.app()->getLocale().'.svg') }}" alt="" class="size-full object-cover">
        </span>
        <span>{{ strtoupper(app()->getLocale()) }}</span>
        <x-heroicon-s-chevron-down class="size-3.5 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
    </button>
    <div id="staff-header-language-menu" x-cloak x-show="open" x-transition role="menu"
        class="absolute right-0 top-full z-[70] mt-2 w-40 overflow-hidden border border-gray-100 bg-white py-1 shadow-xl">
        @foreach(['vi', 'en'] as $language)
            <a role="menuitem" href="{{ request()->fullUrlWithQuery(['lang' => $language]) }}"
                @class([
                    'flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors focus-visible:bg-futa-orange-soft focus-visible:outline-none',
                    'bg-futa-orange-soft text-futa-orange' => app()->getLocale() === $language,
                    'text-gray-700 hover:bg-gray-50' => app()->getLocale() !== $language,
                ])>
                <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                    <img src="{{ asset('icons/flags/'.$language.'.svg') }}" alt="" class="size-full object-cover">
                </span>
                <span class="flex-1">{{ __('StaffDashboard::language.'.$language) }}</span>
            </a>
        @endforeach
    </div>
</div>

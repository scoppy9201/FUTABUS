<header class="futa-header-pattern relative z-20 text-white">
    <div class="h-15.5">
        <div class="relative mx-auto flex h-15.5 w-[calc(100%-24px)] max-w-282 items-center justify-between sm:w-[calc(100%-32px)]">
            <div class="flex items-center gap-3.5">
                {{-- Language dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="flex items-center gap-1.5 text-sm font-bold" aria-label="{{ __('core::app.home.navbar.language_selector') }}">
                        <span class="inline-flex size-6.5 items-center justify-center overflow-hidden rounded-full">
                            @if(app()->getLocale() === 'vi')
                                <img src="{{ asset('icons/flags/vi.svg') }}" alt="" class="h-full w-full object-cover">
                            @else
                                <img src="{{ asset('icons/flags/en.svg') }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <x-heroicon-o-chevron-down
                            class="h-3 w-3 transition-transform duration-200"
                            ::class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute left-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-white/20 bg-white py-1 shadow-xl"
                        style="display: none;"
                    >
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors {{ app()->getLocale() === 'vi' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="{{ asset('icons/flags/vi.svg') }}" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>Tiếng Việt</span>
                        </a>
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="{{ asset('icons/flags/en.svg') }}" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>English</span>
                        </a>
                    </div>
                </div>

                <span class="hidden h-6 w-px bg-white/70 sm:block"></span>

                <div class="relative hidden sm:block" x-data="{ open: false }" @click.away="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-1.5 text-sm font-bold"
                        :aria-expanded="open"
                        aria-haspopup="menu"
                    >
                        <span class="grid size-6.25 place-items-center rounded-full bg-white text-[#22a55b]">
                            <x-heroicon-o-device-phone-mobile class="h-4 w-4" />
                        </span>
                        <span>{{ __('core::app.home.navbar.download_app') }}</span>
                        <x-heroicon-o-chevron-down
                            class="h-3 w-3 transition-transform duration-200"
                            ::class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        role="menu"
                        class="absolute left-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-white/20 bg-white py-1 shadow-xl"
                        style="display: none;"
                    >
                        <a href="#" role="menuitem" class="flex items-center gap-2.5 bg-orange-50 px-3 py-2.5 text-[#ef5222] transition-colors hover:bg-orange-100">
                            <img src="{{ asset('icons/stores/google-play.svg') }}" alt="" class="h-7 w-auto max-w-full">
                        </a>
                        <a href="#" role="menuitem" class="flex items-center gap-2.5 px-3 py-2.5 text-gray-700 transition-colors hover:bg-orange-50 hover:text-[#ef5222]">
                            <img src="{{ asset('icons/stores/app-store.svg') }}" alt="" class="h-7 w-auto max-w-full">
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="absolute left-1/2 top-0 grid h-17 w-80 -translate-x-1/2 place-items-center max-lg:w-66 max-sm:w-47.5" aria-label="{{ __('core::app.home.navbar.home_aria') }}">
                <img
                    src="{{ asset('icons/illustrations/navbar-logo-panel.svg') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full drop-shadow-[0_2px_1px_rgba(103,42,11,.08)]"
                    aria-hidden="true"
                >
                <img src="{{ asset('icons/futabus-logo.png') }}" alt="{{ __('core::app.home.navbar.logo_alt') }}" class="futa-brand-logo relative z-10 h-12 w-48.5 object-contain max-lg:scale-125 max-sm:w-33 max-sm:scale-110">
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="flex min-h-8.75 items-center gap-2 rounded-full bg-white px-4.5 text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <x-heroicon-o-user-circle class="h-5 w-5" />
                    <span class="max-sm:hidden">{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex min-h-8.75 items-center gap-2 rounded-full bg-white px-4.5 text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <x-heroicon-o-user-circle class="h-5 w-5" />
                    <span class="max-sm:hidden">{{ __('core::app.home.navbar.login') }}</span>
                </a>
            @endauth
        </div>
    </div>

    <nav class="h-17" aria-label="{{ __('core::app.home.navbar.primary_navigation') }}">
        <div class="scrollbar-hidden mx-auto flex h-17 max-w-250 items-center justify-center gap-[clamp(28px,3.2vw,58px)] overflow-x-auto px-4 max-md:justify-start">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'futa-nav-active relative' : '' }} py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.home') }}</a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.schedules') }}</a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.lookup') }}</a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.news') }}</a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.invoice') }}</a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.contact') }}</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'futa-nav-active relative' : '' }} py-5.75 text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.about') }}</a>
        </div>
    </nav>
</header>

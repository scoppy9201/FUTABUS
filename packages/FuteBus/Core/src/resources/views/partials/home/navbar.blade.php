<header class="futa-header-pattern relative z-20 text-white">
    <div class="h-[62px]">
        <div class="relative mx-auto flex h-[62px] w-[calc(100%-24px)] max-w-[1128px] items-center justify-between sm:w-[calc(100%-32px)]">
            <div class="flex items-center gap-3.5">
                {{-- Language dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="flex items-center gap-1.5 text-sm font-bold" aria-label="{{ __('core::app.home.navbar.language_selector') }}">
                        <span class="inline-flex h-[26px] w-[26px] items-center justify-center overflow-hidden rounded-full">
                            @if(app()->getLocale() === 'vi')
                                <svg class="h-full w-full" viewBox="0 0 640 480"><path fill="#da251d" d="M0 0h640v480H0z"/><path fill="#ffcd00" d="m339.4 180.3-35.3 108.6-35.4-108.6h-38l53.3 163.4h34.4l53.3-163.4z"/></svg>
                            @else
                                <svg class="h-full w-full" viewBox="0 0 60 30"><clipPath id="s"><path d="M0,0 v30 h60 v-30 z"/></clipPath><clipPath id="t"><path d="M30,15 h30 v15 zv15 h-30 z h-30 v-15 zv-15 h30 z"/></clipPath><g clip-path="url(#s)"><path d="M0,0 v30 h60 v-30 z" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t)" stroke="#C8102E" stroke-width="4"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></g></svg>
                            @endif
                        </span>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <svg class="h-3 w-3 fill-none stroke-current stroke-2 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20"><path d="m5 7.5 5 5 5-5" /></svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute left-0 top-full z-50 mt-2 w-[160px] overflow-hidden rounded-lg border border-white/20 bg-white py-1 shadow-xl" style="display: none;">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors {{ app()->getLocale() === 'vi' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex h-[22px] w-[22px] items-center justify-center overflow-hidden rounded-full">
                                <svg class="h-full w-full" viewBox="0 0 640 480"><path fill="#da251d" d="M0 0h640v480H0z"/><path fill="#ffcd00" d="m339.4 180.3-35.3 108.6-35.4-108.6h-38l53.3 163.4h34.4l53.3-163.4z"/></svg>
                            </span>
                            <span>Tiếng Việt</span>
                        </a>
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="inline-flex h-[22px] w-[22px] items-center justify-center overflow-hidden rounded-full">
                                <svg class="h-full w-full" viewBox="0 0 60 30"><clipPath id="s2"><path d="M0,0 v30 h60 v-30 z"/></clipPath><clipPath id="t2"><path d="M30,15 h30 v15 zv15 h-30 z h-30 v-15 zv-15 h30 z"/></clipPath><g clip-path="url(#s2)"><path d="M0,0 v30 h60 v-30 z" fill="#012169"/><path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#t2)" stroke="#C8102E" stroke-width="4"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></g></svg>
                            </span>
                            <span>English</span>
                        </a>
                    </div>
                </div>

                <span class="hidden h-6 w-px bg-white/70 sm:block"></span>

                <a href="#" class="hidden items-center gap-1.5 text-sm font-bold sm:flex">
                    <span class="grid h-[25px] w-[25px] place-items-center rounded-full bg-white text-[#22a55b]">
                        <svg class="h-4 w-4 fill-none stroke-current stroke-[1.8]" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="2.5" width="12" height="19" rx="2" /><path d="M10 6h4M10 18h4" /></svg>
                    </span>
                    <span>{{ __('core::app.home.navbar.download_app') }}</span>
                    <svg class="h-3 w-3 fill-none stroke-current stroke-2" viewBox="0 0 20 20" aria-hidden="true"><path d="m5 7.5 5 5 5-5" /></svg>
                </a>
            </div>

            <a href="{{ route('home') }}" class="absolute left-1/2 top-0 grid h-[68px] w-[320px] -translate-x-1/2 place-items-center max-lg:w-[264px] max-sm:w-[190px]" aria-label="{{ __('core::app.home.navbar.home_aria') }}">
                <svg class="absolute inset-0 h-full w-full drop-shadow-[0_2px_1px_rgba(103,42,11,.08)]" viewBox="0 0 320 68" preserveAspectRatio="none" aria-hidden="true">
                    <path fill="white" d="M0 0H320L281 49C273 60 263 66 246 68H74C57 66 47 60 39 49L0 0Z" />
                </svg>
                <img src="{{ asset('icons/futabus-logo.png') }}" alt="{{ __('core::app.home.navbar.logo_alt') }}" class="futa-brand-logo relative z-10 h-[48px] w-[194px] object-contain max-lg:scale-125 max-sm:w-[132px] max-sm:scale-110">
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="flex min-h-[35px] items-center gap-2 rounded-full bg-white px-[18px] text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <x-heroicon-o-user-circle class="h-5 w-5" />
                    <span class="max-sm:hidden">{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex min-h-[35px] items-center gap-2 rounded-full bg-white px-[18px] text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <x-heroicon-o-user-circle class="h-5 w-5" />
                    <span class="max-sm:hidden">{{ __('core::app.home.navbar.login') }}</span>
                </a>
            @endauth
        </div>
    </div>

    <nav class="h-[68px]" aria-label="{{ __('core::app.home.navbar.primary_navigation') }}">
        <div class="mx-auto flex h-[68px] max-w-[1000px] items-center justify-center gap-[clamp(28px,3.2vw,58px)] overflow-x-auto px-4 max-md:justify-start">
            <a href="{{ route('home') }}" class="futa-nav-active relative py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.home') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.schedules') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.lookup') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.news') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.invoice') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.contact') }}</a>
            <a href="#" class="py-[23px] text-sm font-extrabold whitespace-nowrap">{{ __('core::app.home.navbar.about') }}</a>
        </div>
    </nav>
</header>

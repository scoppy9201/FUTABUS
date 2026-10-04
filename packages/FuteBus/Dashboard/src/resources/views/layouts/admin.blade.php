<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b2733">
    <title>@yield('title') · FUTABUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-[#f5f7f8] font-sans text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen lg:flex">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            id="owner-navigation"
            class="fixed inset-y-0 left-0 z-50 flex w-66 -translate-x-full flex-col bg-[#0b2733] text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            aria-label="{{ __('Dashboard::app.navigation') }}"
        >
            <div class="flex h-22 items-center justify-between border-b border-white/10 px-5">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ __('Dashboard::app.overview') }}">
                    <span class="grid h-12 w-18 shrink-0 place-items-center rounded-xl bg-white p-1.5">
                        <img src="{{ asset('icons/futabus-logo.png') }}" alt="" class="max-h-10 max-w-full object-contain">
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-base font-extrabold tracking-wide">FUTA Bus Lines</span>
                        <span class="block text-xs font-semibold text-white/60">{{ __('Dashboard::app.owner_portal') }}</span>
                    </span>
                </a>
                <button type="button" class="rounded-lg p-2 text-white/75 hover:bg-white/10 lg:hidden" @click="sidebarOpen = false" aria-label="{{ __('Dashboard::app.close_menu') }}">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 py-6" aria-label="{{ __('Dashboard::app.navigation') }}">
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white/45">{{ __('Dashboard::app.operations') }}</p>
                @foreach([
                    ['key' => 'overview', 'icon' => 'squares-2x2', 'route' => route('dashboard')],
                    ['key' => 'trips', 'icon' => 'calendar-days', 'route' => route('dashboard.section', 'trips')],
                    ['key' => 'routes', 'icon' => 'map', 'route' => route('dashboard.section', 'routes')],
                    ['key' => 'buses', 'icon' => 'truck', 'route' => route('dashboard.section', 'buses')],
                    ['key' => 'bookings', 'icon' => 'ticket', 'route' => route('dashboard.section', 'bookings')],
                    ['key' => 'customers', 'icon' => 'users', 'route' => route('dashboard.section', 'customers')],
                    ['key' => 'reports', 'icon' => 'chart-bar', 'route' => route('dashboard.section', 'reports')],
                ] as $item)
                    @php($active = $item['key'] === ($section ?? 'overview'))
                    <a
                        href="{{ $item['route'] }}"
                        @if($active) aria-current="page" @endif
                        @class([
                            'group flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors',
                            'bg-[#ef5222] text-white shadow-lg shadow-orange-950/15' => $active,
                            'text-white/75 hover:bg-white/10 hover:text-white' => ! $active,
                        ])
                    >
                        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-5 shrink-0" />
                        <span>{{ __('Dashboard::app.'.$item['key']) }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/75 hover:bg-white/10 hover:text-white">
                    <x-heroicon-o-arrow-top-right-on-square class="size-5" />
                    {{ __('Dashboard::app.public_site') }}
                </a>
                <form
                    action="{{ route('logout') }}" method="post" data-confirm
                    data-confirm-title="{{ __('core::confirm.logout_title') }}"
                    data-confirm-message="{{ __('core::confirm.logout_message') }}"
                >
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-white/75 hover:bg-white/10 hover:text-white">
                        <x-heroicon-o-arrow-right-on-rectangle class="size-5" />
                        {{ __('Dashboard::app.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex h-18 items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-7 lg:px-10">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-700 lg:hidden" @click="sidebarOpen = true" :aria-expanded="sidebarOpen.toString()" aria-controls="owner-navigation" aria-label="{{ __('Dashboard::app.open_menu') }}">
                        <x-heroicon-o-bars-3 class="size-6" />
                    </button>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#ef5222]">{{ __('Dashboard::app.owner_portal') }}</p>
                        <p class="truncate text-base font-bold text-slate-900 sm:text-lg">@yield('page_title')</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'vi' ? 'en' : 'vi']) }}" class="rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-bold text-slate-600 hover:border-[#ef5222] hover:text-[#ef5222]">
                        {{ strtoupper(app()->getLocale()) }}
                    </a>
                    <span class="hidden text-sm font-medium text-slate-500 sm:block">{{ now()->translatedFormat('d/m/Y') }}</span>
                    <span class="grid size-9 place-items-center rounded-full bg-orange-100 font-bold text-[#d7461a]">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                    <span class="hidden max-w-38 truncate text-sm font-semibold text-slate-800 xl:block">{{ Auth::user()->name }}</span>
                </div>
            </header>

            <main class="mx-auto w-full max-w-392 px-4 py-6 sm:px-7 sm:py-8 lg:px-10">
                @yield('content')
            </main>
        </div>
    </div>
    <x-confirm-dialog />
</body>
</html>

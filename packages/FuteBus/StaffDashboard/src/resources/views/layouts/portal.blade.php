<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>@yield('title') · FUTABUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'packages/FuteBus/Core/src/resources/css/app.css', 'packages/FuteBus/Core/src/resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js" defer></script>
</head>
<body class="dashboard-page flex h-dvh flex-col overflow-hidden bg-[#F8F9FA] font-[Inter,sans-serif] text-gray-800 antialiased"
    x-data="Object.assign(staffDashboard(@js(route('api.v1.staff.dashboard.show'))), { sidebarOpen: false, viewportWidth: window.innerWidth })" @resize.window="viewportWidth = window.innerWidth">
    @include('core::components.ui-select-template')
    @include('core::components.ui-calendar-templates')
    @include('core::partials.global-loader')
    @include('StaffDashboard::partials.notifications')

    <header class="futa-portal-header sticky top-0 z-30 shrink-0 bg-white">
        <div class="flex h-20 items-center justify-between gap-4 px-4 lg:px-6">
            <div class="flex min-w-0 items-center gap-4">
                <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-futa-orange lg:hidden" aria-label="{{ __('StaffDashboard::staff.menu') }}">
                    <x-heroicon-o-bars-3 class="size-6" />
                </button>
                <a href="{{ route('staff.dashboard') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ __('StaffDashboard::staff.home') }}">
                    @include('core::partials.portal-logo')
                </a>
                <span class="hidden h-7 w-px bg-gray-200 md:block"></span>
                <span class="hidden rounded-full border border-futa-green/10 bg-futa-green/5 px-3 py-1.5 text-sm font-semibold text-futa-green md:inline-flex">{{ __('StaffDashboard::staff.role') }}</span>
            </div>

            <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                        aria-haspopup="true" aria-controls="staff-language-menu"
                        class="flex items-center gap-1.5 rounded-lg px-1.5 py-1 text-sm font-bold text-gray-700 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-futa-orange"
                        aria-label="{{ __('core::app.home.navbar.language_selector') }}">
                        <span class="inline-flex size-6.5 items-center justify-center overflow-hidden rounded-full"><img src="{{ asset('icons/flags/'.app()->getLocale().'.svg') }}" alt="" class="size-full object-cover"></span>
                        <span>{{ strtoupper(app()->getLocale()) }}</span><x-heroicon-s-chevron-down class="size-3.5 transition-transform" ::class="open ? 'rotate-180' : ''" />
                    </button>
                    <div id="staff-language-menu" x-cloak x-show="open" x-transition class="absolute right-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-gray-100 bg-white py-1 shadow-xl">
                        @foreach(['vi', 'en'] as $language)
                            <a href="{{ request()->fullUrlWithQuery(['lang' => $language]) }}"
                                @class([
                                    'flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors',
                                    'bg-futa-orange-soft text-futa-orange' => app()->getLocale() === $language,
                                    'text-gray-700 hover:bg-gray-50' => app()->getLocale() !== $language,
                                ])>
                                <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full"><img src="{{ asset('icons/flags/'.$language.'.svg') }}" alt="" class="size-full object-cover"></span>
                                <span>{{ __('StaffDashboard::language.'.$language) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @include('Notification::partials.bell')
                <span class="hidden h-6 w-px bg-gray-200 sm:block"></span>
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="relative grid size-10 shrink-0 place-items-center overflow-hidden rounded-full border-2 border-futa-green/20 bg-futa-green/5 font-bold text-futa-green">
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        @if(is_string(Auth::user()->avatar) && str_starts_with(Auth::user()->avatar, 'avatars/') && ! str_contains(Auth::user()->avatar, '..'))
                            <img src="{{ route('api.v1.staff.me.avatar', ['v' => Auth::user()->updated_at?->timestamp]) }}" alt="" class="absolute inset-0 size-full rounded-full object-cover" x-on:error="$el.remove()">
                        @endif
                    </span>
                    <span class="hidden max-w-48 text-left sm:block"><span class="block truncate text-sm font-bold leading-tight text-gray-800">{{ Auth::user()->name }}</span><span class="block text-xs text-gray-500">{{ __('StaffDashboard::staff.role') }}</span></span>
                    <x-heroicon-s-chevron-down class="hidden size-4 text-gray-500 sm:block" />
                    <form action="{{ route('logout') }}" method="post" data-confirm data-confirm-title="{{ __('core::confirm.logout_title') }}" data-confirm-message="{{ __('core::confirm.logout_message') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600 focus-visible:outline-2 focus-visible:outline-red-600"
                            title="{{ __('core::app.home.navbar.logout') }}"
                            aria-label="{{ __('core::app.home.navbar.logout') }}">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-5" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="relative flex min-h-0 flex-1 overflow-hidden">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>
        @include('StaffDashboard::partials.sidebar', ['dashboardShell' => true])
        <main class="min-h-0 min-w-0 flex-1 overflow-y-auto bg-[#F8F9FA] p-4 md:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    @include('core::components.confirm-dialog')
</body>
</html>

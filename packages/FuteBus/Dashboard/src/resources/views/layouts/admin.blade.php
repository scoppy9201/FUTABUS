<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>@yield('title') · FUTABUS</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'packages/FuteBus/Core/src/resources/css/app.css',
        'packages/FuteBus/Core/src/resources/js/app.js',
        'packages/FuteBus/Dashboard/src/resources/css/app.css',
        'packages/FuteBus/Reporting/src/resources/js/app.js',
        'packages/FuteBus/AiAssistant/src/resources/css/app.css',
        'packages/FuteBus/AiAssistant/src/resources/js/app.js',
    ])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js" defer></script>
</head>
<body class="dashboard-page flex h-screen overflow-hidden flex-col bg-[#F8F9FA] font-[Inter,sans-serif] text-gray-800 antialiased"
    x-data="{ sidebarOpen: false, viewportWidth: window.innerWidth }" @resize.window="viewportWidth = window.innerWidth">
    @include('core::components.ui-select-template')
    @include('core::components.ui-calendar-templates')
    @include('core::partials.global-loader')

    <!-- HEADER -->
    <header class="futa-portal-header sticky top-0 z-[60] shrink-0 bg-white">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-futa-orange focus:outline-none">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    @include('core::partials.portal-logo')
                </a>
                <div class="hidden md:block h-6 w-px bg-gray-200 mx-2"></div>
                <span class="hidden md:block text-xs md:text-sm font-semibold bg-futa-green/5 text-futa-green px-3 py-1 rounded-full border border-futa-green/10">
                    {{ __('Dashboard::app.owner_portal') }}
                </span>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                <div
                    class="relative"
                    x-data="{ open: false }"
                    @click.away="open = false"
                    @keydown.escape.window="open = false"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-haspopup="menu"
                        aria-controls="dashboard-language-menu"
                        class="flex items-center gap-1.5 rounded-lg px-1.5 py-1 text-sm font-bold text-gray-700 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-futa-orange"
                        aria-label="{{ __('core::app.home.navbar.language_selector') }}"
                    >
                        <span class="inline-flex size-6 items-center justify-center overflow-hidden rounded-full"><img src="{{ asset('icons/flags/'.app()->getLocale().'.svg') }}" alt="" class="size-full object-cover"></span>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <x-heroicon-s-chevron-down class="size-3.5 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
                    </button>
                    <div
                        id="dashboard-language-menu"
                        data-ui-menu
                        role="menu"
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute right-0 top-full z-[70] mt-2 w-40 overflow-hidden border border-gray-100 bg-white py-1 shadow-xl"
                        style="display: none;"
                    >
                        @foreach(['vi', 'en'] as $language)
                            <a
                                role="menuitem"
                                href="{{ request()->fullUrlWithQuery(['lang' => $language]) }}"
                                @class([
                                    'flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors focus-visible:bg-futa-orange-soft focus-visible:outline-none',
                                    'bg-futa-orange-soft text-futa-orange' => app()->getLocale() === $language,
                                    'text-gray-700 hover:bg-gray-50' => app()->getLocale() !== $language,
                                ])
                            >
                                <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full"><img src="{{ asset('icons/flags/'.$language.'.svg') }}" alt="" class="size-full object-cover"></span>
                                <span class="flex-1">{{ __('Dashboard::app.language_'.$language) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @include('Notification::partials.bell')

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center space-x-3">
                    <span class="grid size-9 place-items-center rounded-full bg-futa-green/5 font-bold text-futa-green border-2 border-futa-green/20">
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-bold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ __('Dashboard::app.official_partner') }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="post" data-confirm data-confirm-title="{{ __('core::confirm.logout_title') }}" data-confirm-message="{{ __('core::confirm.logout_message') }}" class="inline-block">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-[#E31B23] transition p-1" title="{{ __('Dashboard::app.logout') }}">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="relative flex min-h-0 flex-1 overflow-hidden">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- SIDEBAR -->
        <aside class="futa-portal-sidebar fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col justify-between border-r transition-transform duration-200 ease-in-out lg:static lg:h-full lg:translate-x-0"
            :role="sidebarOpen && viewportWidth < 1024 ? 'dialog' : 'navigation'"
            :aria-modal="sidebarOpen && viewportWidth < 1024 ? 'true' : 'false'"
            aria-label="{{ __('Dashboard::app.operations') }}"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="futa-portal-sidebar-scroll min-h-0 flex-1 overflow-y-auto py-3">
                <div class="futa-portal-sidebar-label">{{ __('Dashboard::app.operations') }}</div>

                @php
                    $isBusSection = request()->routeIs('bus-management.*')
                        || (isset($section) && $section === 'buses');

                    $navItems = [
                        ['key' => 'overview', 'icon' => 'squares-2x2', 'route' => route('dashboard')],
                        ['key' => 'buses',    'icon' => 'truck',        'route' => '#', 'submenu' => [
                            ['key' => 'vehicle_types',  'icon' => 'squares-plus',            'route' => route('bus-management.vehicle-types.index')],
                            ['key' => 'bus_info',       'icon' => 'information-circle',      'route' => route('bus-management.buses.index')],
                            ['key' => 'document_types', 'icon' => 'clipboard-document-list', 'route' => route('bus-management.document-types.index')],
                            ['key' => 'bus_documents',  'icon' => 'folder-open',             'route' => route('bus-management.vehicle-documents.index')],
                        ]],
                        ['key' => 'route_catalog', 'icon' => 'map', 'route' => route('route-management.index'),
                            'permission' => 'route.view', 'label' => __('RouteManagement::app.title')],
                        ['key' => 'trips', 'icon' => 'map-pin', 'route' => route('trip-management.trips.index')],
                        ['key' => 'routes', 'icon' => 'calendar-days', 'route' => route('trip-management.schedules.index')],
                        ['key' => 'staff_accounts', 'icon' => 'users', 'route' => route('staff-accounts.index')],
                        ['key' => 'bookings', 'icon' => 'clipboard-document-list', 'route' => route('bookings.index'),
                            'permission' => 'booking.view', 'label' => __('BookingTracking::bookings.title')],
                        ['key' => 'refunds', 'icon' => 'receipt-refund', 'route' => route('refunds.index'),
                            'permission' => 'payment.view', 'label' => __('Payment::refunds.title')],
                        ['key' => 'exchanges', 'icon' => 'arrows-right-left', 'route' => route('exchange.manage'),
                            'permission' => 'ticket.exchange', 'label' => __('Payment::exchange.title')],
                        ['key' => 'access_management', 'icon' => 'key', 'route' => route('access-management.index')],
                        ['key' => 'reports', 'icon' => 'chart-bar',     'route' => route('reporting.index')],
                    ];
                @endphp

                <nav class="futa-portal-nav space-y-1" aria-label="{{ __('Dashboard::app.operations') }}">
                    @foreach($navItems as $item)
                        @continue(isset($item['permission']) && ! auth()->user()->hasPermissionTo($item['permission']))
                        @php
                            $active = ($item['key'] === 'overview' && request()->routeIs('dashboard'))
                                || ($item['key'] !== 'overview' && $item['key'] === ($section ?? null))
                                || ($item['key'] === 'reports' && request()->routeIs('reporting.*'))
                                || ($item['key'] === 'buses' && $isBusSection)
                                || ($item['key'] === 'trips' && request()->routeIs('trip-management.trips*'))
                                || ($item['key'] === 'route_catalog' && request()->routeIs('route-management.*'))
                                || ($item['key'] === 'routes' && request()->routeIs('trip-management.schedules*'))
                                || ($item['key'] === 'staff_accounts' && request()->routeIs('staff-accounts.*'))
                                || ($item['key'] === 'access_management' && request()->routeIs('access-management.*'))
                                || ($item['key'] === 'reports' && request()->routeIs('reporting.*'));
                            $hasSubmenu  = isset($item['submenu']);
                            $openDefault = ($item['key'] === 'buses' && $isBusSection) ? 'true' : 'false';
                        @endphp

                        <div x-data="{ open: {{ $openDefault }} }">

                            @if($hasSubmenu)
                                {{-- Parent item with submenu: render as <button> --}}
                                <button type="button" @click="open = !open" @class([
                                    'futa-portal-nav-link justify-between',
                                ]) @if($active) aria-current="page" @endif>
                                    <div class="flex items-center gap-2.5">
                                        {{-- SVG inline avoids :component binding on Blade component --}}
                                        <x-heroicon-o-truck class="w-5 h-5" />
                                        <span>{{ __('Dashboard::app.'.$item['key']) }}</span>
                                    </div>
                                    {{-- Chevron in plain span so Alpine :class works without Blade interference --}}
                                    <span class="inline-flex w-4 h-4 text-gray-400 transition-transform duration-200"
                                          :class="open ? 'rotate-180 text-futa-orange' : ''">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </button>

                                {{-- Submenu --}}
                                <div x-show="open"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="futa-portal-nav-submenu">
                                    <ul class="flex flex-col space-y-0.5 pt-1">
                                        @foreach($item['submenu'] as $sub)
                                            @php
                                                $subActive = ($sub['key'] === 'vehicle_types' && request()->routeIs('bus-management.vehicle-types*'))
                                                || ($sub['key'] === 'bus_info' && request()->routeIs('bus-management.buses*'))
                                                || ($sub['key'] === 'document_types' && request()->routeIs('bus-management.document-types*'))
                                                || ($sub['key'] === 'bus_documents' && request()->routeIs('bus-management.vehicle-documents*'));
                                            @endphp
                                            <li>
                                                <a href="{{ $sub['route'] }}" @class([
                                                    'futa-portal-nav-sublink',
                                                ]) @if($subActive) aria-current="page" @endif>
                                                    <x-dynamic-component :component="'heroicon-o-'.$sub['icon']" class="w-4 h-4 shrink-0" />
                                                    <span>{{ __('Dashboard::app.'.$sub['key']) }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                            @else
                                {{-- Regular nav item: render as <a> --}}
                                <a href="{{ $item['route'] }}" @class([
                                    'futa-portal-nav-link',
                                ]) @if($active) aria-current="page" @endif>
                                    <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="w-5 h-5" />
                                    <span>{{ $item['label'] ?? __('Dashboard::app.'.$item['key']) }}</span>
                                </a>
                            @endif

                        </div>
                    @endforeach
                </nav>

                <div class="futa-portal-sidebar-label">{{ __('Dashboard::app.system') }}</div>
                <nav class="futa-portal-nav space-y-1">
                    <a href="#" class="futa-portal-nav-link">
                        <x-heroicon-o-cog-8-tooth class="w-5 h-5" />
                        <span>{{ __('Dashboard::app.settings') }}</span>
                    </a>
                </nav>
            </div>

            @include('core::partials.portal-quick-lookup', ['lookupRoute' => 'bookings.index'])
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="min-h-0 flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 bg-[#F8F9FA]">
            @yield('content')
        </main>
    </div>
    @include('core::components.confirm-dialog')
    @if (auth()->user()?->hasPermissionTo('report.view'))
        @include('AiAssistant::partials.chat-widget')
    @endif
</body>
</html>

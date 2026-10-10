<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · FUTABUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'packages/FuteBus/Core/src/resources/css/app.css', 'packages/FuteBus/Core/src/resources/js/app.js', 'packages/FuteBus/Dashboard/src/resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js" defer></script>
</head>
<body class="dashboard-page flex min-h-screen flex-col bg-[#F8F9FA] font-[Inter,sans-serif] text-gray-800 antialiased" x-data="{ sidebarOpen: false }">
    @include('core::partials.global-loader')

    <!-- HEADER -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-futa-orange focus:outline-none">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="bg-futa-orange p-2 rounded-xl text-white shadow-md flex items-center justify-center">
                        <x-heroicon-o-truck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-xl tracking-tight text-futa-orange">FUTA</span>
                            <span class="text-sm font-bold text-gray-700 tracking-wider">BUS LINES</span>
                        </div>
                        <p class="text-[10px] text-futa-orange-dark font-semibold uppercase tracking-widest hidden sm:block">{{ __('Dashboard::app.slogan') }}</p>
                    </div>
                </a>
                <div class="hidden md:block h-6 w-px bg-gray-200 mx-2"></div>
                <span class="hidden md:block text-xs md:text-sm font-semibold bg-futa-orange-soft text-futa-orange px-3 py-1 rounded-full border border-futa-orange-soft">
                    {{ __('Dashboard::app.owner_portal') }}
                </span>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'vi' ? 'en' : 'vi']) }}" class="text-xs font-bold text-gray-600 hover:text-futa-orange">
                    {{ strtoupper(app()->getLocale()) }}
                </a>

                <button class="relative p-2 text-gray-500 hover:text-futa-orange hover:bg-futa-orange-soft rounded-full transition">
                    <x-heroicon-o-bell class="w-5 h-5" />
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#E31B23] rounded-full ring-2 ring-white"></span>
                </button>

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center space-x-3">
                    <span class="grid size-9 place-items-center rounded-full bg-futa-orange-soft font-bold text-futa-orange-dark border-2 border-futa-orange">
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

    <div class="flex flex-1 overflow-hidden relative">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- SIDEBAR -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform lg:translate-x-0 lg:static transition-transform duration-200 ease-in-out flex flex-col justify-between"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="py-4 overflow-y-auto">
                <div class="px-4 mb-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Dashboard::app.operations') }}</div>

                @php
                    $isBusSection = request()->routeIs('bus-management.*')
                        || (isset($section) && $section === 'buses');

                    $navItems = [
                        ['key' => 'overview', 'icon' => 'squares-2x2', 'route' => route('dashboard')],
                        ['key' => 'buses',    'icon' => 'truck',        'route' => '#', 'submenu' => [
                            ['key' => 'vehicle_types',  'route' => route('bus-management.vehicle-types.index')],
                            ['key' => 'bus_info', 'route' => route('bus-management.buses.index')],
                            ['key' => 'document_types', 'route' => route('bus-management.document-types.index')],
                            ['key' => 'bus_documents',  'route' => route('bus-management.vehicle-documents.index')],
                        ]],
                        ['key' => 'trips', 'icon' => 'map-pin', 'route' => route('trip-management.trips.index')],
                       ['key' => 'routes', 'icon' => 'calendar-days', 'route' => route('trip-management.schedules.index')],
                        ['key' => 'reports', 'icon' => 'chart-bar',     'route' => route('dashboard.section', 'reports')],
                    ];
                @endphp

                <nav class="space-y-1">
                    @foreach($navItems as $item)
                        @php
                            $active = $item['key'] === ($section ?? 'overview')
                                || ($item['key'] === 'buses' && $isBusSection)
                                || ($item['key'] === 'trips' && request()->routeIs('trip-management.trips*'))
                                || ($item['key'] === 'routes' && request()->routeIs('trip-management.schedules*'));
                            $hasSubmenu  = isset($item['submenu']);
                            $openDefault = ($item['key'] === 'buses' && $isBusSection) ? 'true' : 'false';
                        @endphp

                        <div x-data="{ open: {{ $openDefault }} }">

                            @if($hasSubmenu)
                                {{-- Parent item with submenu: render as <button> --}}
                                <button type="button" @click="open = !open" @class([
                                    'w-full flex items-center justify-between px-4 py-3 text-sm font-medium transition',
                                    'bg-futa-orange-soft text-futa-orange border-r-4 border-futa-orange font-semibold' => $active,
                                    'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => !$active,
                                ])>
                                    <div class="flex items-center space-x-3">
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
                                    class="bg-futa-orange-soft/50 pb-1">
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
                                                    'block pl-12 pr-4 py-2 text-sm transition rounded-r-lg',
                                                    'text-futa-orange font-bold bg-futa-orange-soft/60 border-r-2 border-futa-orange' => $subActive,
                                                    'text-gray-600 hover:text-futa-orange hover:bg-futa-orange-soft' => !$subActive,
                                                ])>
                                                    {{ __('Dashboard::app.'.$sub['key']) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                            @else
                                {{-- Regular nav item: render as <a> --}}
                                <a href="{{ $item['route'] }}" @class([
                                    'w-full flex items-center space-x-3 px-4 py-3 text-sm font-medium transition',
                                    'bg-futa-orange-soft text-futa-orange border-r-4 border-futa-orange font-semibold' => $active,
                                    'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => !$active,
                                ])>
                                    <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="w-5 h-5" />
                                    <span>{{ __('Dashboard::app.'.$item['key']) }}</span>
                                </a>
                            @endif

                        </div>
                    @endforeach
                </nav>

                <div class="px-4 mt-8 mb-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Dashboard::app.system') }}</div>
                <nav class="space-y-1">
                    <a href="#" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange transition border-r-4 border-transparent">
                        <x-heroicon-o-cog-8-tooth class="w-5 h-5" />
                        <span>{{ __('Dashboard::app.settings') }}</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 m-4 bg-futa-orange-soft rounded-xl border border-futa-orange-soft text-center">
                <x-heroicon-o-phone class="w-8 h-8 text-futa-orange mx-auto mb-2" />
                <p class="text-xs font-bold text-gray-800">{{ __('Dashboard::app.support_call_center') }}</p>
                <p class="text-sm font-extrabold text-futa-orange mt-1">1900 6067</p>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 bg-[#F8F9FA]">
            @yield('content')
        </main>
    </div>
    <x-confirm-dialog />
</body>
</html>

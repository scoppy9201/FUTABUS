<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · FUTABUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #f2652255; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #F26522; }
    </style>
</head>
<body class="bg-[#F8F9FA] text-gray-800 antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">

    <!-- HEADER -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-[#F26522] focus:outline-none">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-[#F26522] to-[#E31B23] p-2 rounded-xl text-white shadow-md flex items-center justify-center">
                        <x-heroicon-o-truck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-xl tracking-tight text-[#F26522]">FUTA</span>
                            <span class="text-sm font-bold text-gray-700 tracking-wider">BUS LINES</span>
                        </div>
                        <p class="text-[10px] text-[#E31B23] font-semibold uppercase tracking-widest hidden sm:block">{{ __('Dashboard::app.slogan') }}</p>
                    </div>
                </a>
                <div class="hidden md:block h-6 w-px bg-gray-200 mx-2"></div>
                <span class="hidden md:block text-xs md:text-sm font-semibold text-gray-600 bg-orange-50 text-[#F26522] px-3 py-1 rounded-full border border-orange-100">
                    {{ __('Dashboard::app.owner_portal') }}
                </span>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'vi' ? 'en' : 'vi']) }}" class="text-xs font-bold text-gray-600 hover:text-[#F26522]">
                    {{ strtoupper(app()->getLocale()) }}
                </a>

                <button class="relative p-2 text-gray-500 hover:text-[#F26522] hover:bg-orange-50 rounded-full transition">
                    <x-heroicon-o-bell class="w-5 h-5" />
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#E31B23] rounded-full ring-2 ring-white"></span>
                </button>

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center space-x-3">
                    <span class="grid size-9 place-items-center rounded-full bg-orange-100 font-bold text-[#d7461a] border-2 border-[#F26522]">
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
                <nav class="space-y-1">
                    @foreach([
                        ['key' => 'overview', 'icon' => 'squares-2x2', 'route' => route('dashboard')],
                        ['key' => 'buses', 'icon' => 'truck', 'route' => route('dashboard.section', 'buses'), 'submenu' => [
                            ['key' => 'vehicle_types', 'route' => '#'],
                            ['key' => 'bus_info',      'route' => '#'],
                            ['key' => 'document_types','route' => '#'],
                            ['key' => 'bus_documents', 'route' => '#'],
                        ]],
                        ['key' => 'trips',   'icon' => 'map-pin',      'route' => route('dashboard.section', 'trips')],
                        ['key' => 'routes',  'icon' => 'calendar-days', 'route' => route('dashboard.section', 'routes')],
                        ['key' => 'reports', 'icon' => 'chart-bar',     'route' => route('dashboard.section', 'reports')],
                    ] as $item)
                        @php($active = $item['key'] === ($section ?? 'overview'))
                        <div class="group relative">
                            <a href="{{ $item['route'] }}" @class([
                                'w-full flex items-center justify-between px-4 py-3 text-sm font-medium transition',
                                'bg-[#fff3ed] text-[#F26522] border-r-4 border-[#F26522] font-semibold' => $active,
                                'text-gray-600 group-hover:bg-[#fff3ed] group-hover:text-[#F26522]' => !$active
                            ])>
                                <div class="flex items-center space-x-3">
                                    <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="w-5 h-5" />
                                    <span>{{ __('Dashboard::app.'.$item['key']) }}</span>
                                </div>
                                @if(isset($item['submenu']))
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 group-hover:text-[#F26522] transition-transform duration-200 group-hover:rotate-180" />
                                @endif
                            </a>

                            @if(isset($item['submenu']))
                                <div class="hidden group-hover:block bg-orange-50/50 pb-2">
                                    <ul class="flex flex-col space-y-1">
                                        @foreach($item['submenu'] as $sub)
                                            <li>
                                                <a href="{{ $sub['route'] }}" class="block pl-12 pr-4 py-2 text-sm text-gray-600 hover:text-[#F26522] hover:bg-orange-50 transition">
                                                    {{ __('Dashboard::app.'.$sub['key']) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </nav>

                <div class="px-4 mt-8 mb-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Dashboard::app.system') }}</div>
                <nav class="space-y-1">
                    <a href="#" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-[#fff3ed] hover:text-[#F26522] transition border-r-4 border-transparent">
                        <x-heroicon-o-cog-8-tooth class="w-5 h-5" />
                        <span>{{ __('Dashboard::app.settings') }}</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 m-4 bg-orange-50 rounded-xl border border-orange-100 text-center">
                <x-heroicon-o-phone class="w-8 h-8 text-[#F26522] mx-auto mb-2" />
                <p class="text-xs font-bold text-gray-800">{{ __('Dashboard::app.support_call_center') }}</p>
                <p class="text-sm font-extrabold text-[#F26522] mt-1">1900 6067</p>
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

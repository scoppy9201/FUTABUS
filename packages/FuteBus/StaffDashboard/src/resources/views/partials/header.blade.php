    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shrink-0 shadow-sm">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="$dispatch('staff-navigation-toggle')" data-staff-menu-toggle aria-controls="staff-navigation-panel" aria-expanded="false" aria-label="{{ __('StaffDashboard::staff.menu') }}" class="lg:hidden text-gray-600 hover:text-futa-orange focus:outline-none">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
                <a href="{{ route($operatorHome ?? (Auth::user()->isBusCompany() ? 'dashboard' : 'staff.dashboard')) }}" class="flex items-center space-x-3">
                    <img src="{{ asset('favicon.png') }}" alt="FUTA Bus Lines" class="size-10 rounded-xl object-contain">
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
                    {{ $operatorRole ?? __('StaffDashboard::staff.role') }}
                </span>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                @include('StaffDashboard::partials.language-dropdown')

                <button class="relative p-2 text-gray-500 hover:text-futa-orange hover:bg-futa-orange-soft rounded-full transition">
                    <x-heroicon-o-bell class="w-5 h-5" />
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#E31B23] rounded-full ring-2 ring-white"></span>
                </button>

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center space-x-3">
                    <span class="grid size-9 place-items-center rounded-full bg-futa-orange-soft font-bold text-futa-orange-dark border-2 border-futa-orange">
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <a href="{{ route($operatorProfileRoute ?? 'staff.profile') }}" class="hidden sm:block text-left">
                        <p class="text-sm font-bold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ $operatorRole ?? __('StaffDashboard::staff.role') }}</p>
                    </a>
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

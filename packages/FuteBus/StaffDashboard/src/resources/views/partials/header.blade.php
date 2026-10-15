    <header class="futa-portal-header sticky top-0 z-30 shrink-0 bg-white">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="$dispatch('staff-navigation-toggle')" data-staff-menu-toggle aria-controls="staff-navigation-panel" aria-expanded="false" aria-label="{{ __('StaffDashboard::staff.menu') }}" class="lg:hidden text-gray-600 hover:text-futa-orange focus:outline-none">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>
                <a href="{{ route($operatorHome ?? (Auth::user()->isBusCompany() ? 'dashboard' : 'staff.dashboard')) }}" class="flex shrink-0 items-center">
                    @include('core::partials.portal-logo')
                </a>
                <div class="hidden md:block h-6 w-px bg-gray-200 mx-2"></div>
                <span class="hidden md:block text-xs md:text-sm font-semibold bg-futa-green/5 text-futa-green px-3 py-1 rounded-full border border-futa-green/10">
                    {{ $operatorRole ?? __('StaffDashboard::staff.role') }}
                </span>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                @include('StaffDashboard::partials.language-dropdown')

                @include('Notification::partials.bell')

                <div class="h-6 w-px bg-gray-200"></div>

                <div class="flex items-center space-x-3">
                    <span class="grid size-9 place-items-center rounded-full bg-futa-green/5 font-bold text-futa-green border-2 border-futa-green/20">
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

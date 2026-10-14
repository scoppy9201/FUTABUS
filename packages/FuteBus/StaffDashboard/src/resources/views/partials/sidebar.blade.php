<dialog id="staff-navigation-panel"
    x-data="staffNavigation"
    @staff-navigation-toggle.window="toggle()"
    @resize.window="syncViewport()"
    @close="syncExpanded()"
    @click="if ($event.target === $el) close()"
    aria-label="{{ __('StaffDashboard::staff.menu') }}"
    class="staff-navigation m-0 h-dvh max-h-none w-64 max-w-[85vw] border-0 border-r border-gray-200 bg-white p-0 text-gray-800 backdrop:bg-gray-900/50 lg:static lg:h-full lg:shrink-0 lg:open:flex lg:flex-col lg:justify-between">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 lg:hidden">
        <span class="text-sm font-semibold">{{ __('StaffDashboard::staff.menu') }}</span>
        <button type="button" @click="close()" aria-label="{{ __('StaffDashboard::staff.close_menu') }}" class="rounded-lg p-2 hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange"><x-heroicon-o-x-mark class="size-5" /></button>
    </div>
    <div class="py-4 overflow-y-auto">
        <div class="px-4 mb-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('StaffDashboard::staff.menu') }}</div>
                <nav id="staff-navigation" aria-label="{{ __('StaffDashboard::staff.title') }}" class="space-y-1">
                    <a
                        href="{{ route($sidebarHome ?? 'staff.dashboard') }}" @if(request()->routeIs($sidebarHome ?? 'staff.dashboard'))
                        aria-current="page" @endif @class(['w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                            'bg-futa-orange-soft text-futa-orange border-r-4 border-futa-orange font-semibold' => request()->routeIs($sidebarHome ?? 'staff.dashboard'),
                            'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => !request()->routeIs($sidebarHome ?? 'staff.dashboard')])>
                    <x-heroicon-o-home
                        class="size-5 shrink-0" /><span>{{ __('StaffDashboard::staff.home') }}</span></a>
                    <a
                        href="{{ route('staff.tickets') }}" @if(request()->routeIs('staff.tickets', 'staff.tickets.*'))
                        aria-current="page" @endif @class(['w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                            'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs('staff.tickets',
                            'staff.tickets.*'),
                            'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => !request()->routeIs('staff.tickets',
                            'staff.tickets.*')])>
                    <x-heroicon-o-ticket
                        class="size-5 shrink-0" /><span>{{ __('StaffDashboard::staff.ticket_menu') }}</span></a>
                    <a
                        href="{{ auth()->user()->hasPermissionTo('support.view') ? route('staff.support') : route($sidebarHome ?? 'staff.dashboard').'#support' }}"
                        @if(request()->routeIs('staff.support')) aria-current="page" @endif
                        @class(['w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                            'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs('staff.support'),
                            'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs('staff.support')])>
                    <x-heroicon-o-chat-bubble-left-right
                        class="size-5 shrink-0" /><span>{{ __('StaffDashboard::staff.support.title') }}</span></a>
                    <a href="{{ route('staff.ticket-lookup') }}"
                        @if(request()->routeIs('staff.ticket-lookup')) aria-current="page" @endif
                        @class(['w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                            'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs('staff.ticket-lookup'),
                            'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs('staff.ticket-lookup')])>
                        <x-heroicon-o-magnifying-glass class="size-5 shrink-0" /><span>{{ __('StaffDashboard::staff.tickets.actions.lookup') }}</span>
                    </a>
                    @if(auth()->user()->hasPermissionTo('payment.view'))
                        <a href="{{ route('staff.refunds.index') }}"
                            @if(request()->routeIs('staff.refunds.*')) aria-current="page" @endif
                            @class(['w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                                'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs('staff.refunds.*'),
                                'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs('staff.refunds.*')])>
                            <x-heroicon-o-receipt-refund class="size-5 shrink-0" /><span>{{ __('Payment::refunds.title') }}</span>
                        </a>
                    @endif
                    <template
                        x-for="item in dashboard?.navigation || []"
                        :key="item.key">
                    <a
                        :href="item.url"
                        class="flex items-center gap-3 border-r-4 border-transparent px-4 py-3 text-sm text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange">
                    <x-heroicon-o-rectangle-stack
                        class="size-5 shrink-0" />
                    <span
                        x-text="@js(__('StaffDashboard::staff.navigation'))[item.key]">
                    </span>
                    </a>
                    </template>
                </nav>

        <div class="px-4 mt-8 mb-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('StaffDashboard::staff.account') }}</div>
        <nav class="space-y-1">
            <a href="{{ route($operatorProfileRoute ?? 'staff.profile') }}"
                @if(request()->routeIs($operatorProfileRoute ?? 'staff.profile')) aria-current="page" @endif
                @class([
                    'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                    'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs($operatorProfileRoute ?? 'staff.profile'),
                    'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs($operatorProfileRoute ?? 'staff.profile'),
                ])>
                <x-heroicon-o-user-circle class="size-5 shrink-0" /><span>{{ __('Profile::app.title') }}</span>
            </a>
            <a href="{{ route($operatorPasswordRoute ?? 'staff.password') }}"
                @if(request()->routeIs($operatorPasswordRoute ?? 'staff.password')) aria-current="page" @endif
                @class([
                    'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition',
                    'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs($operatorPasswordRoute ?? 'staff.password'),
                    'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs($operatorPasswordRoute ?? 'staff.password'),
                ])>
                <x-heroicon-o-lock-closed class="size-5 shrink-0" /><span>{{ __('StaffDashboard::staff.change_password') }}</span>
            </a>
        </nav>
    </div>
</dialog>

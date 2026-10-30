<dialog id="staff-navigation-panel"
    x-data="staffNavigation"
    @staff-navigation-toggle.window="toggle()"
    @resize.window="syncViewport()"
    @close="syncExpanded()"
    @click="if ($event.target === $el) close()"
    aria-label="{{ __('StaffDashboard::staff.menu') }}"
    class="futa-portal-sidebar staff-navigation m-0 h-dvh max-h-none w-64 max-w-[85vw] border-0 border-r p-0 text-gray-800 open:flex open:flex-col backdrop:bg-gray-900/50 lg:static lg:h-full lg:shrink-0 lg:justify-between">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 lg:hidden">
        <span class="text-sm font-semibold">{{ __('StaffDashboard::staff.menu') }}</span>
        <button type="button" @click="close()" aria-label="{{ __('StaffDashboard::staff.close_menu') }}"
            class="rounded-lg p-2 hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
            <x-heroicon-o-x-mark class="size-5" />
        </button>
    </div>

    <div class="futa-portal-sidebar-scroll min-h-0 flex-1 overflow-y-auto py-3">
        <div class="futa-portal-sidebar-label">{{ __('StaffDashboard::staff.menu') }}</div>
        <nav id="staff-navigation" aria-label="{{ __('StaffDashboard::staff.title') }}" class="futa-portal-nav space-y-1">
            <a href="{{ route($sidebarHome ?? 'staff.dashboard') }}"
                @if(request()->routeIs($sidebarHome ?? 'staff.dashboard')) aria-current="page" @endif
                class="futa-portal-nav-link">
                <x-heroicon-o-home class="size-5 shrink-0" />
                <span>{{ __('StaffDashboard::staff.home') }}</span>
            </a>

            @if(app(\FuteBus\StaffTicketProcessing\Services\TicketAccessService::class)->allows(auth()->user()))
                <a href="{{ route('staff.tickets') }}"
                    @if(request()->routeIs('staff.tickets', 'staff.tickets.*', 'staff.exchange.*')) aria-current="page" @endif
                    class="futa-portal-nav-link">
                    <x-heroicon-o-ticket class="size-5 shrink-0" />
                    <span>{{ __('StaffDashboard::staff.ticket_menu') }}</span>
                </a>
            @else
                <button type="button" aria-disabled="true" title="{{ __('StaffDashboard::staff.ticket_access_hint') }}"
                    @click="void window.FutaNotify?.warning(@js(__('StaffDashboard::staff.ticket_access_hint')))"
                    class="futa-portal-nav-link">
                    <x-heroicon-o-ticket class="size-5 shrink-0" />
                    <span>{{ __('StaffDashboard::staff.ticket_menu') }}</span>
                    <x-heroicon-o-lock-closed class="ml-auto size-4 shrink-0" aria-hidden="true" />
                </button>
            @endif

            @foreach([
                ['route' => 'staff.support', 'active' => 'staff.support', 'permission' => 'support.view', 'icon' => 'chat-bubble-left-right',
                    'label' => __('StaffDashboard::staff.support.title'), 'hint' => __('StaffDashboard::staff.support_access_hint')],
                ['route' => 'staff.ticket-lookup', 'active' => 'staff.ticket-lookup', 'permission' => 'ticket.view', 'icon' => 'magnifying-glass',
                    'label' => __('StaffDashboard::staff.tickets.actions.lookup'), 'hint' => __('StaffDashboard::staff.lookup_access_hint')],
                ['route' => 'staff.bookings.index', 'active' => 'staff.bookings.*', 'permission' => 'booking.view', 'icon' => 'clipboard-document-list',
                    'label' => __('BookingTracking::bookings.title'), 'hint' => __('StaffDashboard::staff.booking_access_hint')],
                ['route' => 'staff.refunds.index', 'active' => 'staff.refunds.*', 'permission' => 'payment.view', 'icon' => 'receipt-refund',
                    'label' => __('Payment::refunds.title'), 'hint' => __('StaffDashboard::staff.refund_access_hint')],
            ] as $item)
                @include('StaffDashboard::partials.permission-link', ['item' => $item])
            @endforeach

            <template x-for="item in dashboard?.navigation || []" :key="item.key">
                <a :href="item.url" class="futa-portal-nav-link">
                    <x-heroicon-o-rectangle-stack class="size-5 shrink-0" />
                    <span x-text="@js(__('StaffDashboard::staff.navigation'))[item.key]"></span>
                </a>
            </template>
        </nav>

        <div class="futa-portal-sidebar-label">{{ __('StaffDashboard::staff.account') }}</div>
        <nav class="futa-portal-nav space-y-1" aria-label="{{ __('StaffDashboard::staff.account') }}">
            <a href="{{ route($operatorProfileRoute ?? 'staff.profile') }}"
                @if(request()->routeIs($operatorProfileRoute ?? 'staff.profile')) aria-current="page" @endif
                class="futa-portal-nav-link">
                <x-heroicon-o-user-circle class="size-5 shrink-0" />
                <span>{{ __('Profile::app.title') }}</span>
            </a>
            <a href="{{ route($operatorPasswordRoute ?? 'staff.password') }}"
                @if(request()->routeIs($operatorPasswordRoute ?? 'staff.password')) aria-current="page" @endif
                class="futa-portal-nav-link">
                <x-heroicon-o-lock-closed class="size-5 shrink-0" />
                <span>{{ __('StaffDashboard::staff.change_password') }}</span>
            </a>
        </nav>
    </div>
    @include('core::partials.portal-quick-lookup', ['lookupRoute' => 'staff.bookings.index'])
</dialog>

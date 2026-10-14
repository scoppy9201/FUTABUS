            <aside class="rounded-xl bg-white p-2 lg:sticky lg:top-24 lg:h-[calc(100dvh-7rem)] lg:self-start lg:overflow-y-auto">
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    :aria-expanded="sidebarOpen"
                    aria-controls="staff-navigation"
                    class="flex w-full items-center justify-between rounded-xl p-2 font-semibold text-gray-900 lg:hidden">{{ __('StaffDashboard::staff.menu') }}<x-heroicon-o-bars-3
                    class="size-6 shrink-0" />
                </button>
                <nav id="staff-navigation" aria-label="{{ __('StaffDashboard::staff.title') }}" :class="sidebarOpen ? 'flex' : 'hidden lg:flex'" class="flex-col gap-1">
                    <a
                        href="{{ route($sidebarHome ?? 'staff.dashboard') }}" @if(request()->routeIs($sidebarHome ?? 'staff.dashboard'))
                        aria-current="page" @endif @class(['flex shrink-0 items-center gap-3 rounded-xl px-3 py-3 font-semibold',
                            'bg-linear-to-r from-red-600 to-orange-500 text-white' => request()->routeIs($sidebarHome ?? 'staff.dashboard'),
                            'text-gray-900 hover:bg-futa-orange-soft' => !request()->routeIs($sidebarHome ?? 'staff.dashboard')])>
                    <x-heroicon-o-home
                        class="size-6 shrink-0" />{{ __('StaffDashboard::staff.home') }}</a>
                    <a
                        href="{{ route('staff.tickets') }}" @if(request()->routeIs('staff.tickets', 'staff.tickets.*'))
                        aria-current="page" @endif @class(['flex shrink-0 items-center gap-3 rounded-xl px-3 py-3',
                            'bg-linear-to-r from-red-600 to-orange-500 font-semibold text-white' => request()->routeIs('staff.tickets',
                            'staff.tickets.*'),
                            'text-gray-900 hover:bg-futa-orange-soft' => !request()->routeIs('staff.tickets',
                            'staff.tickets.*')])>
                    <x-heroicon-o-ticket
                        class="size-6 shrink-0" />{{ __('StaffDashboard::staff.ticket_menu') }}</a>
                    <a
                        href="{{ auth()->user()->hasPermissionTo('support.view') ? route('staff.support') : route($sidebarHome ?? 'staff.dashboard').'#support' }}"
                        @if(request()->routeIs('staff.support')) aria-current="page" @endif
                        @class(['flex shrink-0 items-center gap-3 rounded-xl px-3 py-3',
                            'bg-linear-to-r from-red-600 to-orange-500 font-semibold text-white' => request()->routeIs('staff.support'),
                            'text-gray-900 hover:bg-futa-orange-soft' => ! request()->routeIs('staff.support')])>
                    <x-heroicon-o-chat-bubble-left-right
                        class="size-6 shrink-0" />{{ __('StaffDashboard::staff.support.title') }}</a>
                    <a href="{{ route('staff.ticket-lookup') }}"
                        @if(request()->routeIs('staff.ticket-lookup')) aria-current="page" @endif
                        @class(['flex shrink-0 items-center gap-3 rounded-xl px-3 py-3',
                            'bg-linear-to-r from-red-600 to-orange-500 font-semibold text-white' => request()->routeIs('staff.ticket-lookup'),
                            'text-gray-900 hover:bg-futa-orange-soft' => ! request()->routeIs('staff.ticket-lookup')])>
                        <x-heroicon-o-magnifying-glass class="size-6 shrink-0" />{{ __('StaffDashboard::staff.tickets.actions.lookup') }}
                    </a>
                    @if(auth()->user()->hasPermissionTo('booking.view'))
                        <a href="{{ route('staff.bookings.index') }}"
                            @if(request()->routeIs('staff.bookings.*')) aria-current="page" @endif
                            @class(['flex items-center gap-3 rounded-xl px-3 py-3',
                                'bg-linear-to-r from-red-600 to-orange-500 font-semibold text-white' => request()->routeIs('staff.bookings.*'),
                                'text-gray-900 hover:bg-futa-orange-soft' => ! request()->routeIs('staff.bookings.*')])>
                            <x-heroicon-o-clipboard-document-list class="size-6 shrink-0" />{{ __('BookingTracking::bookings.title') }}
                        </a>
                    @endif
                    @if(auth()->user()->hasPermissionTo('payment.view'))
                        <a href="{{ route('staff.refunds.index') }}"
                            @if(request()->routeIs('staff.refunds.*')) aria-current="page" @endif
                            @class(['flex items-center gap-3 rounded-xl px-3 py-3',
                                'bg-linear-to-r from-red-600 to-orange-500 font-semibold text-white' => request()->routeIs('staff.refunds.*'),
                                'text-gray-900 hover:bg-futa-orange-soft' => ! request()->routeIs('staff.refunds.*')])>
                            <x-heroicon-o-receipt-refund class="size-6 shrink-0" />{{ __('Payment::refunds.title') }}
                        </a>
                    @else
                        <button type="button" aria-disabled="true" title="{{ __('StaffDashboard::staff.refund_access_hint') }}"
                            @click="window.FutaNotify?.warning(@js(__('StaffDashboard::staff.refund_access_hint')))"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-slate-400">
                            <x-heroicon-o-receipt-refund class="size-6 shrink-0" />{{ __('Payment::refunds.title') }}
                            <x-heroicon-o-lock-closed class="ml-auto size-4 shrink-0" aria-hidden="true" />
                        </button>
                    @endif
                    <template
                        x-for="item in dashboard?.navigation || []"
                        :key="item.key">
                    <a
                        :href="item.url"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-gray-900 hover:bg-futa-orange-soft">
                    <x-heroicon-o-rectangle-stack
                        class="size-6 shrink-0" />
                    <span
                        x-text="@js(__('StaffDashboard::staff.navigation'))[item.key]">
                    </span>
                    </a>
                    </template>
                </nav>
            </aside>

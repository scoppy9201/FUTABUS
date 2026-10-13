            <aside class="rounded-xl bg-white p-2">
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    :aria-expanded="sidebarOpen"
                    aria-controls="staff-navigation"
                    class="flex w-full items-center justify-between rounded-xl p-2 font-semibold text-gray-900 lg:hidden">{{ __('StaffDashboard::staff.menu') }}<x-heroicon-o-bars-3
                    class="size-6 shrink-0" />
                </button>
                <nav id="staff-navigation" aria-label="{{ __('StaffDashboard::staff.title') }}" :class="sidebarOpen ? 'flex' : 'hidden lg:flex'" class="flex-col gap-1 lg:sticky lg:top-4">
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
                        href="{{ route($sidebarHome ?? 'staff.dashboard') }}#support"
                        class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-3 text-gray-900 hover:bg-futa-orange-soft">
                    <x-heroicon-o-chat-bubble-left-right
                        class="size-6 shrink-0" />{{ __('StaffDashboard::staff.support.title') }}</a>
                    <a href="{{ route('staff.tickets') }}" class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-3 text-gray-900 hover:bg-futa-orange-soft"><x-heroicon-o-magnifying-glass class="size-6 shrink-0" />{{ __('StaffDashboard::staff.tickets.actions.lookup') }}</a>
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
                    <a href="{{ route($sidebarHome ?? 'staff.dashboard') }}#notifications" class="flex items-center gap-3 rounded-xl px-3 py-3 text-gray-900 hover:bg-futa-orange-soft"><x-heroicon-o-bell class="size-6 shrink-0" />{{ __('StaffDashboard::staff.notice_menu') }}</a>
                </nav>
            </aside>

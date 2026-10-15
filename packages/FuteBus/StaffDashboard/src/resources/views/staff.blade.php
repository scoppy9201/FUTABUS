@extends('StaffDashboard::layouts.staff')

@section('title', __('StaffDashboard::staff.title'))

@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush

@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" :aria-busy="loading" class="flex h-dvh min-h-0 flex-col overflow-hidden print:h-auto print:overflow-visible">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div
            class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0
            [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
            @include('StaffDashboard::partials.sidebar')
            <div class="min-h-0 min-w-0 overflow-y-auto p-4 md:p-6">
        <main class="relative flex min-w-0 flex-col gap-4 lg:gap-3">
            <section class="relative isolate flex flex-col overflow-hidden rounded-2xl bg-orange-50 lg:flex-row">
                <img src="{{ asset('images/banners/home-banner.jpg') }}" alt="{{ __('StaffDashboard::staff.banner_alt') }}" class="order-2 h-40 w-full object-fill lg:h-44 lg:w-1/2">
                <div class="order-1 flex min-h-40 items-center gap-4 p-5 lg:min-h-44 lg:w-1/2 lg:p-6">
                    <span
                        class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-full bg-white text-futa-orange">
                    <img
                        x-show="employee?.avatar_url" x-cloak
                        :src="employee?.avatar_url || null"
                        alt=""
                        class="size-20 object-cover">
                    <x-heroicon-o-user-circle
                        x-show="!employee?.avatar_url"
                        class="size-12" />
                    </span>
                    <div class="min-w-0">
                <h1 class="text-xl font-semibold text-gray-900">
                    {{ __('StaffDashboard::staff.greeting') }}<span x-show="employee" x-cloak>, <span class="uppercase text-futa-red" x-text="employee?.name"></span></span>!
                </h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('StaffDashboard::staff.workday') }}</p>
                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    <span class="flex items-center gap-2 rounded-xl bg-white px-4 py-2"><x-heroicon-o-calendar-days class="size-5 text-futa-orange" /><span x-text="dateLabel"></span></span>
                    <span class="flex items-center gap-2 rounded-xl bg-white px-4 py-2"><x-heroicon-o-clock class="size-5 text-futa-orange" /><span x-text="timeLabel"></span></span>
                </div>
                    </div>
                </div>
            </section>
            <p x-show="loading" role="status" class="absolute right-3 top-3 z-10 rounded-lg bg-white px-3 py-2 text-xs text-gray-600 shadow-sm">{{ __('StaffDashboard::staff.loading') }}</p>
            <div x-show="failed" x-cloak role="alert" class="absolute inset-x-3 top-3 z-10 rounded-xl border border-red-200 bg-red-50 p-4 text-futa-red">
                <p>{{ __('StaffDashboard::staff.error') }}</p>
                <button type="button" @click="load()" class="mt-3 rounded-full border border-current px-4 py-2 font-semibold focus-visible:outline-2">{{ __('StaffDashboard::staff.retry') }}</button>
            </div>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach(['trips_today' => 'truck', 'tickets_today' => 'ticket', 'support_requests' => 'chat-bubble-left-right', 'processing_requests' => 'clock'] as $metric => $icon)
                    <section
                        class="flex min-w-0 items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <span @class(['grid size-8 shrink-0 place-items-center rounded-full',
                        'bg-green-50 text-green-600' => $metric === 'support_requests',
                        'bg-orange-50 text-orange-500' => $metric === 'processing_requests',
                        'bg-rose-50 text-red-600' => in_array($metric,
                        ['trips_today',
                        'tickets_today'])])>
                    <x-dynamic-component
                        :component="'heroicon-o-'.$icon"
                        class="size-4 shrink-0" />
                    </span>
                    <div>
                    <h2
                        class="text-sm text-gray-500">{{ __('StaffDashboard::staff.metrics.'.$metric) }}</h2>
                    <p
                        class="mt-1 text-2xl font-bold"
                        x-text="dashboard?.statistics?.[@js($metric)] ?? 0">
                    </p>
                    </div>
                    </section>
                @endforeach
            </div>
                    <section id="tickets" class="flex min-h-0 flex-col scroll-mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <span class="sr-only">{{ __('StaffDashboard::staff.tickets.title') }}</span>
                        <h2 class="text-base font-bold text-gray-900">{{ __('StaffDashboard::staff.quick_actions') }}</h2>
                        <ul class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach(['sale' => ['ticket',
                                'bg-rose-50 text-red-600'],
                                'exchange' => ['arrows-right-left',
                                'bg-blue-50 text-blue-600'],
                                'cancellation' => ['arrow-path',
                                'bg-green-50 text-green-600'],
                                'support' => ['chat-bubble-left-right',
                                'bg-orange-50 text-orange-500']] as $action => [$actionIcon,
                                $actionColors])
                                <li class="relative flex min-h-20 flex-col justify-between rounded-xl p-3 lg:min-h-16 lg:flex-row lg:items-center lg:gap-3 {{ $actionColors }}">
                                    @if($action === 'sale')
                                        <button
                                            type="button"
                                            @click="groups?.tickets.sale ? window.location.assign(@js(route('staff.tickets.sale'))) : void window.FutaNotify.warning(@js(__('StaffTicketProcessing::sale.denied')), { duration: 4000 })"
                                            aria-label="{{ __('StaffDashboard::staff.tickets.actions.sale') }}"
                                            class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-futa-orange">
                                        </button>
                                    @endif
                                    @if($action === 'exchange' && auth()->user()->hasPermissionTo('ticket.view'))
                                        <a href="{{ route('staff.tickets.exchange') }}" class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-futa-orange" aria-label="{{ __('StaffDashboard::staff.tickets.actions.exchange') }}"></a>
                                    @endif
                                    @if($action === 'support' && auth()->user()->hasPermissionTo('support.view'))
                                        <a href="{{ route('staff.support') }}" aria-label="{{ __('StaffCustomerSupport::support.title') }}"
                                            class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-futa-orange"></a>
                                    @endif
                                    <x-dynamic-component :component="'heroicon-o-'.$actionIcon" class="size-6 shrink-0" />
                                    <div class="mt-2 flex items-end justify-between gap-2 lg:mt-0 lg:min-w-0 lg:flex-1">
                                        <div>
                                        <span
                                            class="block text-sm font-bold text-gray-900">{{ $action === 'support' ? __('StaffDashboard::staff.support.title') : __('StaffDashboard::staff.tickets.actions.'.$action) }}</span>
                                        <span
                                            class="sr-only">{{ $action === 'support' && auth()->user()->hasPermissionTo('support.view')
                                                ? __('StaffDashboard::staff.support.actions.requests') : __('StaffDashboard::staff.unavailable') }}</span>
                                        </div>
                                        <x-heroicon-o-arrow-right class="size-5 shrink-0 text-gray-900" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
            <div class="grid min-h-0 gap-3">
            <section id="support" class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                <div
                    class="flex items-center justify-between gap-2">
                <h2
                    class="flex items-center gap-2 text-base font-bold text-gray-900"><x-heroicon-o-chat-bubble-left-right class="size-5 text-futa-orange" />{{ __('StaffDashboard::staff.recent_support') }}</h2>
                @if(auth()->user()->hasPermissionTo('support.view'))
                    <a href="{{ route('staff.support') }}" class="text-xs font-semibold text-futa-orange">{{ __('StaffDashboard::staff.view_all') }}</a>
                @endif
                </div>
                <div
                    class="mt-3 overflow-x-auto rounded-lg border border-gray-100">
                <table
                    class="w-full min-w-160 table-fixed text-left text-sm text-gray-600">
                <colgroup>
                    <col class="w-40">
                    <col>
                    <col class="w-36">
                    <col class="w-40">
                    <col class="w-36">
                </colgroup>
                <thead>
                <tr
                    class="border-b border-gray-200 bg-gray-50">@foreach(['request', 'content', 'time', 'status', 'actions'] as $column)<th
                    @class(['whitespace-nowrap px-4 py-2 text-sm font-semibold text-gray-500', 'text-center' => in_array($column, ['time', 'status', 'actions'])])>{{ __('StaffDashboard::staff.'.$column) }}</th>@endforeach</tr>
                </thead>
                <tbody><template x-for="item in dashboard?.support_requests || []" :key="item.id">
                    <tr class="border-b border-gray-100 transition-colors last:border-0 hover:bg-futa-orange-soft/40">
                        <td class="px-4 py-2 font-semibold text-futa-orange" x-text="item.reference"></td>
                        <td class="max-w-64 truncate px-4 py-2 font-medium text-gray-800" :title="item.subject" x-text="item.subject"></td>
                        <td class="whitespace-nowrap px-4 py-2 text-center text-sm text-gray-500" x-text="new Date(item.created_at).toLocaleDateString(document.documentElement.lang)"></td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-sm font-semibold"
                                :class="({ waiting: 'bg-amber-50 text-amber-700', received: 'bg-blue-50 text-blue-700', reviewing: 'bg-futa-orange-soft text-futa-orange-dark', resolved: 'bg-green-50 text-green-700' })[item.status] || 'bg-gray-100 text-gray-600'">
                                <span x-text="(@js(__('Profile::feedback.status')))[item.status]"></span>
                            </span>
                        </td>
                        <td
                            class="px-4 py-2 text-center">
                            <a
                            :href="item.url"
                            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-futa-orange/20 px-2.5 py-1 text-sm font-semibold text-futa-orange transition
                            hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                            {{ __('StaffCustomerSupport::support.view') }}
                        </a></td>
                    </tr>
                </template></tbody>
                </table>
                </div>
                <x-core::empty-state x-show="!(dashboard?.support_requests?.length)" class="py-3 text-center text-xs text-gray-500"
                    :title="auth()->user()->hasPermissionTo('support.view') ? __('StaffCustomerSupport::support.empty') : __('StaffDashboard::staff.unavailable')"
                    compact />
            </section>
            </div>
        </main>

            </div>
        </div>
    </div>
@endsection

@extends('core::layouts.home')

@section('title', __('StaffDashboard::staff.title'))

@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush

@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" :aria-busy="loading" class="min-h-dvh lg:flex lg:flex-col">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div class="grid gap-2 bg-gray-50 p-2 text-sm lg:min-h-0 lg:flex-1 lg:grid-cols-[15%_minmax(0,1fr)]">
            @include('StaffDashboard::partials.sidebar')
            <div class="grid min-w-0 gap-2 lg:min-h-0 lg:grid-cols-[minmax(0,1fr)_20%]">
        <main class="relative grid min-w-0 gap-2 lg:min-h-0 lg:grid-rows-[minmax(0,1.5fr)_minmax(0,0.67fr)_minmax(0,1.17fr)_minmax(0,1.88fr)]">
            <section class="relative isolate min-h-40 overflow-hidden rounded-xl bg-orange-50 lg:min-h-0">
                <img src="{{ asset('images/banners/home-banner.jpg') }}" alt="{{ __('StaffDashboard::staff.banner_alt') }}" class="absolute inset-y-0 right-0 -z-20 h-full w-1/2 object-cover">
                <div class="absolute inset-0 -z-10 bg-linear-to-r from-orange-50 from-45% via-orange-50/70 via-55% to-transparent to-75%"></div>
                <div class="flex h-full items-center gap-4 p-4">
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
                <p class="mt-2 text-base text-gray-600">{{ __('StaffDashboard::staff.workday') }}</p>
                <div class="mt-3 flex gap-2 whitespace-nowrap text-sm">
                    <span class="flex items-center gap-2 rounded-xl bg-white px-3 py-2"><x-heroicon-o-calendar-days class="size-5 text-futa-orange" /><span x-text="dateLabel"></span></span>
                    <span class="flex items-center gap-2 rounded-xl bg-white px-3 py-2"><x-heroicon-o-clock class="size-5 text-futa-orange" /><span x-text="timeLabel"></span></span>
                </div>
                    </div>
                </div>
            </section>
            <p x-show="loading" role="status" class="absolute right-3 top-3 z-10 rounded-lg bg-white px-3 py-2 text-xs text-gray-600 shadow-sm">{{ __('StaffDashboard::staff.loading') }}</p>
            <div x-show="failed" x-cloak role="alert" class="absolute inset-x-3 top-3 z-10 rounded-xl border border-red-200 bg-red-50 p-4 text-futa-red">
                <p>{{ __('StaffDashboard::staff.error') }}</p>
                <button type="button" @click="load()" class="mt-3 rounded-full border border-current px-4 py-2 font-semibold focus-visible:outline-2">{{ __('StaffDashboard::staff.retry') }}</button>
            </div>
            <div class="grid grid-cols-2 gap-2 lg:min-h-0 lg:grid-cols-4">
                @foreach(['trips_today' => 'truck', 'tickets_today' => 'ticket', 'support_requests' => 'chat-bubble-left-right', 'processing_requests' => 'clock'] as $metric => $icon)
                    <section
                        class="flex min-w-0 items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
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
                    <section id="tickets" class="flex min-h-0 flex-col scroll-mt-4 rounded-xl border border-gray-200 bg-white p-3 shadow-sm ">
                        <span class="sr-only">{{ __('StaffDashboard::staff.tickets.title') }}</span>
                        <h2 class="text-base font-bold text-gray-900">{{ __('StaffDashboard::staff.quick_actions') }}</h2>
                        <ul class="mt-3 grid flex-1 gap-2 sm:grid-cols-2 lg:min-h-0 lg:grid-cols-4">
                            @foreach(['sale' => ['ticket',
                                'bg-rose-50 text-red-600'],
                                'exchange' => ['arrows-right-left',
                                'bg-blue-50 text-blue-600'],
                                'cancellation' => ['arrow-path',
                                'bg-green-50 text-green-600'],
                                'support' => ['chat-bubble-left-right',
                                'bg-orange-50 text-orange-500']] as $action => [$actionIcon,
                                $actionColors])
                                <li class="relative flex min-h-24 flex-col justify-between rounded-xl p-3 lg:min-h-0 {{ $actionColors }}">
                                    @if($action === 'sale')
                                        <button
                                            type="button"
                                            @click="groups?.tickets.sale ? window.location.assign(@js(route('staff.tickets.sale'))) : window.FutaNotify.warning(@js(__('StaffTicketProcessing::sale.denied')), { duration: 4000 })"
                                            aria-label="{{ __('StaffDashboard::staff.tickets.actions.sale') }}"
                                            class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-futa-orange">
                                        </button>
                                    @endif
                                    @if($action === 'support' && auth()->user()->hasPermissionTo('support.view'))
                                        <a href="{{ route('staff.support') }}" aria-label="{{ __('StaffCustomerSupport::support.title') }}"
                                            class="absolute inset-0 rounded-xl focus-visible:outline-2 focus-visible:outline-futa-orange"></a>
                                    @endif
                                    <x-dynamic-component :component="'heroicon-o-'.$actionIcon" class="size-6 shrink-0" />
                                    <div class="mt-2 flex items-end justify-between gap-2">
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
            <section id="support" class="min-w-0 rounded-xl border border-gray-200 bg-white p-3 shadow-sm ">
                <div
                    class="flex items-center justify-between gap-2">
                <h2
                    class="text-sm font-bold text-gray-900">{{ __('StaffDashboard::staff.recent_support') }}</h2>
                @if(auth()->user()->hasPermissionTo('support.view'))
                    <a href="{{ route('staff.support') }}" class="text-xs font-semibold text-futa-orange">{{ __('StaffDashboard::staff.view_all') }}</a>
                @endif
                </div>
                <div
                    class="mt-3 overflow-x-auto">
                <table
                    class="w-full text-left text-xs text-gray-500">
                <thead>
                <tr
                    class="border-b border-gray-200">@foreach(['request', 'content', 'time', 'status', 'actions'] as $column)<th
                    class="whitespace-nowrap px-1 pb-2">{{ __('StaffDashboard::staff.'.$column) }}</th>@endforeach</tr>
                </thead>
                <tbody><template x-for="item in dashboard?.support_requests || []" :key="item.id">
                    <tr class="border-b border-gray-100">
                        <td class="p-2 font-semibold text-futa-orange" x-text="item.reference"></td>
                        <td class="max-w-48 truncate p-2" x-text="item.subject"></td>
                        <td class="p-2" x-text="new Date(item.created_at).toLocaleDateString(document.documentElement.lang)"></td>
                        <td class="p-2" x-text="(@js(__('Profile::feedback.status')))[item.status]"></td>
                        <td class="p-2"><a :href="item.url" class="font-semibold text-futa-orange">{{ __('StaffCustomerSupport::support.view') }}</a></td>
                    </tr>
                </template></tbody>
                </table>
                </div>
                <p x-show="!(dashboard?.support_requests?.length)" class="py-3 text-center text-xs text-gray-500">
                    {{ auth()->user()->hasPermissionTo('support.view') ? __('StaffCustomerSupport::support.empty') : __('StaffDashboard::staff.unavailable') }}
                </p>
            </section>
            </div>
        </main>
                <aside class="flex flex-col gap-3 lg:min-h-0">
                    <section class="min-h-0 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="border-b border-gray-100 pb-2 font-bold text-gray-900">{{ __('StaffDashboard::staff.contact') }}</h2>
                        <div
                            class="mt-3 flex items-center gap-3">
                        <span
                            class="grid size-8 shrink-0 place-items-center rounded-full bg-rose-50 text-red-600">
                        <x-heroicon-s-phone
                            class="size-4 shrink-0" />
                        </span>
                        <div>
                        <a
                            href="tel:{{ preg_replace('/\s+/', '', __('core::app.home.footer.hotline')) }}"
                            class="font-semibold text-gray-900">{{ __('core::app.home.footer.hotline') }}</a>
                        <p
                            class="mt-1 text-xs text-gray-500">{{ __('core::app.home.footer.hotline_title') }}</p>
                        </div>
                        </div>
                        <div
                            class="mt-3 flex items-center gap-3">
                        <span
                            class="grid size-8 shrink-0 place-items-center rounded-full bg-rose-50 text-red-600">
                        <x-heroicon-s-envelope
                            class="size-4 shrink-0" />
                        </span>
                        <div
                            class="min-w-0">
                        <a
                            href="mailto:{{ __('core::app.home.footer.email') }}"
                            class="break-all font-semibold text-gray-900">{{ __('core::app.home.footer.email') }}</a>
                        <p
                            class="mt-1 text-xs text-gray-500">{{ __('StaffDashboard::staff.support.title') }}</p>
                        </div>
                        </div>
                        <div
                            class="mt-3 flex items-center gap-3">
                        <span
                            class="grid size-8 shrink-0 place-items-center rounded-full bg-rose-50 text-red-600">
                        <x-heroicon-o-clock
                            class="size-4 shrink-0" />
                        </span>
                        <div>
                        <p
                            class="font-semibold text-gray-900">{{ __('StaffDashboard::staff.call_hours') }}</p>
                        <p
                            class="mt-1 text-xs text-gray-500">{{ __('StaffDashboard::staff.call_hours_value') }}</p>
                        </div>
                        </div>
                    </section>
                    <div class="flex min-h-0 items-end overflow-hidden rounded-xl border border-gray-200 bg-white p-0 shadow-sm"><img src="{{ asset('images/about/futabus-fleet.png') }}" alt="{{ __('StaffDashboard::staff.fleet_alt') }}" class="max-h-40 w-full object-contain"></div>
                </aside>
            </div>
        </div>
    </div>
@endsection

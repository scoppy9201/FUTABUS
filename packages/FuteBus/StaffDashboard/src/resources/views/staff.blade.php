@extends('StaffDashboard::layouts.staff')
@section('title', __('StaffDashboard::staff.title'))

@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')), @js(route('api.v1.staff.activity')))" :aria-busy="loading" class="flex h-dvh min-h-0 flex-col overflow-hidden">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div class="grid min-h-0 min-w-0 flex-1 grid-cols-1 overflow-hidden lg:grid-cols-[16rem_minmax(0,1fr)]">
            @include('StaffDashboard::partials.sidebar')
            <main class="min-h-0 min-w-0 overflow-y-auto p-4 md:p-6 lg:p-8">
                <div class="mx-auto max-w-[1600px] space-y-6">
                    <header class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('StaffDashboard::workspace.title') }}</h1>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                            <span class="flex items-center gap-2"><x-heroicon-o-calendar-days class="size-4 text-futa-green" /><span x-text="dateLabel"></span></span>
                            <span class="border-l border-slate-200 pl-3 font-semibold tabular-nums" x-text="timeLabel"></span>
                            <button type="button" @click="load(); loadActivity()" :disabled="loading || activityLoading" aria-label="{{ __('StaffDashboard::workspace.refresh') }}"
                                class="rounded-lg border border-slate-200 bg-white p-2 hover:text-futa-green disabled:opacity-50">
                                <x-heroicon-o-arrow-path class="size-4" ::class="loading ? 'animate-spin motion-reduce:animate-none' : ''" />
                            </button>
                        </div>
                    </header>
                    <div x-show="failed" x-cloak role="alert" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <p>{{ __('StaffDashboard::staff.error') }}</p>
                        <button type="button" @click="load()" class="rounded-lg border border-red-200 bg-white px-4 py-2 font-semibold">{{ __('StaffDashboard::staff.retry') }}</button>
                    </div>
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.8fr)_minmax(280px,1fr)]">
                        <section class="staff-workbench-hero relative overflow-hidden rounded-2xl p-6 text-white sm:p-8">
                            <div class="relative z-10 grid h-full items-center gap-6 2xl:grid-cols-[minmax(0,1fr)_18rem]">
                                <div class="min-w-0">
                                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-white/70">
                                    <x-heroicon-o-building-office-2 class="size-4" />{{ __('StaffDashboard::workspace.hero_eyebrow') }}
                                </p>
                                <h2 class="mt-4 text-2xl font-bold leading-tight tracking-tight sm:text-3xl">
                                    {{ __('StaffDashboard::staff.greeting') }},
                                    <span class="block" x-text="employee?.name ?? @js(auth()->user()->name)"></span>
                                </h2>
                                <p class="mt-3 max-w-md text-sm leading-6 text-white/80">{{ __('StaffDashboard::workspace.hero_intro') }}</p>
                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if(auth()->user()->bus_company_id && auth()->user()->hasPermissionTo('booking.create'))
                                        <a href="{{ route('staff.tickets.sale') }}" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-3 text-sm font-semibold shadow-sm hover:bg-futa-orange-dark">
                                            <x-heroicon-o-plus class="size-5" />{{ __('StaffDashboard::staff.tickets.actions.sale') }}
                                        </a>
                                    @endif
                                    @if(auth()->user()->bus_company_id && auth()->user()->hasPermissionTo('ticket.view'))
                                        <a href="{{ route('staff.ticket-lookup') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold hover:bg-white/20">
                                            <x-heroicon-o-magnifying-glass class="size-5" />{{ __('StaffDashboard::staff.tickets.actions.lookup') }}
                                        </a>
                                    @endif
                                </div>
                                </div>
                                @include('StaffDashboard::partials.home.queue')
                            </div>
                        </section>
                        @include('StaffDashboard::partials.home.calendar')
                    </div>
                    <section aria-label="{{ __('StaffDashboard::workspace.statistics') }}" class="grid gap-4 sm:grid-cols-3">
                        @foreach(['tickets_today' => ['ticket', 'orange'], 'support_requests' => ['chat-bubble-left-right', 'green'], 'processing_requests' => ['clock', 'green']] as $metric => [$icon, $tone])
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
                                <div>
                                    <p class="text-sm text-slate-600">{{ __('StaffDashboard::staff.metrics.'.$metric) }}</p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums"
                                        x-text="loading ? '…' : (dashboard?.statistics?.[@js($metric)] ?? '—')"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-show="!loading && dashboard?.statistics?.[@js($metric)] == null">{{ __('StaffDashboard::staff.unavailable') }}</p>
                                </div>
                                <span @class(['grid size-12 shrink-0 place-items-center rounded-2xl', 'bg-futa-orange-soft text-futa-orange' => $tone === 'orange', 'bg-futa-green/5 text-futa-green' => $tone === 'green'])>
                                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-6" />
                                </span>
                            </div>
                        @endforeach
                    </section>
                    @include('StaffDashboard::partials.home.actions')
                    @include('StaffDashboard::partials.home.support')
                    <p x-show="loading" role="status" class="sr-only">{{ __('StaffDashboard::staff.loading') }}</p>
                </div>
            </main>
        </div>
    </div>
@endsection

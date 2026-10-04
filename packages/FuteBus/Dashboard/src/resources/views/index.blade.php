@extends('Dashboard::layouts.admin')

@section('title', __('Dashboard::app.overview'))
@section('page_title', __('Dashboard::app.overview'))

@section('content')
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#e94b19] via-[#f56a1e] to-[#ff8a1c] px-6 py-7 text-white shadow-lg shadow-orange-100 sm:px-8 sm:py-9">
        <div class="absolute -right-18 -top-25 size-70 rounded-full border-35 border-white/10" aria-hidden="true"></div>
        <div class="absolute bottom-0 right-20 size-32 rounded-t-full bg-white/5" aria-hidden="true"></div>
        <div class="relative flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-white/80">{{ __('Dashboard::app.owner_portal') }}</p>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('Dashboard::app.welcome', ['name' => Auth::user()->name]) }}</h1>
                <p class="mt-2 max-w-2xl text-sm font-medium text-white/90 sm:text-base">{{ __('Dashboard::app.intro') }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur">
                <span class="size-2 rounded-full bg-[#b7f4bb]"></span>
                {{ $company?->name ?? __('Dashboard::app.company_unavailable') }}
            </span>
        </div>
    </div>

    @if(! $company)
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-semibold text-amber-900">
            {{ __('Dashboard::app.company_unavailable_hint') }}
        </div>
    @endif

    <section class="mt-7" aria-labelledby="overview-metrics">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h2 id="overview-metrics" class="text-lg font-bold text-slate-950">{{ __('Dashboard::app.at_a_glance') }}</h2>
            <span class="text-sm font-medium text-slate-500">{{ __('Dashboard::app.live_database') }}</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['key' => 'paid_amount', 'value' => number_format($overview['paid_amount'], 0, ',', '.').' ₫', 'icon' => 'banknotes', 'tone' => 'bg-orange-50 text-[#ef5222]'],
                ['key' => 'booking_count', 'value' => number_format($overview['booking_count']), 'icon' => 'ticket', 'tone' => 'bg-sky-50 text-sky-600'],
                ['key' => 'upcoming_count', 'value' => number_format($overview['upcoming_count']), 'icon' => 'calendar-days', 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['key' => 'active_buses', 'value' => $overview['active_buses'].' / '.$overview['bus_count'], 'icon' => 'truck', 'tone' => 'bg-violet-50 text-violet-700'],
            ] as $metric)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-slate-500">{{ __('Dashboard::app.'.$metric['key']) }}</span>
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $metric['tone'] }}">
                            <x-dynamic-component :component="'heroicon-o-'.$metric['icon']" class="size-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-extrabold tracking-tight text-slate-950">{{ $metric['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mt-6 grid gap-5 2xl:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
        <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="recent-bookings">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="recent-bookings" class="text-lg font-bold text-slate-950">{{ __('Dashboard::app.recent_bookings') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Dashboard::app.recent_bookings_hint') }}</p>
                </div>
                <a href="{{ route('dashboard.section', 'bookings') }}" class="shrink-0 text-sm font-bold text-[#ef5222] hover:underline">{{ __('Dashboard::app.view_all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-135 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 sm:px-6">{{ __('Dashboard::app.booking_code') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Dashboard::app.customer') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Dashboard::app.route') }}</th>
                            <th scope="col" class="px-5 py-3 text-right sm:px-6">{{ __('Dashboard::app.status_label') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($overview['recent_bookings'] as $booking)
                            <tr>
                                <td class="px-5 py-4 font-bold text-slate-900 sm:px-6">{{ $booking->booking_code }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $booking->full_name }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $booking->origin_city }} → {{ $booking->destination_city }}</td>
                                <td class="px-5 py-4 text-right sm:px-6">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                        'bg-amber-50 text-amber-700' => $booking->status === 'pending',
                                        'bg-emerald-50 text-emerald-700' => in_array($booking->status, ['confirmed', 'completed'], true),
                                        'bg-rose-50 text-rose-700' => $booking->status === 'cancelled',
                                    ])>{{ __('Dashboard::app.status.'.$booking->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">{{ __('Dashboard::app.no_bookings') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="upcoming-trips">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="upcoming-trips" class="text-lg font-bold text-slate-950">{{ __('Dashboard::app.upcoming_trips') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Dashboard::app.upcoming_trips_hint') }}</p>
                </div>
                <a href="{{ route('dashboard.section', 'trips') }}" class="shrink-0 text-sm font-bold text-[#ef5222] hover:underline">{{ __('Dashboard::app.view_all') }}</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($overview['upcoming_trips'] as $trip)
                    <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-orange-50 text-[#ef5222]"><x-heroicon-o-truck class="size-6" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $trip->origin_city }} → {{ $trip->destination_city }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">{{ \Illuminate\Support\Carbon::parse($trip->departure_time)->format('H:i · d/m/Y') }} · {{ $trip->license_plate }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-bold text-slate-700">{{ $trip->available_seats ?? '—' }} {{ __('Dashboard::app.seats') }}</span>
                    </div>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-slate-500">{{ __('Dashboard::app.no_trips') }}</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="operation-shortcuts">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="operation-shortcuts" class="text-lg font-bold text-slate-950">{{ __('Dashboard::app.quick_access') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Dashboard::app.quick_access_hint') }}</p>
            </div>
            <span class="text-sm font-medium text-slate-500">{{ $overview['route_count'] }} {{ __('Dashboard::app.routes_count') }} · {{ $overview['pending_count'] }} {{ __('Dashboard::app.pending_count') }}</span>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach(['routes' => 'map', 'buses' => 'truck', 'bookings' => 'ticket'] as $key => $icon)
                <a href="{{ route('dashboard.section', $key) }}" class="group flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-4 transition hover:border-[#ef5222] hover:bg-orange-50/50">
                    <span class="flex items-center gap-3 text-sm font-bold text-slate-900">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5 text-[#ef5222]" />
                        {{ __('Dashboard::app.'.$key) }}
                    </span>
                    <x-heroicon-o-arrow-right class="size-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-[#ef5222]" />
                </a>
            @endforeach
        </div>
    </section>
@endsection

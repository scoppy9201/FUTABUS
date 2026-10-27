@extends('StaffDashboard::layouts.staff')
@section('title', __('StaffDashboard::staff.tickets.actions.exchange'))
@push('styles')
    @vite(['packages/FuteBus/StaffTicketProcessing/src/resources/css/app.css', 'packages/FuteBus/StaffTicketProcessing/src/resources/js/app.js', 'packages/Customer/TripSearch/src/resources/css/app.css', 'packages/Customer/BookingManagement/src/resources/css/app.css'])
@endpush
@section('content')
<div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" class="flex h-dvh min-h-0 flex-col overflow-hidden bg-gray-50 print:h-auto print:overflow-visible">
    @include('StaffDashboard::partials.header')
    @include('StaffDashboard::partials.notifications')
    <div
        class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0
        [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
        @include('StaffDashboard::partials.sidebar')
        <main
            x-data="staffTicketExchange(@js(route('api.v1.staff.tickets.index')),
            @js(['denied' => __('Payment::exchange.denied'),
            'error' => __('Payment::exchange.error'),
            'consent' => __('Payment::exchange.consent_error'),
            'expired' => __('Payment::exchange.expired')]),
            @js(['context' => route('staff.tickets.exchange.context',
            ['ticket' => '__TICKET__']),
            'csrf' => csrf_token()]))"
            @pagehide.window="flow.abandon()"
            class="min-w-0 space-y-5">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange"><x-heroicon-o-arrows-right-left class="size-7" /></span>
                    <div><h1 class="text-2xl font-bold text-futa-green">{{ __('StaffDashboard::staff.tickets.actions.exchange') }}</h1><p class="mt-1 text-slate-500">{{ __('StaffTicketProcessing::exchange.subtitle') }}</p></div>
                </div>
                <a href="{{ route('staff.tickets') }}" class="inline-flex items-center gap-2 font-semibold text-futa-orange hover:underline"><x-heroicon-o-arrow-left class="size-4" />{{ __('StaffTicketProcessing::sale.back_processing') }}</a>
            </header>
            <nav x-ref="exchangeProgress" aria-label="{{ __('StaffDashboard::staff.tickets.actions.exchange') }}" class="overflow-x-auto rounded-xl border border-gray-100 bg-white px-4 py-6">
                <ol class="grid min-w-160 grid-cols-4">
                    @foreach(__('StaffTicketProcessing::exchange.steps') as $step)
                        <li :aria-current="step === {{ $loop->iteration }} ? 'step' : null" :class="step >= {{ $loop->iteration }} ? 'text-futa-orange' : 'text-slate-400'" class="relative flex flex-col items-center gap-2 px-2 text-center">
                            @unless($loop->last)<span aria-hidden="true" class="absolute left-[calc(50%+1rem)] right-[calc(-50%+1rem)] top-3.5 h-px bg-slate-200"></span>@endunless
                            <span :class="step >= {{ $loop->iteration }} ? 'bg-futa-orange text-white' : 'bg-slate-100 text-slate-400'" class="relative grid size-7 place-items-center rounded-full text-xs font-bold">{{ $loop->iteration }}</span>
                            <span class="max-w-48 text-xs font-semibold leading-5 sm:text-sm">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </nav>
            @include('StaffTicketProcessing::partials.ticket-action-lookup')
            <section x-show="step === 2" x-cloak class="space-y-4" @staff-trip-selected.stop="chooseTrip($event.detail)" @staff-exchange-search.stop="searchTrips()">
                <div class="flex flex-wrap items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange"><x-heroicon-o-ticket class="size-6" /></span>
                        <div class="min-w-0 space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('StaffTicketProcessing::exchange.selected') }}</p>
                            <p class="break-all font-bold text-futa-green" x-text="selected?.code"></p>
                            <dl class="flex flex-wrap gap-x-6 gap-y-3 text-sm">
                                <div><dt class="text-xs text-slate-400">{{ __('StaffTicketProcessing::exchange.passenger') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.customer"></dd></div>
                                <div><dt class="text-xs text-slate-400">{{ __('StaffTicketProcessing::tickets.route') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.route"></dd></div>
                                <div><dt class="text-xs text-slate-400">{{ __('StaffTicketProcessing::exchange.departure') }}</dt><dd class="mt-1 font-semibold" x-text="flow.time(selected?.departure)"></dd></div>
                                <div><dt class="text-xs text-slate-400">{{ __('StaffTicketProcessing::tickets.seat') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.seat"></dd></div>
                            </dl>
                        </div>
                    </div>
                    <button type="button"
                        @click="back()"
                        :disabled="tripBusy"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-orange-200 px-4 py-2.5 text-sm font-semibold text-futa-orange hover:bg-orange-50 disabled:opacity-50">
                    <x-heroicon-o-arrow-left
                        class="size-4" />{{ __('StaffTicketProcessing::exchange.change_ticket') }}</button>
                </div>
                <div x-show="!tripSearched && !tripBusy" class="max-w-90">
                    @include('StaffTicketProcessing::partials.exchange-trip-search')
                </div>
                <p x-show="tripBusy" role="status" class="py-6 text-center text-slate-500">{{ __('StaffTicketProcessing::tickets.loading') }}</p>
                <div x-show="tripSearched" :class="tripBusy ? 'pointer-events-none opacity-60' : ''" :inert="tripBusy" x-html="resultsHtml"></div>
            </section>
            @include('StaffTicketProcessing::partials.exchange-details')
        </main>
    </div>
</div>
@endsection

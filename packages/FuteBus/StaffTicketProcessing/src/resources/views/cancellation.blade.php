@extends('StaffDashboard::layouts.staff')
@section('title', __('StaffDashboard::staff.tickets.actions.cancellation'))
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
        <main x-data="staffTicketCancellation(@js(route('api.v1.staff.tickets.index')), @js([
            'context' => route('staff.tickets.cancellation.context', ['ticket' => '__TICKET__']),
            'store' => route('staff.tickets.cancellation.store', ['ticket' => '__TICKET__']), 'csrf' => csrf_token(),
            'denied' => __('StaffTicketProcessing::cancellation.denied'), 'error' => __('StaffTicketProcessing::cancellation.error'),
        ]))" class="min-w-0 space-y-5">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange"><x-heroicon-o-x-circle class="size-7" /></span>
                    <div><h1 class="text-2xl font-bold text-futa-green">{{ __('StaffDashboard::staff.tickets.actions.cancellation') }}</h1><p class="mt-1 text-slate-500">{{ __('StaffTicketProcessing::cancellation.subtitle') }}</p></div>
                </div>
                <a href="{{ route('staff.tickets') }}" class="inline-flex items-center gap-2 font-semibold text-futa-orange hover:underline"><x-heroicon-o-arrow-left class="size-4" />{{ __('StaffTicketProcessing::sale.back_processing') }}</a>
            </header>
            <nav x-ref="progress" aria-label="{{ __('StaffDashboard::staff.tickets.actions.cancellation') }}" class="overflow-x-auto rounded-xl border border-gray-100 bg-white px-4 py-6">
                <ol class="grid min-w-160 grid-cols-4">
                    @foreach(__('StaffTicketProcessing::cancellation.steps') as $step)
                        <li :aria-current="step === {{ $loop->iteration }} ? 'step' : null" :class="step >= {{ $loop->iteration }} ? 'text-futa-orange' : 'text-slate-400'" class="relative flex flex-col items-center gap-2 px-2 text-center">
                            @unless($loop->last)<span aria-hidden="true" class="absolute left-[calc(50%+1rem)] right-[calc(-50%+1rem)] top-3.5 h-px bg-slate-200"></span>@endunless
                            <span :class="step >= {{ $loop->iteration }} ? 'bg-futa-orange text-white' : 'bg-slate-100 text-slate-400'" class="relative grid size-7 place-items-center rounded-full text-xs font-bold">{{ $loop->iteration }}</span>
                            <span class="max-w-48 text-xs font-semibold leading-5 sm:text-sm">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </nav>
            @include('StaffTicketProcessing::partials.ticket-action-lookup')
            @include('StaffTicketProcessing::partials.cancellation-details')
            <dialog x-ref="cancellationTicketDetail" aria-labelledby="cancellation-ticket-detail-title"
                @click="if ($event.target === $refs.cancellationTicketDetail) $refs.cancellationTicketDetail.close()"
                class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl border border-gray-100 bg-white p-6 shadow-xl backdrop:bg-gray-950/50">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h2 id="cancellation-ticket-detail-title" class="text-lg font-bold text-futa-green">{{ __('core::ticket-lookup.ticket_details') }}</h2>
                    <button type="button" autofocus @click="$refs.cancellationTicketDetail.close()"
                        aria-label="{{ __('core::booking.close') }}" class="rounded-lg p-1 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-futa-orange">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>
                <dl class="space-y-3 text-sm">
                    @foreach (['code' => 'ticket_code', 'customer' => 'passenger_name', 'phone' => 'passenger_phone', 'seat' => 'seat_code'] as $label => $field)
                        @if ($label === 'seat')
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.route') }}</dt>
                                <dd class="text-right font-semibold" x-text="selected ? selected.origin_city + ' → ' + selected.destination_city : '—'"></dd>
                            </div>
                            @foreach (['date' => 'dateStyle', 'time' => 'timeStyle'] as $labelPart => $style)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.'.$labelPart) }}</dt>
                                    <dd class="text-right font-semibold"
                                        x-text="selected?.departure_time ? new Intl.DateTimeFormat(document.documentElement.lang || 'vi', { {{ $style }}: 'short', timeZone: 'Asia/Ho_Chi_Minh' }).format(new Date(selected.departure_time.replace(' ', 'T'))) : '—'"></dd>
                                </div>
                            @endforeach
                        @endif
                        <div class="flex justify-between gap-4">
                            <dt class="shrink-0 text-slate-500">{{ __('StaffTicketProcessing::tickets.'.$label) }}</dt>
                            <dd class="min-w-0 break-words text-right font-semibold" x-text="selected?.{{ $field }} || '—'"></dd>
                        </div>
                    @endforeach
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ __('StaffDashboard::staff.status') }}</dt>
                        <dd x-text="rows.find(ticket => ticket.id === selected?.id)?.status_label || '—'"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ __('core::ticket-lookup.fare') }}</dt>
                        <dd class="font-bold text-futa-orange" x-text="money(selected?.price || 0)"></dd>
                    </div>
                </dl>
            </dialog>
        </main>
    </div>
</div>
@endsection

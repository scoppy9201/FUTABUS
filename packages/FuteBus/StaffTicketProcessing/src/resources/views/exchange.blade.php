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
                <ol class="grid min-w-160 grid-cols-5">
                    @foreach(__('StaffTicketProcessing::exchange.steps') as $step)
                        <li :aria-current="step === {{ $loop->iteration }} ? 'step' : null" :class="step >= {{ $loop->iteration }} ? 'text-futa-orange' : 'text-slate-400'" class="relative flex flex-col items-center gap-2 px-2 text-center">
                            @unless($loop->last)<span aria-hidden="true" class="absolute left-[calc(50%+1rem)] right-[calc(-50%+1rem)] top-3.5 h-px bg-slate-200"></span>@endunless
                            <span :class="step >= {{ $loop->iteration }} ? 'bg-futa-orange text-white' : 'bg-slate-100 text-slate-400'" class="relative grid size-7 place-items-center rounded-full text-xs font-bold">{{ $loop->iteration }}</span>
                            <span class="max-w-48 text-xs font-semibold leading-5 sm:text-sm">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </nav>
            <p x-show="Object.keys(flow.errors).length" x-cloak role="alert" class="rounded-xl bg-red-50 p-4 text-red-700" x-text="Object.values(flow.errors).join(' ')"></p>
            <div x-show="step === 1" class="space-y-5">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 font-bold text-slate-800">{{ __('StaffTicketProcessing::exchange.search_title') }}</h2>
                <form @submit.prevent="searchTickets()">
                    <fieldset class="mb-3 flex flex-wrap gap-x-7 gap-y-3" :disabled="loadingTickets">
                        <legend class="sr-only">{{ __('StaffTicketProcessing::exchange.search_title') }}</legend>
                        @foreach(['code', 'phone', 'name'] as $field)
                            <label
                                class="flex cursor-pointer items-center gap-2 text-sm">
                                <input
                                type="radio" name="exchange-search" value="{{ $field }}"
                                x-model="searchField"
                                class="size-4 accent-futa-orange">{{ $field === 'name' ? __('StaffTicketProcessing::exchange.name') : __('StaffTicketProcessing::tickets.'.$field) }}</label>
                        @endforeach
                    </fieldset>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <input
                                x-model="query"
                                :type="searchField === 'phone' ? 'tel' : 'text'"
                                :aria-label="searchField === 'code' ? @js(__('StaffTicketProcessing::tickets.code')) : searchField === 'phone' ? @js(__('StaffTicketProcessing::tickets.phone')) : @js(__('StaffTicketProcessing::exchange.name'))" required maxlength="255"
                                class="h-11 w-full rounded-lg border border-gray-200 pl-3 pr-11 outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
                            <button type="button" @click="query = ''; $el.previousElementSibling.focus()" aria-label="{{ __('StaffTicketProcessing::exchange.clear') }}" class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-futa-orange"><x-heroicon-o-x-circle class="size-5" /></button>
                        </div>
                        <button
                            :disabled="loadingTickets || !groups?.tickets.lookup || !query.trim()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-futa-orange px-10 font-semibold text-white hover:bg-futa-orange-dark disabled:opacity-50">
                            <x-heroicon-o-magnifying-glass
                            class="size-5" />{{ __('StaffTicketProcessing::exchange.search') }}</button>
                    </div>
                </form>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div
                    class="mb-4 flex items-center justify-between gap-3">
                    <h2
                    class="flex items-center gap-2 font-bold">
                    <x-heroicon-o-list-bullet
                    class="size-5" />{{ __('StaffTicketProcessing::exchange.results') }} (<span
                    x-text="meta.total">
                    </span> {{ __('StaffTicketProcessing::tickets.ticket_count') }})</h2>

                </div>
                <div
                    class="overflow-x-auto">
                <table
                    class="w-full whitespace-nowrap text-left text-sm">
                <thead
                    class="bg-gray-50 text-gray-500">
                <tr><th class="px-3 py-3">{{ __('StaffTicketProcessing::exchange.number') }}</th>@foreach(['code', 'customer', 'phone', 'route', 'date', 'time', 'seat'] as $column)<th
                    @class(['px-3 py-3', 'text-center' => in_array($column, ['date', 'time', 'seat'])])>{{ __('StaffTicketProcessing::tickets.'.$column) }}</th>@endforeach<th
                    class="px-3 py-3 text-center">{{ __('StaffDashboard::staff.status') }}</th>
                <th
                    class="px-3 py-3 text-center">{{ __('StaffDashboard::staff.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                    <tr x-show="!searched"><td colspan="10" class="h-32 px-4 text-center text-slate-500">{{ __('StaffTicketProcessing::exchange.initial') }}</td></tr>
                    <template x-for="(ticket, index) in rows" :key="ticket.id"><tr x-show="!loadingTickets" class="border-b border-gray-100 hover:bg-orange-50/40">
                        <td class="px-3 py-3 text-slate-500" x-text="(meta.from || 1) + index"></td>
                        <td
                            class="max-w-52 whitespace-normal break-all px-3 py-3 font-semibold"
                            >
                            <span class="block" x-text="ticket.code"></span>
                            <span class="mt-1 block text-xs font-normal text-slate-400" x-text="ticket.booking_code"></span>
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.customer">
                        </td>
                        <td
                            class="px-3 py-3"
                            x-text="ticket.phone || '—'">
                        </td>
                        <td
                            class="max-w-48 whitespace-normal px-3 py-3"
                            x-text="ticket.route">
                        </td>
                        <td
                            class="px-3 py-3 text-center whitespace-nowrap"
                            x-text="ticket.date">
                        </td>
                        <td
                            class="px-3 py-3 text-center whitespace-nowrap"
                            x-text="ticket.time">
                        </td>
                        <td
                            class="px-3 py-3 text-center"
                            x-text="ticket.seat || '—'">
                        </td>
                        <td
                            class="px-3 py-3 text-center">
                        <span
                            class="rounded-full px-2 py-1 font-medium"
                            :class="statusClass(ticket.display_status)"
                            x-text="ticket.status_label">
                        </span>
                        </td>
                        <td
                            class="px-3 py-3 text-center">
                        <button
                            type="button"
                            @click="chooseTicket(ticket)" :disabled="!ticket.usable || loadingTickets || choosing"
                           
                                class="rounded-md border border-futa-orange px-4 py-1 font-semibold text-futa-orange hover:bg-futa-orange-soft disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400">
                                <span
                                x-text="ticket.usable ? @js(__('StaffTicketProcessing::exchange.select')) : @js(__('StaffTicketProcessing::exchange.unavailable'))">
                                </span>
                                </button>
                        </td>
                    </tr></template>
                    <tr x-show="loadingTickets"><td colspan="10" class="h-32 px-4 text-center text-gray-500" role="status">{{ __('StaffTicketProcessing::tickets.loading') }}</td></tr>
                    <tr x-show="searched && !loadingTickets && !rows.length"><td colspan="10" class="h-32 px-4 text-center text-gray-500">
                        <span
                            x-show="!groups?.tickets.lookup">{{ __('StaffTicketProcessing::tickets.denied') }}</span>
                        <x-core::empty-state x-show="groups?.tickets.lookup && !listFailed" :title="__('StaffTicketProcessing::tickets.empty')" compact />
                        <button
                            x-show="groups?.tickets.lookup && listFailed"
                            @click="loadTickets()"
                            type="button"
                            class="text-futa-orange">{{ __('StaffDashboard::staff.retry') }}</button>
                    </td></tr>
                </tbody></table></div>
                <x-core::data-pagination meta="meta" navigate="loadTickets" busy="loadingTickets"
                    x-cloak x-show="searched && !listFailed && groups?.tickets.lookup" class="mt-4" />

            </section>
            </div>
            <section x-show="step === 2" x-cloak class="space-y-4" @staff-trip-selected.stop="chooseTrip($event.detail)">
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4">
                    <p class="font-semibold text-futa-green">{{ __('StaffTicketProcessing::exchange.selected') }}: <span x-text="selected?.code"></span> · <span x-text="selected?.customer"></span></p>
                    <button type="button" @click="back()" :disabled="tripBusy" class="inline-flex items-center gap-2 font-semibold text-futa-orange disabled:opacity-50"><x-heroicon-o-arrow-left class="size-4" />{{ __('StaffTicketProcessing::exchange.steps')[0] }}</button>
                </div>
                <div x-ref="customerSearch" :class="tripBusy ? 'pointer-events-none opacity-60' : ''" :inert="tripBusy">
                    @include('StaffTicketProcessing::partials.search-form', ['bookingLocations' => [], 'searchCriteria' => [], 'exchangeSearch' => true])
                </div>
                <p x-show="tripBusy" role="status" class="py-6 text-center text-slate-500">{{ __('StaffTicketProcessing::tickets.loading') }}</p>
                <div x-show="tripSearched && !tripBusy" class="mx-auto w-full max-w-282 px-3 sm:px-4" x-html="resultsHtml"></div>
            </section>
            @include('StaffTicketProcessing::partials.exchange-details')
        </main>
    </div>
</div>
@endsection

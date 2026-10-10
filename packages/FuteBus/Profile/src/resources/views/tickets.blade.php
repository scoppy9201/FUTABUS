@extends('core::layouts.home')

@section('title', __('Profile::tickets.title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section class="min-w-0" aria-labelledby="ticket-history-title">
                <div class="flex flex-wrap items-start justify-between gap-5">
                    <div>
                        <h1 id="ticket-history-title" class="text-3xl font-semibold text-gray-950">{{ __('Profile::tickets.title') }}</h1>
                        <p class="mt-2 text-base font-medium text-slate-600">{{ __('Profile::tickets.description') }}</p>
                    </div>
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex min-h-11 min-w-32 items-center justify-center rounded-full bg-futa-orange px-6 text-base font-semibold text-white shadow-sm transition hover:bg-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                    >
                        {{ __('Profile::tickets.book') }}
                    </a>
                </div>

                <form
                    action="{{ route('profile.tickets.index') }}"
                    method="get"
                    class="mt-8 grid gap-3 rounded-2xl border border-gray-200 bg-futa-orange-soft/40 p-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,1.2fr)_minmax(0,1fr)_auto] xl:items-end"
                >
                    <div class="min-w-0">
                        <label for="ticket-code" class="mb-1.5 block truncate text-sm font-semibold text-gray-900">{{ __('Profile::tickets.fields.code') }}</label>
                        <div class="relative">
                            <x-heroicon-o-qr-code class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-futa-orange" />
                            <input
                                id="ticket-code" name="code" type="search" maxlength="100"
                                value="{{ $filters['code'] ?? '' }}"
                                placeholder="{{ __('Profile::tickets.placeholders.code') }}"
                                class="h-11 w-full min-w-0 rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm font-medium text-gray-950 placeholder:text-slate-400 focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/10"
                            >
                        </div>
                    </div>
                    <div data-profile-date-picker class="relative min-w-0">
                        <span id="ticket-date-label" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::tickets.fields.date') }}</span>
                        <input
                            id="ticket-date" name="date" type="hidden"
                            value="{{ $filters['date'] ?? '' }}"
                            data-profile-date-value
                        >
                        <button
                            type="button"
                            data-profile-date-toggle
                            aria-labelledby="ticket-date-label ticket-date-display"
                            aria-haspopup="dialog"
                            aria-controls="ticket-date-calendar"
                            aria-expanded="false"
                            class="flex h-11 w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 text-left text-sm font-medium text-gray-950 hover:border-futa-orange focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                        >
                            <span id="ticket-date-display" data-profile-date-label class="truncate">{{ __('Profile::tickets.placeholders.date') }}</span>
                            <x-heroicon-o-calendar-days class="size-5 shrink-0 text-futa-orange" />
                        </button>
                        <div
                            id="ticket-date-calendar"
                            data-profile-calendar
                            hidden
                            role="dialog"
                            aria-label="{{ __('Profile::tickets.fields.date') }}"
                            class="absolute left-0 top-full z-40 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 text-gray-900 shadow-xl"
                        >
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <button type="button" data-profile-date-previous aria-label="{{ __('Profile::app.previous_month') }}" class="grid size-8 place-items-center rounded-full text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                    <x-heroicon-o-chevron-left class="size-4" />
                                </button>
                                <span data-profile-date-month class="text-sm font-semibold text-gray-950"></span>
                                <input
                                    type="number"
                                    data-profile-date-year
                                    aria-label="{{ __('Profile::app.year') }}"
                                    min="1000" max="2100"
                                    class="w-17 rounded-md border border-gray-200 px-1 py-1 text-center text-sm font-semibold text-gray-950 focus:border-futa-orange focus:outline-none"
                                >
                                <button type="button" data-profile-date-next aria-label="{{ __('Profile::app.next_month') }}" class="grid size-8 place-items-center rounded-full text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                    <x-heroicon-o-chevron-right class="size-4" />
                                </button>
                            </div>
                            <div data-profile-date-weekdays class="grid grid-cols-7 text-center text-xs font-semibold text-slate-500"></div>
                            <div data-profile-date-days class="mt-1 grid grid-cols-7 gap-0.5"></div>
                            <button type="button" data-profile-date-clear class="mt-3 w-full rounded-lg py-1.5 text-sm font-semibold text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                {{ __('Profile::app.clear_date') }}
                            </button>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <label for="ticket-route" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::tickets.fields.route') }}</label>
                        <div class="relative">
                            <x-heroicon-o-map-pin class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-futa-orange" />
                            <input
                                id="ticket-route" name="route" type="search" maxlength="100"
                                value="{{ $filters['route'] ?? '' }}"
                                placeholder="{{ __('Profile::tickets.placeholders.route') }}"
                                class="h-11 w-full min-w-0 rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm font-medium text-gray-950 placeholder:text-slate-400 focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/10"
                            >
                        </div>
                    </div>
                    <div data-ticket-status-picker class="relative min-w-0">
                        <span id="ticket-status-label" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::tickets.fields.status') }}</span>
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}" data-ticket-status-value>
                        <button
                            type="button"
                            data-ticket-status-toggle
                            aria-labelledby="ticket-status-label ticket-status-display"
                            aria-haspopup="listbox"
                            aria-controls="ticket-status-options"
                            aria-expanded="false"
                            class="flex h-11 w-full min-w-0 items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 text-left text-sm font-medium text-gray-950 hover:border-futa-orange focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                        >
                            <span id="ticket-status-display" data-ticket-status-label class="truncate">
                                {{ filled($filters['status'] ?? null) ? __('Profile::tickets.filter_status.'.$filters['status']) : __('Profile::tickets.placeholders.status') }}
                            </span>
                            <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-500" />
                        </button>
                        <div
                            id="ticket-status-options"
                            data-ticket-status-options
                            role="listbox"
                            aria-label="{{ __('Profile::tickets.fields.status') }}"
                            hidden
                            class="absolute left-0 top-full z-40 mt-2 max-h-64 w-full min-w-48 overflow-y-auto rounded-xl border border-gray-200 bg-white py-1 shadow-xl"
                        >
                            <button
                                type="button" role="option" tabindex="-1" data-ticket-status-option=""
                                aria-selected="{{ empty($filters['status']) ? 'true' : 'false' }}"
                                class="block w-full px-3 py-2.5 text-left text-sm font-medium text-gray-900 hover:bg-futa-orange-soft focus:bg-futa-orange-soft focus:outline-none aria-selected:bg-futa-orange-soft aria-selected:font-semibold aria-selected:text-futa-orange"
                            >{{ __('Profile::tickets.placeholders.status') }}</button>
                            @foreach(\FuteBus\Profile\Http\Requests\TicketHistoryRequest::STATUS_OPTIONS as $status)
                                <button
                                    type="button" role="option" tabindex="-1" data-ticket-status-option="{{ $status }}"
                                    aria-selected="{{ ($filters['status'] ?? '') === $status ? 'true' : 'false' }}"
                                    class="block w-full px-3 py-2.5 text-left text-sm font-medium text-gray-900 hover:bg-futa-orange-soft focus:bg-futa-orange-soft focus:outline-none aria-selected:bg-futa-orange-soft aria-selected:font-semibold aria-selected:text-futa-orange"
                                >{{ __('Profile::tickets.filter_status.'.$status) }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex h-11 min-w-24 items-center justify-center rounded-full border border-futa-orange
                            bg-white px-4 text-sm font-semibold text-futa-orange transition hover:bg-futa-orange
                            hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2
                            focus-visible:outline-futa-orange sm:col-span-2 xl:col-span-1"
                    >
                        <x-heroicon-o-magnifying-glass class="mr-2 size-5" />{{ __('Profile::tickets.search') }}
                    </button>
                </form>

                @if($errors->any())
                    <div role="alert" class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <div class="mt-7 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4">
                        <p class="text-sm font-semibold text-slate-600">{{ __('Profile::tickets.results', ['count' => $bookings->total()]) }}</p>
                        @if($hasFilters)
                            <a href="{{ route('profile.tickets.index') }}" class="text-sm font-semibold text-futa-orange hover:underline">{{ __('Profile::tickets.clear') }}</a>
                        @endif
                    </div>
                    <div
                        class="overflow-x-auto lg:[scrollbar-width:none] lg:hover:[scrollbar-width:thin]
                            lg:hover:[scrollbar-color:#ef5222_#f3f4f6] lg:focus-within:[scrollbar-width:thin]
                            lg:focus-within:[scrollbar-color:#ef5222_#f3f4f6]
                            lg:[&::-webkit-scrollbar]:h-0 lg:hover:[&::-webkit-scrollbar]:h-2
                            lg:focus-within:[&::-webkit-scrollbar]:h-2"
                    >
                        <table class="w-full min-w-245 text-left text-sm">
                            <caption class="sr-only">{{ __('Profile::tickets.title') }}</caption>
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    @foreach(['code', 'count', 'route', 'departure', 'amount', 'payment', 'status', 'action'] as $column)
                                        <th scope="col" class="whitespace-nowrap px-4 py-3.5 font-semibold">{{ __('Profile::tickets.columns.'.$column) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($bookings as $booking)
                                    @php
                                        $bookingColor = match ($booking->status) {
                                            'confirmed', 'completed' => 'bg-green-50 text-green-700',
                                            'cancelled' => 'bg-red-50 text-red-700',
                                            default => 'bg-amber-50 text-amber-800',
                                        };
                                        $paymentColor = match ($booking->payment_status) {
                                            'completed' => 'text-green-700',
                                            'failed', 'refunded' => 'text-red-700',
                                            default => 'text-slate-600',
                                        };
                                    @endphp
                                    <tr class="align-top hover:bg-futa-orange-soft/30">
                                        <td class="px-4 py-4 font-semibold text-futa-orange">{{ $booking->booking_code }}</td>
                                        <td class="px-4 py-4 text-gray-900">{{ $booking->seat_count }}</td>
                                        <td class="px-4 py-4 font-medium text-gray-950">{{ $booking->origin_city }} <span aria-hidden="true">→</span> {{ $booking->destination_city }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-gray-800">{{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 font-semibold text-gray-950">{{ __('Profile::tickets.currency', ['amount' => number_format((float) $booking->total_amount, 0, ',', '.')]) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 font-medium {{ $paymentColor }}">{{ __('Profile::tickets.payment_status.'.($booking->payment_status ?? 'unpaid')) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $bookingColor }}">{{ __('Profile::tickets.booking_status.'.$booking->status) }}</span></td>
                                        <td class="whitespace-nowrap px-4 py-4">
                                            <a href="{{ route('profile.tickets.show', $booking->id) }}" class="font-semibold text-futa-orange hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                                                {{ __('Profile::tickets.view_details') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-14 text-center">
                                            <div class="mx-auto grid size-14 place-items-center rounded-full bg-futa-orange-soft text-futa-orange"><x-heroicon-o-ticket class="size-7" /></div>
                                            <p class="mt-4 text-lg font-semibold text-gray-950">{{ __($hasFilters ? 'Profile::tickets.filtered_empty_title' : 'Profile::tickets.empty_title') }}</p>
                                            <p class="mt-1 text-sm text-slate-600">{{ __($hasFilters ? 'Profile::tickets.filtered_empty_description' : 'Profile::tickets.empty_description') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($bookings->hasPages())
                    <div class="mt-6">{{ $bookings->links() }}</div>
                @endif
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection

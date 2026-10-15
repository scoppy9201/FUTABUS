@extends('core::layouts.home')

@section('title', __('Profile::tickets.title'))

@section('content')
    <div class="home-page ticket-history-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section class="min-w-0 lg:flex lg:min-h-134 lg:flex-col" aria-labelledby="ticket-history-title">
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
                    <div class="min-w-0">
                        <label for="ticket-status" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::tickets.fields.status') }}</label>
                        <select id="ticket-status" name="status"
                            class="h-11 w-full min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-950 hover:border-futa-orange focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/10">
                            <option value="">{{ __('Profile::tickets.placeholders.status') }}</option>
                            @foreach(\FuteBus\Profile\Http\Requests\TicketHistoryRequest::STATUS_OPTIONS as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('Profile::tickets.filter_status.'.$status) }}</option>
                            @endforeach
                        </select>
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

                <div class="mt-5 rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4">
                        <div>
                            <p class="text-sm font-semibold text-futa-green">{{ __('Profile::tickets.results', ['count' => $bookings->total()]) }}</p>
                            @if($bookings->count() > 0)
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Profile::tickets.change_policy_note') }}</p>
                            @endif
                        </div>
                        @if($hasFilters)
                            <a href="{{ route('profile.tickets.index') }}" class="text-sm font-semibold text-futa-orange hover:underline">{{ __('Profile::tickets.clear') }}</a>
                        @endif
                    </div>
                    <div
                        tabindex="0" aria-label="{{ __('Profile::tickets.title') }}"
                        class="ticket-history-scroll max-h-96 overflow-auto focus-visible:-outline-offset-2 focus-visible:outline-2 focus-visible:outline-futa-orange"
                    >
                        <table class="w-full min-w-245 text-left text-sm">
                            <caption class="sr-only">{{ __('Profile::tickets.title') }}</caption>
                            <thead class="sticky top-0 z-10 bg-futa-orange-soft text-gray-700">
                                <tr>
                                    @foreach(['code', 'count', 'route', 'departure', 'amount', 'payment', 'status', 'action'] as $column)
                                        <th scope="col" class="whitespace-nowrap px-4 py-3.5 font-semibold">{{ __('Profile::tickets.columns.'.$column) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($bookings as $booking)
                                    @php
                                        $bookingColor = match ($booking->journey_stage) {
                                            'arrived', 'rated' => 'bg-green-50 text-green-700',
                                            'cancelled', 'refunded', 'payment_failed' => 'bg-red-50 text-red-700',
                                            'departed' => 'bg-futa-orange-soft text-futa-orange',
                                            'paid' => 'bg-blue-50 text-blue-700',
                                            default => 'bg-amber-50 text-amber-800',
                                        };
                                        $paymentColor = match ($booking->payment_status) {
                                            'completed' => 'text-green-700',
                                            'failed', 'refunded' => 'text-red-700',
                                            default => 'text-slate-600',
                                        };
                                    @endphp
                                    <tr class="align-middle transition-colors hover:bg-futa-orange-soft/30">
                                        <td class="whitespace-nowrap px-4 py-4 font-semibold text-futa-orange">
                                            <span>{{ $booking->booking_code }}</span>
                                            @if(str_starts_with($booking->booking_code, 'DEMOHIST'))
                                                <span class="ml-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 align-middle text-[11px] font-semibold text-slate-600">{{ __('Profile::tickets.demo_label') }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-gray-900">{{ $booking->seat_count }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 font-medium text-gray-950">{{ $booking->origin_city }} <span aria-hidden="true">→</span> {{ $booking->destination_city }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-gray-800">{{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 font-semibold text-gray-950">{{ __('Profile::tickets.currency', ['amount' => number_format((float) $booking->total_amount, 0, ',', '.')]) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 font-medium {{ $paymentColor }}">{{ __('Profile::tickets.payment_status.'.($booking->payment_status ?? 'unpaid')) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4"><span data-history-stage class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $bookingColor }}">{{ __('Profile::tickets.journey.list_status.'.$booking->journey_stage) }}</span></td>
                                        <td class="whitespace-nowrap px-4 py-4 text-center">
                                            <button type="button" data-ticket-history-actions aria-controls="ticket-actions-{{ $booking->id }}" aria-expanded="false"
                                                class="inline-flex size-9 items-center justify-center rounded-full border border-gray-200
                                                    text-futa-orange transition hover:border-futa-orange hover:bg-futa-orange-soft
                                                    focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                                                aria-label="{{ __('Profile::tickets.actions') }}: {{ $booking->booking_code }}">
                                                <x-heroicon-o-ellipsis-horizontal class="size-6" />
                                            </button>
                                            <div id="ticket-actions-{{ $booking->id }}" popover="auto"
                                                class="fixed z-50 m-0 w-52 rounded-xl border border-gray-200 bg-white p-1.5 text-left shadow-xl">
                                                <a href="{{ route('profile.tickets.show', $booking->id) }}" data-history-detail data-no-loader
                                                    class="block whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-semibold
                                                        text-gray-900 hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                                    {{ __('Profile::tickets.view_details') }}
                                                </a>
                                                @if($booking->can_cancel || $booking->cancellation_id)
                                                    <a href="{{ route('cancellation.show', $booking->id) }}" data-ticket-cancel data-no-loader
                                                        class="block whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium
                                                            text-gray-900 hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                                        {{ $booking->cancellation_id ? __('Cancellation::cancellation.refund') : __('Profile::tickets.cancel_ticket') }}
                                                    </a>
                                                @else
                                                    <button type="button" disabled class="block w-full px-3 py-2.5 text-left text-sm text-slate-400">{{ __('Profile::tickets.cancel_ticket') }}</button>
                                                @endif
                                                @if($booking->can_contact_for_change)
                                                    <a href="{{ route('exchange.show', $booking->id) }}" data-ticket-exchange data-no-loader
                                                        class="block whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium
                                                            text-gray-900 hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                                        {{ __('Profile::tickets.change_ticket') }}
                                                    </a>
                                                @else
                                                    <button type="button" disabled title="{{ __('Profile::tickets.change_unavailable') }}"
                                                        class="block w-full cursor-not-allowed whitespace-nowrap rounded-lg
                                                            px-3 py-2.5 text-left text-sm text-slate-400">
                                                        {{ __('Profile::tickets.change_ticket') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-14 text-center">
                                            <x-core::empty-state
                                                :title="__($hasFilters ? 'Profile::tickets.filtered_empty_title' : 'Profile::tickets.empty_title')"
                                                :description="__($hasFilters ? 'Profile::tickets.filtered_empty_description' : 'Profile::tickets.empty_description')" />
                                            @unless($hasFilters)
                                                <p class="mx-auto mt-3 max-w-lg text-sm text-slate-600">{{ __('Profile::tickets.guest_lookup') }}</p>
                                                <a href="{{ route('ticket-lookup') }}" class="mt-2 inline-block text-sm font-semibold text-futa-orange hover:underline">{{ __('Profile::tickets.guest_lookup_action') }}</a>
                                            @endunless
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

        <dialog data-history-dialog aria-label="{{ __('Profile::tickets.detail_title') }}"
            data-load-error="{{ __('Profile::tickets.journey.load_error') }}"
            class="ticket-history-scroll m-auto w-[min(64rem,calc(100vw-1.5rem))] max-h-[calc(100dvh-1.5rem)] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/65">
            <div data-history-dialog-content aria-live="polite"></div>
        </dialog>

        @include('core::partials.home.footer')
        <dialog data-cancellation-dialog aria-label="{{ __('Cancellation::cancellation.title') }}"
            data-error="{{ __('Cancellation::cancellation.load_error') }}"
            class="m-auto w-[min(64rem,calc(100vw-1.5rem))] max-h-[calc(100dvh-1.5rem)] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/65">
            <div data-cancellation-content></div>
        </dialog>
    </div>
    @include('Payment::exchange.host')
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection

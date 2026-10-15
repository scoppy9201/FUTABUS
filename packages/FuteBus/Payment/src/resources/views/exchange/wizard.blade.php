@php
    $choices = $tickets->map(fn ($ticket) => [
        'id' => $ticket->id, 'code' => $ticket->ticket_code, 'seat' => $ticket->seat_code, 'fare' => (int) $ticket->price,
        'departure' => \Illuminate\Support\Carbon::parse($ticket->departure_time)->toIso8601String(), 'eligible' => $ticket->eligible, 'blocked' => $ticket->blocked_reason,
        'route' => $ticket->origin_city.' → '.$ticket->destination_city,
    ])->values();
    $config = ['tickets' => $choices, 'csrf' => csrf_token(), 'trips' => route('exchange.trips', $booking),
        'hold' => route('exchange.hold', $booking), 'base' => route('exchange.show', $booking),
        'error' => __('Payment::exchange.error'), 'consentError' => __('Payment::exchange.consent_error'), 'expired' => __('Payment::exchange.expired'),
        'labels' => ['sleeper' => __('core::trip-search.sleeper'), 'seat' => __('core::trip-search.seat')]];
@endphp
<div data-exchange-wizard x-data="ticketExchangeWizard(@js($config))" class="text-left">
    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
        <div><h2 class="text-xl font-extrabold">{{ __('Payment::exchange.title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Payment::exchange.intro') }}</p></div>
        <button type="button" @click="close()" :disabled="busy" aria-label="{{ __('core::side-panel.close') }}"
            class="rounded-full p-2 text-slate-500 hover:bg-slate-100 disabled:opacity-40">
            <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
        </button>
    </header>
    <ol class="grid grid-cols-4 border-b border-slate-100 px-6 py-4 text-center text-xs sm:text-sm">
        @foreach(['choose', 'select', 'confirm', 'done'] as $index => $key)
            <li class="relative flex min-w-0 flex-col items-center gap-2 px-1" :class="step >= {{ $index }} ? 'font-semibold text-futa-orange' : 'text-slate-400'"
                :aria-current="step === {{ $index }} ? 'step' : null">
                @if(!$loop->last)
                    <span aria-hidden="true" class="pointer-events-none absolute top-4 left-[calc(50%+1rem)] right-[calc(-50%+1rem)] border-t border-dashed transition-colors"
                        :class="step > {{ $index }} ? 'border-futa-orange' : 'border-slate-200'"></span>
                @endif
                <span class="relative grid size-8 shrink-0 place-items-center rounded-full border border-current font-bold transition-colors"
                    :class="step > {{ $index }} ? 'border-futa-orange bg-futa-orange text-white' : (step === {{ $index }} ? 'bg-futa-orange-soft ring-2 ring-futa-orange/15' : 'bg-white')">
                    {{ $index + 1 }}
                </span>
                <span class="leading-5">{{ __('Payment::exchange.'.$key) }}</span>
            </li>
        @endforeach
    </ol>
    <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="min-w-0 space-y-5">
            <section x-show="step === 0" class="space-y-3">
                <h3 class="font-bold">{{ __('Payment::exchange.choose') }}</h3>
                @foreach($choices as $choice)
                    <label @class(['block rounded-xl border p-4', 'border-slate-200 bg-slate-50 text-slate-400' => !$choice['eligible'],
                        'cursor-pointer border-slate-200 has-checked:border-futa-orange has-checked:bg-futa-orange-soft' => $choice['eligible']])>
                        <span class="flex items-center gap-3">
                            <input type="radio" x-model="ticketId" value="{{ $choice['id'] }}" @disabled(!$choice['eligible']) class="accent-futa-orange">
                            <span class="min-w-0"><strong class="block break-all text-sm">{{ $choice['code'] }}</strong>
                                <span class="mt-1 block text-sm">{{ $choice['route'] }} · {{ $choice['seat'] }}</span>
                                <span class="mt-1 block text-xs">{{ \Illuminate\Support\Carbon::parse($choice['departure'])->format('H:i d/m/Y') }}</span>
                            </span>
                        </span>
                        @if(!$choice['eligible'])<span class="mt-2 block text-xs leading-5">{{ $choice['blocked'] }}</span>@endif
                    </label>
                @endforeach
            </section>
            <section x-show="step === 1" x-cloak class="space-y-4">
                <label class="block text-sm font-semibold">{{ __('Payment::exchange.date') }}
                    <input type="date" name="date" x-model="date" :aria-invalid="Boolean(errors.date)" aria-describedby="exchange-date-error" @change="loadTrips()" min="{{ now()->toDateString() }}" :disabled="busy"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5">
                </label>
                <p class="rounded-lg bg-blue-50 px-3 py-2 text-xs leading-5 text-blue-800">{{ __('Payment::exchange.trip_card_hint') }}</p>
                <p x-show="busy" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500" role="status">{{ __('Payment::exchange.loading_trips') }}</p>
                <p x-show="!busy && trips.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('Payment::exchange.no_trips') }}</p>
                <div class="space-y-4">
                    <template x-for="trip in trips" :key="trip.id">
                        <article class="trip-card exchange-trip-card" :class="{ 'is-expanded': openTripId === trip.id && openPanel !== null, 'is-exchange-selected': selectedTrip?.id === trip.id }">
                            @include('TripSearch::partials.trip-card-summary')
                            @include('TripSearch::partials.trip-card-navigation', ['exchangeMode' => true])
                            <div class="trip-card__detail" :class="{ 'is-seats': openPanel === 'seats' }"
                                x-show="openTripId === trip.id && openPanel !== null" x-cloak>
                                <div x-show="openPanel === 'seats'" class="trip-card__seat-panel">
                                    @include('TripSearch::partials.trip-seat-map', ['exchangeMode' => true])
                                    <div class="exchange-seat-footer">
                                        <div class="min-w-0">
                                            <strong class="block text-xs text-slate-500">{{ __('Payment::exchange.seat') }}</strong>
                                            <span class="block truncate text-sm font-semibold" x-text="selectedTrip?.id === trip.id && seat ? seat.code : @js(__('Payment::exchange.no_seat_selected'))"></span>
                                        </div>
                                        <strong class="text-sm text-futa-orange" x-text="money(trip.fare)"></strong>
                                    </div>
                                </div>
                                <div x-show="openPanel === 'schedule'" class="trip-card__itinerary-panel">
                                    @include('TripSearch::partials.trip-itinerary', [
                                        'itineraryStopsExpression' => 'trip.timeline',
                                        'itineraryHasStops' => true,
                                        'selectableStops' => true,
                                        'hideItineraryNote' => true,
                                    ])
                                </div>
                                @include('TripSearch::partials.trip-transfer')
                                @include('TripSearch::partials.trip-policies')
                            </div>
                        </article>
                    </template>
                </div>
                <p id="exchange-date-error" class="text-xs text-red-600" x-text="errors.trip_id || errors.date"></p>
                <template x-if="selectedTrip">
                    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold">{{ __('Payment::exchange.selected_trip') }}
                            <span class="font-normal text-slate-600" x-text="stationLabel(selectedTrip.origin) + ' → ' + stationLabel(selectedTrip.destination) + ' · ' + time(selectedTrip.departure)"></span>
                        </p>
                        <dl class="grid gap-2 text-xs sm:grid-cols-3">
                            <div><dt class="text-slate-500">{{ __('Payment::exchange.seat') }}</dt><dd class="mt-1 font-semibold" x-text="seat?.code || '—'"></dd></div>
                            <div><dt class="text-slate-500">{{ __('Payment::exchange.pickup') }}</dt><dd class="mt-1 font-semibold" x-text="pickupStop?.name || '—'"></dd></div>
                            <div><dt class="text-slate-500">{{ __('Payment::exchange.dropoff') }}</dt><dd class="mt-1 font-semibold" x-text="dropoffStop?.name || '—'"></dd></div>
                        </dl>
                        <p class="text-xs text-red-600" x-text="errors.seat_id"></p>
                        <p class="text-xs text-red-600" x-text="errors.pickup"></p>
                        <p class="text-xs text-red-600" x-text="errors.dropoff"></p>
                        <label class="block text-sm font-semibold">{{ __('Payment::exchange.reason') }}
                            <textarea name="reason" x-model="reason" :aria-invalid="Boolean(errors.reason)" aria-describedby="exchange-reason-error" maxlength="500" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></textarea>
                            <span id="exchange-reason-error" class="mt-1 block text-xs font-normal text-red-600" x-text="errors.reason"></span>
                        </label>
                    </div>
                </template>
            </section>
            <section x-show="step === 2" x-cloak class="space-y-4">
                <h3 class="font-bold">{{ __('Payment::exchange.confirm') }}</h3>
                <dl class="space-y-4 rounded-xl border border-slate-200 p-4 text-sm">
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.old_ticket') }}</dt><dd class="mt-1 font-semibold" x-text="ticket?.code + ' · ' + time(ticket?.departure)"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.new_trip') }}</dt><dd class="mt-1 font-semibold" x-text="time(selectedTrip?.departure) + ' · ' + (seat?.code || '')"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.pickup') }}</dt><dd class="mt-1 font-semibold" x-text="pickupStop?.name + ' · ' + pickupStop?.time"></dd>
                        <dd class="mt-1 text-xs text-slate-500" x-text="pickupStop?.address"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.dropoff') }}</dt><dd class="mt-1 font-semibold" x-text="dropoffStop?.name + ' · ' + dropoffStop?.time"></dd>
                        <dd class="mt-1 text-xs text-slate-500" x-text="dropoffStop?.address"></dd></div>
                </dl>
                <p class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">{{ __('Payment::exchange.warning') }}</p>
                <p class="text-sm text-slate-500">{{ __('Payment::exchange.hold_time') }} <strong x-text="Math.floor(remaining / 60) + ':' + String(remaining % 60).padStart(2, '0')"></strong></p>
                <p x-show="remaining === 0" class="text-sm text-red-600">{{ __('Payment::exchange.expired') }}</p>
                <div class="flex items-start gap-3 text-sm leading-6">
                    <input id="exchange-consent" type="checkbox" name="consent" x-model="consent" :aria-invalid="Boolean(errors.consent)"
                        aria-describedby="exchange-consent-error" aria-labelledby="exchange-consent-label exchange-consent-policy"
                        class="mt-1.5 shrink-0 accent-futa-orange">
                    <p>
                        <label id="exchange-consent-label" for="exchange-consent" class="cursor-pointer">{{ __('Payment::exchange.consent_prefix') }}</label>
                        <a id="exchange-consent-policy" href="{{ route('refund') }}#ticket-change-rules" target="_blank" rel="noopener noreferrer" data-no-loader
                            title="{{ __('Payment::exchange.policy_new_tab') }}"
                            class="rounded-sm font-semibold text-futa-orange underline decoration-futa-orange/50 underline-offset-4 hover:text-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                            {{ __('Payment::exchange.policy_link') }}<x-heroicon-o-arrow-top-right-on-square class="ml-0.5 inline size-3.5 align-middle" aria-hidden="true" />
                        </a>.
                    </p>
                </div>
                <p id="exchange-consent-error" class="text-xs text-red-600" x-text="errors.consent"></p>
            </section>
            <section x-show="step === 3" x-cloak class="space-y-4 text-center">
                <x-heroicon-o-check-circle class="mx-auto size-16 text-futa-green" aria-hidden="true" />
                <h3 class="text-xl font-bold text-futa-green">{{ __('Payment::exchange.completed') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Payment::exchange.new_qr_note') }}</p>
                <strong class="block break-all text-futa-orange" x-text="result?.code"></strong>
                <img :src="result?.qr || ''" x-show="result?.qr" alt="{{ __('Payment::exchange.qr') }}" class="mx-auto size-48">
                <a :href="result?.url" class="inline-flex rounded-full bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::exchange.view_ticket') }}</a>
            </section>
            <p role="alert" class="text-sm text-red-600" x-text="errors.ticket"></p>
        </div>
        <aside class="self-start rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm lg:sticky lg:top-4">
            <h3 class="font-bold">{{ __('Payment::exchange.summary') }}</h3>
            <p class="mt-3 font-semibold" x-text="ticket?.route"></p>
            <dl class="mt-4 space-y-3">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.old_fare') }}</dt><dd x-text="money(ticket?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.new_fare') }}</dt><dd x-text="money(selectedTrip?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.fee') }}</dt><dd class="font-bold text-futa-green">0 ₫</dd></div>
                <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 font-bold"><dt>{{ __('Payment::exchange.additional') }}</dt><dd class="text-futa-orange">0 ₫</dd></div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('Payment::exchange.policy_note') }}</p>
        </aside>
    </div>
    <footer class="flex justify-between gap-3 border-t border-slate-200 bg-white px-6 py-4">
        <button type="button" @click="step === 0 || step === 3 ? close() : back()" :disabled="busy"
            class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold disabled:opacity-40">{{ __('Payment::exchange.back') }}</button>
        <button type="button" x-show="step < 2" @click="next()" :disabled="busy || (step === 0 ? !ticket?.eligible : !ready)"
            class="rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{{ __('Payment::exchange.next') }}</button>
        <button type="button" x-show="step === 2" @click="complete()" :disabled="busy || !consent || (remaining === 0 && !completionAttempted)"
            class="rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{{ __('Payment::exchange.submit') }}</button>
    </footer>
</div>

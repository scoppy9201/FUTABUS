@php
    $choices = $tickets->map(fn ($ticket) => [
        'id' => $ticket->id, 'code' => $ticket->ticket_code, 'seat' => $ticket->seat_code, 'fare' => (int) $ticket->price,
        'departure' => \Illuminate\Support\Carbon::parse($ticket->departure_time)->toIso8601String(), 'eligible' => $ticket->eligible, 'blocked' => $ticket->blocked_reason,
        'route' => $ticket->origin_city.' → '.$ticket->destination_city,
    ])->values();
    $config = ['tickets' => $choices, 'csrf' => csrf_token(), 'trips' => route('exchange.trips', $booking),
        'hold' => route('exchange.hold', $booking), 'base' => route('exchange.show', $booking),
        'error' => __('Payment::exchange.error'), 'consentError' => __('Payment::exchange.consent_error'), 'expired' => __('Payment::exchange.expired')];
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
    <ol class="grid grid-cols-4 gap-2 border-b border-slate-100 px-6 py-4 text-xs sm:text-sm">
        @foreach(['choose', 'select', 'confirm', 'done'] as $index => $key)
            <li class="flex items-center gap-2" :class="step >= {{ $index }} ? 'font-semibold text-futa-orange' : 'text-slate-400'"
                :aria-current="step === {{ $index }} ? 'step' : null">
                <span class="grid size-6 shrink-0 place-items-center rounded-full border border-current">{{ $index + 1 }}</span>
                {{ __('Payment::exchange.'.$key) }}
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
                <p x-show="!busy && trips.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('Payment::exchange.no_trips') }}</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    <template x-for="trip in trips" :key="trip.id">
                        <button type="button" @click="selectTrip(trip)" :disabled="busy || trip.available < 1"
                            class="rounded-xl border p-3 text-left disabled:cursor-not-allowed disabled:opacity-40"
                            :class="selectedTrip?.id === trip.id ? 'border-futa-orange bg-futa-orange-soft' : 'border-slate-200'">
                            <strong class="block text-sm" x-text="time(trip.departure)"></strong>
                            <span class="mt-1 block text-xs text-slate-500"><span x-text="trip.available"></span> {{ __('Payment::exchange.available') }}</span>
                            <span class="mt-1 block text-sm font-semibold text-futa-green" x-text="money(trip.fare)"></span>
                        </button>
                    </template>
                </div>
                <p id="exchange-date-error" class="text-xs text-red-600" x-text="errors.trip_id || errors.date"></p>
                <template x-if="selectedTrip">
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold">{{ __('Payment::exchange.seat') }}</h3>
                        <div class="flex flex-wrap justify-center gap-8 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <template x-for="deck in decks" :key="deck">
                                <div><h4 class="mb-3 text-center text-xs font-semibold" x-text="deck === 'upper' ? @js(__('Payment::exchange.upper')) : @js(__('Payment::exchange.lower'))"></h4>
                                    <div class="grid gap-2" :style="{ gridTemplateColumns: 'repeat(' + columns + ', 2.75rem)' }">
                                        <template x-for="seat in selectedTrip.seats.filter(seat => seat.deck === deck)" :key="seat.id">
                                            <button type="button" @click="seatId = String(seat.id)" :disabled="seat.sold || busy"
                                                :style="{ gridRow: seat.row, gridColumn: seat.column }"
                                                class="h-11 rounded-lg border text-xs font-semibold disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-200 disabled:text-slate-400"
                                                :class="String(seatId) === String(seat.id) ? 'border-futa-orange bg-futa-orange-soft text-futa-orange' : 'border-blue-200 bg-blue-50 text-blue-600'"
                                                :aria-pressed="String(seatId) === String(seat.id)" x-text="seat.code"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-red-600" x-text="errors.seat_id"></p>
                        @foreach(['pickup', 'dropoff'] as $kind)
                            <label class="block text-sm font-semibold">{{ __('Payment::exchange.'.$kind) }}
                                <select name="{{ $kind }}" x-model="{{ $kind }}" :aria-invalid="Boolean(errors.{{ $kind }})" aria-describedby="exchange-{{ $kind }}-error" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5">
                                    <option value="">{{ __('Payment::exchange.choose_stop') }}</option>
                                    <template x-for="stop in selectedTrip.stops.{{ $kind }}" :key="stop.id">
                                        <option :value="stop.id" :disabled="stop.expired" x-text="stop.name + ' · ' + stop.time"></option>
                                    </template>
                                </select>
                                <span id="exchange-{{ $kind }}-error" class="mt-1 block text-xs font-normal text-red-600" x-text="errors.{{ $kind }}"></span>
                            </label>
                        @endforeach
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
                <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="consent" x-model="consent" :aria-invalid="Boolean(errors.consent)" aria-describedby="exchange-consent-error" class="mt-1 accent-futa-orange">{{ __('Payment::exchange.consent') }}</label>
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

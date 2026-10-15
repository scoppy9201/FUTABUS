<section x-show="step === 3" x-cloak class="space-y-5">
    <p class="rounded-xl bg-futa-orange-soft p-4 text-sm text-futa-orange">{{ __('Payment::exchange.policy_note') }}</p>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
        <div class="space-y-4 rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-bold text-futa-green">{{ __('StaffTicketProcessing::exchange.passenger') }}</h2>
            <p class="text-sm text-slate-500">{{ __('StaffTicketProcessing::exchange.keep_passenger') }}</p>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.customer') }}</dt><dd class="font-semibold" x-text="selected?.customer"></dd></div>
                <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.phone') }}</dt><dd class="font-semibold" x-text="selected?.phone || '—'"></dd></div>
            </dl>
            <template x-if="flow.selectedTrip"><div class="space-y-4">
                        <h3 class="text-sm font-bold">{{ __('Payment::exchange.seat') }}</h3>
                        <div class="flex flex-wrap justify-center gap-8 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <template x-for="deck in flow.decks" :key="deck">
                                <div><h4 class="mb-3 text-center text-xs font-semibold" x-text="deck === 'upper' ? @js(__('Payment::exchange.upper')) : @js(__('Payment::exchange.lower'))"></h4>
                                    <div class="grid gap-2" :style="{ gridTemplateColumns: 'repeat(' + flow.columns + ', 2.75rem)' }">
                                        <template x-for="seat in flow.selectedTrip.seats.filter(seat => seat.deck === deck)" :key="seat.id">
                                            <button type="button" @click="flow.seatId = String(seat.id)" :disabled="seat.sold || flow.busy"
                                                :style="{ gridRow: seat.row, gridColumn: seat.column }"
                                                class="h-11 rounded-lg border text-xs font-semibold disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-200 disabled:text-slate-400"
                                                :class="String(flow.seatId) === String(seat.id) ? 'border-futa-orange bg-futa-orange-soft text-futa-orange' : 'border-blue-200 bg-blue-50 text-blue-600'"
                                                :aria-pressed="String(flow.seatId) === String(seat.id)" x-text="seat.code"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-red-600" x-text="flow.errors.seat_id"></p>
                        @foreach(['pickup', 'dropoff'] as $kind)
                            <label class="block text-sm font-semibold">{{ __('Payment::exchange.'.$kind) }}
                                <select name="{{ $kind }}" x-model="flow.{{ $kind }}" :aria-invalid="Boolean(flow.errors.{{ $kind }})" aria-describedby="exchange-{{ $kind }}-error" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5">
                                    <option value="">{{ __('Payment::exchange.choose_stop') }}</option>
                                    <template x-for="stop in flow.selectedTrip.stops.{{ $kind }}" :key="stop.id">
                                        <option :value="stop.id" :disabled="stop.expired" x-text="stop.name + ' · ' + stop.time"></option>
                                    </template>
                                </select>
                                <span id="exchange-{{ $kind }}-error" class="mt-1 block text-xs font-normal text-red-600" x-text="flow.errors.{{ $kind }}"></span>
                            </label>
                        @endforeach
                        <label class="block text-sm font-semibold">{{ __('Payment::exchange.reason') }}
                            <textarea name="reason" x-model="reason" :aria-invalid="Boolean(flow.errors.reason)" aria-describedby="exchange-reason-error" maxlength="500" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></textarea>
                            <span id="exchange-reason-error" class="mt-1 block text-xs font-normal text-red-600" x-text="flow.errors.reason"></span>
                        </label>

            </div></template>
        </div>
        <aside class="self-start rounded-xl border border-gray-200 bg-white p-5 text-sm">            <h3 class="font-bold">{{ __('Payment::exchange.summary') }}</h3>
            <p class="mt-3 font-semibold" x-text="flow.ticket?.route"></p>
            <dl class="mt-4 space-y-3">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.old_fare') }}</dt><dd x-text="flow.money(flow.ticket?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.new_fare') }}</dt><dd x-text="flow.money(flow.selectedTrip?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.fee') }}</dt><dd class="font-bold text-futa-green">0 ₫</dd></div>
                <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 font-bold"><dt>{{ __('Payment::exchange.additional') }}</dt><dd class="text-futa-orange">0 ₫</dd></div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('Payment::exchange.policy_note') }}</p>
</aside>
    </div>
    <div class="flex justify-between gap-3">
        <button type="button" @click="back()" :disabled="flow.busy" class="rounded-lg border border-gray-200 bg-white px-5 py-3 disabled:opacity-50">{{ __('Payment::exchange.back') }}</button>
        <button type="button" @click="holdSeat()" :disabled="flow.busy || !flow.ready" class="rounded-lg bg-futa-orange px-5 py-3 font-semibold text-white disabled:opacity-50">{{ __('Payment::exchange.next') }}</button>
    </div>
</section>
<section x-show="step === 4" x-cloak class="space-y-5 rounded-xl border border-gray-200 bg-white p-5">
    <h2 class="text-xl font-bold text-futa-green">{{ __('StaffTicketProcessing::exchange.settlement') }}</h2>
    <p class="rounded-lg bg-futa-orange-soft p-4 text-futa-orange">{{ __('StaffTicketProcessing::exchange.no_payment') }}</p>
            <h3 class="font-bold">{{ __('Payment::exchange.summary') }}</h3>
            <p class="mt-3 font-semibold" x-text="flow.ticket?.route"></p>
            <dl class="mt-4 space-y-3">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.old_fare') }}</dt><dd x-text="flow.money(flow.ticket?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.new_fare') }}</dt><dd x-text="flow.money(flow.selectedTrip?.fare)"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.fee') }}</dt><dd class="font-bold text-futa-green">0 ₫</dd></div>
                <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 font-bold"><dt>{{ __('Payment::exchange.additional') }}</dt><dd class="text-futa-orange">0 ₫</dd></div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('Payment::exchange.policy_note') }}</p>
                <h3 class="font-bold">{{ __('Payment::exchange.confirm') }}</h3>
                <dl class="space-y-4 rounded-xl border border-slate-200 p-4 text-sm">
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.old_ticket') }}</dt><dd class="mt-1 font-semibold" x-text="flow.ticket?.code + ' · ' + flow.time(flow.ticket?.departure)"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.new_trip') }}</dt><dd class="mt-1 font-semibold" x-text="flow.time(flow.selectedTrip?.departure) + ' · ' + (flow.seat?.code || '')"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.pickup') }}</dt><dd class="mt-1 font-semibold" x-text="flow.pickupStop?.name + ' · ' + flow.pickupStop?.time"></dd>
                        <dd class="mt-1 text-xs text-slate-500" x-text="flow.pickupStop?.address"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Payment::exchange.dropoff') }}</dt><dd class="mt-1 font-semibold" x-text="flow.dropoffStop?.name + ' · ' + flow.dropoffStop?.time"></dd>
                        <dd class="mt-1 text-xs text-slate-500" x-text="flow.dropoffStop?.address"></dd></div>
                </dl>
                <p class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">{{ __('Payment::exchange.warning') }}</p>
                <p class="text-sm text-slate-500">{{ __('Payment::exchange.hold_time') }} <strong x-text="Math.floor(flow.remaining / 60) + ':' + String(flow.remaining % 60).padStart(2, '0')"></strong></p>
                <p x-show="flow.remaining === 0" class="text-sm text-red-600">{{ __('Payment::exchange.expired') }}</p>
                <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="consent" x-model="flow.consent" :aria-invalid="Boolean(flow.errors.consent)" aria-describedby="exchange-consent-error" class="mt-1 accent-futa-orange">{{ __('Payment::exchange.consent') }}</label>
                <p id="exchange-consent-error" class="text-xs text-red-600" x-text="flow.errors.consent"></p>

    <div class="flex justify-between gap-3">
        <button type="button" @click="back()" :disabled="flow.busy || flow.completionAttempted" class="rounded-lg border border-gray-200 px-5 py-3 disabled:opacity-50">{{ __('Payment::exchange.back') }}</button>
        <button type="button" @click="completeExchange()" :disabled="flow.busy || !flow.consent || (flow.remaining === 0 && !flow.completionAttempted)" class="rounded-lg bg-futa-orange px-5 py-3 font-semibold text-white disabled:opacity-50">{{ __('Payment::exchange.submit') }}</button>
    </div>
</section>
<section x-show="step === 5" x-cloak class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 text-center">
                <x-heroicon-o-check-circle class="mx-auto size-16 text-futa-green" aria-hidden="true" />
                <h3 class="text-xl font-bold text-futa-green">{{ __('Payment::exchange.completed') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Payment::exchange.new_qr_note') }}</p>
                <strong class="block break-all text-futa-orange" x-text="flow.result?.code"></strong>
                <img :src="flow.result?.qr || ''" x-show="flow.result?.qr" alt="{{ __('Payment::exchange.qr') }}" class="mx-auto size-48">
                <a :href="flow.result?.url" class="inline-flex rounded-full bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::exchange.view_ticket') }}</a>

    <a href="{{ route('staff.tickets.exchange') }}" class="block font-semibold text-futa-orange hover:underline">{{ __('StaffTicketProcessing::exchange.another') }}</a>
</section>

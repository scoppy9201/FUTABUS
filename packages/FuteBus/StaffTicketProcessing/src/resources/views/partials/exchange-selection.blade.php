<section x-show="step === 3" x-cloak class="mx-auto w-full max-w-282 space-y-5 staff-exchange-selection">
    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,760px)_minmax(270px,344px)]">
        <div class="min-w-0 space-y-5">
            <section class="booking-panel" aria-labelledby="exchange-seat-title">
                <div class="booking-panel__header justify-between border-b border-slate-100">
                    <h2 id="exchange-seat-title">{{ __('Payment::exchange.seat') }}</h2>
                    <span class="rounded-lg bg-futa-orange-soft px-3 py-1.5 text-sm font-semibold text-futa-orange" x-text="flow.seat?.code || '—'"></span>
                </div>
                <fieldset :disabled="flow.busy || Boolean(pendingHold)" class="booking-page__seat-area min-w-0">
                    <legend class="sr-only">{{ __('Payment::exchange.seat') }}</legend>
                    <div class="booking-page__seat-map min-w-0">
                        <template x-for="deck in seatDecks" :key="deck">
                            <div class="booking-page__deck">
                                <h3 x-text="deck === 'upper' ? @js(__('core::booking.upper_deck')) : @js(__('core::booking.lower_deck'))"></h3>
                                <div class="trip-card__seat-grid" :style="{ '--seat-columns': seatColumns }">
                                    <template x-for="seat in flow.selectedTrip.seats.filter(item => item.deck === deck)" :key="seat.id">
                                        <button type="button" class="trip-card__seat"
                                            :style="{ gridRow: seatRow(seat), gridColumn: seat.column }"
                                            :class="seat.sold ? 'is-sold' : (String(flow.seatId) === String(seat.id) ? 'is-chosen' : 'is-free')"
                                            :disabled="seat.sold || flow.busy || Boolean(pendingHold)"
                                            :aria-label="seat.code + ', ' + (seat.sold ? @js(__('core::booking.sold')) : (String(flow.seatId) === String(seat.id) ? @js(__('core::booking.selected')) : @js(__('core::booking.available'))))"
                                            :aria-pressed="String(flow.seatId) === String(seat.id)"
                                            @click="flow.seatId = String(seat.id)">
                                            <span class="trip-card__seat-code" x-text="seat.code"></span>
                                            <span class="trip-card__seat-foot" aria-hidden="true"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="shrink-0">
                        @include('BookingManagement::partials.booking-seat-legend')
                    </div>
                </fieldset>
                <div class="px-5 pb-3">
                    <p role="alert" class="mt-2 text-xs text-red-600" x-text="flow.errors.seat_id"></p>
                </div>
            </section>

            <section class="booking-panel" aria-labelledby="exchange-passenger-title">
                <div class="booking-panel__header border-b border-slate-100">
                    <h2 id="exchange-passenger-title">{{ __('StaffTicketProcessing::exchange.passenger') }}</h2>
                </div>
                <div class="p-5">
                    <p class="mb-5 text-sm leading-6 text-slate-500">{{ __('StaffTicketProcessing::exchange.edit_passenger_hint') }}</p>
                    <form x-ref="passengerForm" @submit.prevent="holdSeat()" class="booking-page__fields grid gap-4 sm:grid-cols-2">
                        @foreach ([['field' => 'passenger_name', 'model' => 'passengerName', 'label' => 'customer', 'type' => 'text', 'autocomplete' => 'name'], ['field' => 'passenger_phone', 'model' => 'passengerPhone', 'label' => 'phone', 'type' => 'tel', 'autocomplete' => 'tel']] as $input)
                            <div class="booking-page__field">
                                <label for="exchange-{{ $input['field'] }}">{{ __('StaffTicketProcessing::tickets.'.$input['label']) }} <b>*</b></label>
                                <div class="booking-page__input-wrap">
                                    <input id="exchange-{{ $input['field'] }}" name="{{ $input['field'] }}" type="{{ $input['type'] }}"
                                        x-model="{{ $input['model'] }}" required autocomplete="{{ $input['autocomplete'] }}"
                                        maxlength="{{ $input['type'] === 'tel' ? 12 : 255 }}"
                                        @if ($input['type'] === 'tel') pattern="(0|[+]84)[0-9]{9}" @endif
                                        :disabled="flow.busy || Boolean(pendingHold)" :aria-invalid="Boolean(flow.errors.{{ $input['field'] }})"
                                        aria-describedby="exchange-{{ $input['field'] }}-error">
                                </div>
                                <p id="exchange-{{ $input['field'] }}-error" role="alert" class="booking-page__field-error" x-text="flow.errors.{{ $input['field'] }}"></p>
                            </div>
                        @endforeach
                    </form>
                </div>
            </section>

            <section class="booking-panel" aria-labelledby="exchange-stops-title">
                <div class="booking-panel__header border-b border-slate-100">
                    <h2 id="exchange-stops-title">{{ __('core::booking.pickup_title') }}</h2>
                    <x-heroicon-o-information-circle class="size-6 text-futa-orange" aria-hidden="true" />
                </div>
                <fieldset :disabled="flow.busy || Boolean(pendingHold)" class="booking-page__pickup-grid min-w-0">
                    <legend class="sr-only">{{ __('core::booking.pickup_title') }}</legend>
                    @foreach (['pickup', 'dropoff'] as $kind)
                        <div class="min-w-0 space-y-3 sm:last-of-type:border-l sm:last-of-type:border-slate-100 sm:last-of-type:pl-6">
                            <label for="exchange-{{ $kind }}" class="text-sm font-semibold text-slate-700">{{ __('core::booking.'.$kind) }} <span class="text-futa-orange">*</span></label>
                            <div class="booking-page__radio-row pt-3">
                                @foreach (['station', 'transfer'] as $mode)
                                    <label class="inline-flex cursor-pointer items-center gap-2" :class="{{ $kind }}Mode === '{{ $mode }}' ? 'text-futa-orange' : 'text-slate-700'">
                                        <input type="radio" name="exchange_{{ $kind }}_mode" value="{{ $mode }}" x-model="{{ $kind }}Mode" class="size-4 accent-futa-orange">
                                        {{ __('core::booking.'.$mode) }}
                                    </label>
                                @endforeach
                            </div>
                            <div x-show="{{ $kind }}Mode === 'transfer'" class="booking-page__input-wrap">
                                <input type="text" x-model="{{ $kind }}Address" maxlength="500"
                                    aria-label="{{ __('core::booking.transfer_address') }}" placeholder="{{ __('core::booking.transfer_address') }}"
                                    class="h-9.25 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-futa-orange">
                                <p role="alert" class="mt-1 text-xs text-red-600" x-text="flow.errors.{{ $kind }}_address"></p>
                            </div>
                            <div x-show="{{ $kind }}Mode === 'station'">
                            <select id="exchange-{{ $kind }}" name="{{ $kind }}" x-model="flow.{{ $kind }}"
                                :aria-invalid="Boolean(flow.errors.{{ $kind }})" aria-describedby="exchange-{{ $kind }}-error"
                                class="h-9.25 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/15">
                                <option value="">{{ __('Payment::exchange.choose_stop') }}</option>
                                <template x-for="stop in flow.selectedTrip?.stops.{{ $kind }} || []" :key="stop.id">
                                    <option :value="stop.id" :disabled="stop.expired" x-text="stop.time + ' · ' + stop.name"></option>
                                </template>
                            </select>
                            <p class="text-xs leading-5 text-slate-500" x-text="flow.{{ $kind }}Stop?.address || ''"></p>
                            </div>
                            <p id="exchange-{{ $kind }}-error" role="alert" class="text-xs text-red-600" x-text="flow.errors.{{ $kind }}"></p>
                        </div>
                    @endforeach
                    <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">{{ __('Payment::exchange.reason') }}
                        <textarea name="reason" x-model="reason" maxlength="500" rows="2" :aria-invalid="Boolean(flow.errors.reason)" aria-describedby="exchange-reason-error"
                            class="mt-2 w-full rounded-lg border border-slate-200 p-3 font-normal outline-none focus:border-futa-orange"></textarea>
                        <span id="exchange-reason-error" role="alert" class="mt-1 block text-xs text-red-600" x-text="flow.errors.reason"></span>
                    </label>
                </fieldset>
            </section>
        </div>

        <aside class="min-w-0 space-y-5 xl:sticky xl:top-4">
            <section class="booking-panel">
                <div class="booking-panel__header border-b border-slate-100">
                    <h2>{{ __('StaffTicketProcessing::exchange.new_trip_info') }}</h2>
                </div>
                <div class="space-y-4 p-5 text-sm">
                    <p class="font-bold text-futa-green" x-text="selected?.route"></p>
                    <dl class="space-y-4">
                        <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::exchange.departure') }}</dt><dd class="mt-1 font-semibold" x-text="flow.time(flow.selectedTrip?.departure)"></dd></div>
                        <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::exchange.arrival') }}</dt><dd class="mt-1 font-semibold" x-text="flow.time(flow.selectedTrip?.arrival)"></dd></div>
                        <div class="border-t border-dashed border-slate-200 pt-4"><dt class="text-slate-500">{{ __('Payment::exchange.seat') }}</dt><dd class="mt-1 font-bold text-futa-orange" x-text="flow.seat?.code || '—'"></dd></div>
                        @foreach (['pickup', 'dropoff'] as $kind)
                            <div><dt class="text-slate-500">{{ __('core::booking.'.$kind) }}</dt><dd class="mt-1 font-semibold" x-text="{{ $kind }}Mode === 'transfer' ? {{ $kind }}Address : (flow.{{ $kind }}Stop ? flow.{{ $kind }}Stop.time + ' · ' + flow.{{ $kind }}Stop.name : '—')"></dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>
            <section class="booking-panel">
                <div class="booking-panel__header border-b border-slate-100">
                    <h2>{{ __('StaffTicketProcessing::exchange.price_details') }}</h2>
                </div>
                <div class="p-5 text-sm">
                    <dl class="space-y-4">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.old_fare') }}</dt><dd class="font-semibold" x-text="flow.money(flow.ticket?.fare)"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.new_fare') }}</dt><dd class="font-semibold" x-text="flow.money(flow.selectedTrip?.fare)"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.fee') }}</dt><dd class="font-semibold text-futa-green" x-text="flow.money(0)"></dd></div>
                        <div class="flex items-center justify-between gap-3 border-t border-dashed border-slate-200 pt-4"><dt class="font-bold">{{ __('StaffTicketProcessing::exchange.total_payment') }}</dt><dd class="text-xl font-bold text-futa-orange" x-text="flow.money(0)"></dd></div>
                    </dl>
                    <p class="mt-4 rounded-lg bg-futa-orange-soft p-3 text-xs leading-5 text-futa-orange">{{ __('StaffTicketProcessing::exchange.no_payment') }}</p>
                    <p class="mt-3 text-xs leading-5 text-slate-500">{{ __('Payment::exchange.policy_note') }}</p>
                </div>
            </section>
        </aside>
    </div>
    <p x-show="pendingHold && !flow.busy" x-cloak role="status" class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">{{ __('StaffTicketProcessing::exchange.hold_retry') }}</p>
    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <button type="button" @click="back()" :disabled="flow.busy || Boolean(pendingHold)" class="rounded-lg border border-slate-200 px-5 py-3 font-semibold disabled:opacity-50">{{ __('Payment::exchange.back') }}</button>
        <button type="button" @click="holdSeat()" :disabled="flow.busy || !flow.ready" class="rounded-lg bg-futa-orange px-6 py-3 font-semibold text-white hover:bg-futa-orange-dark disabled:opacity-50">{{ __('Payment::exchange.next') }}</button>
    </div>
</section>

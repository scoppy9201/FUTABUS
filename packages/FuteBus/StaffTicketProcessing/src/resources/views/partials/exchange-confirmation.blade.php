<section x-show="step === 4 && !flow.result" x-cloak class="mx-auto max-w-282 space-y-5">
    <h2 class="text-xl font-bold text-futa-green">{{ __('StaffTicketProcessing::exchange.confirm_title') }}</h2>
    <div class="grid items-start gap-4 lg:grid-cols-3">
        @foreach (['old', 'new'] as $kind)
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header @class(['flex items-center justify-between gap-2 px-4 py-3', 'bg-orange-50' => $kind === 'old', 'bg-sky-50' => $kind === 'new'])>
                    <h3 class="flex items-center gap-2 text-sm font-bold"><x-heroicon-o-ticket class="size-5 shrink-0 text-futa-orange" />{{ __('StaffTicketProcessing::exchange.'.$kind.'_card') }}</h3>
                    <span class="rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-futa-green">{{ __('StaffTicketProcessing::exchange.'.$kind.'_badge') }}</span>
                </header>
                <div class="space-y-5 p-4">
                    <div class="min-h-14">
                        @if ($kind === 'old')
                            <strong class="block break-all text-lg" x-text="selected?.code"></strong>
                        @else
                            <strong class="block text-lg">{{ __('StaffTicketProcessing::exchange.pending_code') }}</strong>
                        @endif
                        @if ($kind === 'old')
                            <p class="mt-1 break-all text-xs text-slate-500" x-text="selected?.booking_code || ''"></p>
                        @else
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('StaffTicketProcessing::exchange.issue_after_confirm') }}</p>
                        @endif
                    </div>
                    <dl class="space-y-4 text-sm">
                        <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.route') }}</dt><dd class="font-semibold" x-text="selected?.route"></dd></div>
                        <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('StaffTicketProcessing::exchange.departure') }}</dt><dd class="font-semibold" x-text="flow.time({{ $kind === 'old' ? 'selected?.departure' : 'flow.selectedTrip?.departure' }})"></dd></div>
                        <div
                            class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3">
                        <dt
                            class="text-slate-500">{{ __('StaffTicketProcessing::exchange.arrival') }}</dt>
                        <dd
                            class="font-semibold"
                            x-text="flow.time({{ $kind === 'old' ? 'selected?.arrival' : 'flow.selectedTrip?.arrival' }}) || '—'">
                        </dd>
                        </div>
                        <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.seat') }}</dt><dd class="font-bold" x-text="{{ $kind === 'old' ? 'selected?.seat' : "flow.seat?.code || '—'" }}"></dd></div>
                        @foreach (['pickup', 'dropoff'] as $stop)
                            <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('core::booking.'.$stop) }}</dt>
                                @if ($kind === 'new')
                                    <dd class="font-semibold" x-text="{{ $stop }}Mode === 'transfer' ? {{ $stop }}Address : (flow.{{ $stop }}Stop?.name || '—')"></dd>
                                @else
                                    <dd class="font-semibold" x-text="selected?.{{ $stop }} || '—'"></dd>
                                @endif
                            </div>
                        @endforeach
                        <div
                            class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3 border-t border-slate-100 pt-4">
                        <dt
                            class="text-slate-500">{{ __('StaffTicketProcessing::exchange.ticket_price') }}</dt>
                        <dd
                            class="font-bold text-futa-orange"
                            x-text="flow.money({{ $kind === 'old' ? 'selected?.fare' : 'flow.selectedTrip?.fare' }})">
                        </dd>
                        </div>
                    </dl>
                </div>
            </section>
        @endforeach
        <aside class="space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <header class="mb-5 flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 text-sm font-bold"><x-heroicon-o-user class="size-5" />{{ __('StaffTicketProcessing::exchange.passenger') }}</h3>
                    <button type="button" @click="back()" :disabled="flow.busy || flow.completionAttempted" class="rounded-lg border border-orange-200 px-3 py-1.5 text-xs font-semibold text-futa-orange disabled:opacity-50">{{ __('StaffTicketProcessing::exchange.edit') }}</button>
                </header>
                <dl class="space-y-4 text-sm">
                    <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('StaffTicketProcessing::exchange.name') }}</dt><dd class="font-semibold" x-text="confirmedPassenger?.passenger_name"></dd></div>
                    <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.phone') }}</dt><dd class="font-semibold" x-text="confirmedPassenger?.passenger_phone"></dd></div>
                </dl>
            </section>
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-sm font-bold"><x-heroicon-o-arrows-right-left class="size-5 text-futa-orange" />{{ __('Payment::exchange.summary') }}</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.old_fare') }}</dt><dd class="font-semibold" x-text="flow.money(selected?.fare)"></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Payment::exchange.new_fare') }}</dt><dd class="font-semibold" x-text="flow.money(flow.selectedTrip?.fare)"></dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-3"><dt class="font-semibold">{{ __('Payment::exchange.additional') }}</dt><dd class="font-bold text-futa-green" x-text="flow.money(0)"></dd></div>
                </dl>
                <div class="mt-4 flex gap-2 rounded-lg bg-emerald-50 p-3 text-xs leading-5 text-futa-green"><x-heroicon-o-check-circle class="size-5 shrink-0" /><p>{{ __('StaffTicketProcessing::exchange.no_payment') }}</p></div>
            </section>
        </aside>
    </div>
    <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-4">
        <p role="timer" aria-live="off"
            class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
        <x-heroicon-o-clock
            class="size-5 text-futa-orange" />{{ __('Payment::exchange.hold_time') }} <strong
            class="rounded-lg bg-orange-50 px-3 py-1 font-mono text-base text-futa-orange"
            x-text="Math.floor(flow.remaining / 60) + ':' + String(flow.remaining % 60).padStart(2, '0')">
        </strong>
        </p>
        <p x-show="flow.remaining === 0" role="alert" class="text-sm text-red-600">{{ __('Payment::exchange.expired') }}</p>
        <p class="text-xs leading-5 text-slate-500">{{ __('Payment::exchange.warning') }}</p>
        <div class="flex justify-between gap-3 border-t border-slate-100 pt-4">
            <button type="button" @click="back()" :disabled="flow.busy || flow.completionAttempted" class="rounded-lg border border-slate-200 px-5 py-3 font-semibold disabled:opacity-50">{{ __('Payment::exchange.back') }}</button>
            <button type="button" @click="completeExchange()" :disabled="flow.busy || (flow.remaining === 0 && !flow.completionAttempted)" class="rounded-lg bg-futa-orange px-5 py-3 font-semibold text-white disabled:opacity-50">{{ __('Payment::exchange.submit') }}</button>
        </div>
    </div>
</section>

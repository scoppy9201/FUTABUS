<section x-show="step === 2" x-cloak class="space-y-5">
    <div x-show="quoteStale" role="status" class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
        <p>{{ __('Cancellation::cancellation.quote_changed') }}</p>
        <button type="button" @click="chooseTicket({ id: selected.id, usable: true })" :disabled="choosing" class="rounded-lg border border-amber-300 px-4 py-2 font-semibold">{{ __('StaffDashboard::staff.retry') }}</button>
    </div>
    <div class="grid items-start gap-4 xl:grid-cols-3">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <h2 class="flex items-center gap-2 bg-orange-50 px-4 py-3 font-bold"><x-heroicon-o-ticket class="size-5 text-futa-orange" />{{ __('StaffTicketProcessing::cancellation.ticket_info') }}</h2>
            <div class="space-y-5 p-4 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><strong class="block break-all text-lg" x-text="selected?.ticket_code"></strong><p class="mt-1 break-all text-xs text-slate-500" x-text="selected?.booking_code"></p></div>
                    <button type="button" @click="$refs.cancellationTicketDetail.showModal()" aria-haspopup="dialog"
                        class="shrink-0 text-center text-xs text-blue-600">
                        <img x-show="context?.qr" :src="context?.qr || ''" alt="{{ __('Payment::exchange.qr') }}" class="mx-auto mb-1 size-16">
                        {{ __('StaffTicketProcessing::tickets.view') }}
                    </button>
                </div>
                <dl class="space-y-4">
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.route') }}</dt><dd class="mt-1 font-semibold" x-text="selected ? selected.origin_city + ' → ' + selected.destination_city : ''"></dd></div>
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::exchange.departure') }}</dt><dd class="mt-1 font-semibold" x-text="time(selected?.departure_time)"></dd></div>
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.seat') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.seat_code"></dd></div>
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::sale.vehicle_info') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.company_name"></dd><dd class="mt-1 text-xs text-slate-500" x-text="selected?.license_plate"></dd></div>
                    @foreach (['pickup', 'dropoff'] as $stop)
                        <div><dt class="text-slate-500">{{ __('core::booking.'.$stop) }}</dt><dd class="mt-1 font-semibold" x-text="context?.{{ $stop }}"></dd></div>
                    @endforeach
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.customer') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.passenger_name"></dd></div>
                    <div><dt class="text-slate-500">{{ __('StaffTicketProcessing::tickets.phone') }}</dt><dd class="mt-1 font-semibold" x-text="selected?.passenger_phone || '—'"></dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-4"><dt>{{ __('Cancellation::cancellation.price') }}</dt><dd class="font-bold text-futa-orange" x-text="money(selected?.price || 0)"></dd></div>
                </dl>
            </div>
        </section>
        <div class="space-y-4">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <h2 class="flex items-center gap-2 bg-rose-50 px-4 py-3 font-bold text-rose-600"><x-heroicon-o-document-text class="size-5" />{{ __('Cancellation::cancellation.policy') }}</h2>
                <table class="w-full text-sm"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-3 text-left">{{ __('Cancellation::cancellation.before') }}</th><th class="p-3 text-right">{{ __('StaffTicketProcessing::cancellation.refund_rate') }}</th></tr></thead><tbody>
                    @foreach (['over24' => '90%', '4to24' => '70%', 'under4' => null] as $period => $rate)
                        <tr class="border-t border-slate-100"><td class="p-3">{{ __('Cancellation::cancellation.'.$period) }}</td><td class="p-3 text-right font-semibold">{{ $rate ?? __('Cancellation::cancellation.blocked') }}</td></tr>
                    @endforeach
                </tbody></table>
                <p x-show="selected?.original_departure_at" class="p-3 text-xs leading-5 text-slate-500">{{ __('StaffTicketProcessing::cancellation.exchanged_note') }}</p>
            </section>
            <section class="rounded-xl border border-sky-100 bg-sky-50/50 p-4">
                <h2 class="mb-4 font-bold text-sky-700">{{ __('StaffTicketProcessing::cancellation.estimate') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt>{{ __('Cancellation::cancellation.price') }}</dt><dd x-text="money(context?.quote.amount || 0)"></dd></div>
                    <div class="flex justify-between gap-3"><dt>{{ __('Cancellation::cancellation.fee') }} (<span x-text="context?.quote.fee_percent"></span>%)</dt><dd x-text="money(context?.quote.fee || 0)"></dd></div>
                    <div class="flex justify-between gap-3 border-t border-sky-100 pt-3 font-bold"><dt>{{ __('Cancellation::cancellation.refund') }}</dt><dd class="text-lg text-futa-green" x-text="money(context?.quote.refund || 0)"></dd></div>
                </dl>
                <p x-show="quoteStale" class="mt-4 text-xs leading-5 text-slate-500">{{ __('Cancellation::cancellation.quote_changed') }}</p>
            </section>
        </div>
        <div class="space-y-4">
            <form x-ref="reasonForm" novalidate @submit.prevent="proceed()" class="space-y-4 rounded-xl border border-slate-200 bg-white p-4">
                <label class="block text-sm font-semibold">{{ __('Cancellation::cancellation.reason') }} <span class="text-futa-orange">*</span>
                    <select x-model="reason" x-ref="cancellationReason"
                        data-required-message="{{ __('StaffTicketProcessing::cancellation.reason_required') }}"
                        @change="$el.setCustomValidity('')"
                        class="mt-3 h-11 w-full rounded-lg border border-slate-200 px-3 text-sm font-normal">
                        <option value="">{{ __('StaffTicketProcessing::cancellation.choose_reason') }}</option>
                        @foreach (__('Cancellation::cancellation.reasons') as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm font-semibold">{{ __('Cancellation::cancellation.note') }}<textarea x-model="note" maxlength="500" rows="5" class="mt-3 w-full rounded-lg border border-slate-200 p-3 font-normal"></textarea></label>
                <p class="text-right text-xs text-slate-400"><span x-text="note.length"></span>/500</p>
            </form>
            <aside class="rounded-xl border border-amber-100 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                <h3 class="mb-2 font-bold">{{ __('StaffTicketProcessing::cancellation.warning_title') }}</h3>
                <p>{{ __('Cancellation::cancellation.warning') }}</p>
                <p class="mt-3">{{ __('StaffTicketProcessing::cancellation.not_cancelled_yet') }}</p>
            </aside>
        </div>
    </div>
    <div
        class="flex justify-between rounded-xl border border-slate-200 bg-white p-4">
    <button type="button"
        @click="back()"
        class="rounded-lg border border-slate-200 px-5 py-3">{{ __('Cancellation::cancellation.back') }}</button>
    <button type="button"
        @click="proceed()"
        :disabled="quoteStale || choosing"
        class="rounded-lg bg-futa-orange px-6 py-3 font-semibold text-white disabled:opacity-50">{{ __('Cancellation::cancellation.next') }}</button>
    </div>
</section>
<section x-show="step === 3" x-cloak class="mx-auto max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
    <fieldset :disabled="busy || Boolean(pending)" class="flex flex-wrap gap-5 rounded-xl border border-slate-200 p-4">
        <legend class="px-1 font-semibold">{{ __('StaffTicketProcessing::cancellation.refund_method') }}</legend>
        <label class="flex items-center gap-2"><input type="radio" x-model="refundMethod" value="cash" @change="cashPaid = false" class="accent-futa-orange">{{ __('StaffTicketProcessing::cancellation.cash_option') }}</label>
        <label x-show="!context?.cash && context?.can_request_refund" class="flex items-center gap-2"><input type="radio" x-model="refundMethod" value="request" @change="cashPaid = false" class="accent-futa-orange">{{ __('StaffTicketProcessing::cancellation.request_option') }}</label>
    </fieldset>
    <h2 class="text-xl font-bold text-futa-green" x-text="cashRefund ? @js(__('StaffTicketProcessing::cancellation.cash_title')) : @js(__('StaffTicketProcessing::cancellation.online_title'))"></h2>
    <p class="text-sm leading-6 text-slate-500" x-text="cashRefund ? @js(__('StaffTicketProcessing::cancellation.cash_help')) : @js(__('StaffTicketProcessing::cancellation.online_help'))"></p>
    <dl class="space-y-4 rounded-xl bg-slate-50 p-5 text-sm">
        <div class="flex justify-between gap-3"><dt>{{ __('StaffTicketProcessing::tickets.code') }}</dt><dd class="break-all font-semibold" x-text="selected?.ticket_code"></dd></div>
        <div class="flex justify-between gap-3"><dt>{{ __('Cancellation::cancellation.fee') }}</dt><dd x-text="money(context?.quote.fee || 0)"></dd></div>
        <div class="flex justify-between gap-3 border-t border-slate-200 pt-4 font-bold"><dt>{{ __('Cancellation::cancellation.refund') }}</dt><dd class="text-xl text-futa-green" x-text="money(context?.quote.refund || 0)"></dd></div>
    </dl>
    <p x-show="cashRefund && !context?.can_refund_cash" class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">{{ __('StaffTicketProcessing::cancellation.cash_permission') }}</p>
    <label x-show="cashRefund" class="flex items-start gap-3 text-sm leading-6"><input type="checkbox" x-model="cashPaid" :disabled="busy || Boolean(pending) || !context?.can_refund_cash" class="mt-1 accent-futa-orange">{{ __('StaffTicketProcessing::cancellation.cash_confirm') }}</label>
    <p x-show="pending && !busy" role="status" class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">{{ __('StaffTicketProcessing::cancellation.retry') }}</p>
    <div class="flex justify-between gap-3 border-t border-slate-100 pt-4">
        <button type="button" @click="back()" :disabled="busy || Boolean(pending)" class="rounded-lg border border-slate-200 px-5 py-3 disabled:opacity-50">{{ __('Cancellation::cancellation.back') }}</button>
        <button type="button"
            @click="submitCancellation()"
            :disabled="busy || (cashRefund && (!cashPaid || !context.can_refund_cash))"
            class="rounded-lg bg-red-600 px-5 py-3 font-semibold text-white disabled:opacity-50"
            x-text="cashRefund ? @js(__('StaffTicketProcessing::cancellation.cash_submit')) : @js(__('StaffTicketProcessing::cancellation.online_submit'))">
        </button>
    </div>
</section>
<section x-show="step === 4" x-cloak class="mx-auto max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
    <header class="rounded-xl bg-emerald-50 p-5 text-center"><x-heroicon-o-check-circle class="mx-auto mb-3 size-14 text-futa-green" /><h2 class="text-xl font-bold text-futa-green">{{ __('Cancellation::cancellation.success') }}</h2></header>
    <p class="rounded-lg bg-slate-50 p-4 text-sm leading-6" x-text="result?.status === 'refunded' ? @js(__('StaffTicketProcessing::cancellation.cash_done')) : @js(__('StaffTicketProcessing::cancellation.online_done'))"></p>
    <dl class="space-y-4 text-sm">
        <div><dt class="text-slate-500">{{ __('Cancellation::cancellation.reference') }}</dt><dd class="mt-2 break-all font-semibold" x-text="result?.reference"></dd></div>
        <div class="flex justify-between"><dt>{{ __('Cancellation::cancellation.fee') }}</dt><dd x-text="money(result?.fee || 0)"></dd></div>
        <div class="flex justify-between font-bold"><dt>{{ __('Cancellation::cancellation.refund') }}</dt><dd class="text-futa-green" x-text="money(result?.refund || 0)"></dd></div>
        <div x-show="result?.cash_reference"><dt class="text-slate-500">{{ __('StaffTicketProcessing::cancellation.cash_receipt') }}</dt><dd class="mt-2 break-all font-semibold" x-text="result?.cash_reference"></dd></div>
    </dl>
    <div
        class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
    <a href="{{ route('staff.tickets.cancellation') }}"
        class="rounded-lg bg-futa-orange px-5 py-3 font-semibold text-white">{{ __('StaffTicketProcessing::cancellation.another') }}</a>
    <a href="{{ route('staff.tickets') }}"
        class="rounded-lg border border-slate-200 px-5 py-3">{{ __('StaffTicketProcessing::sale.back_processing') }}</a>
    </div>
</section>

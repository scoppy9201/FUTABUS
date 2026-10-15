<form data-cancellation-form data-quote-url="{{ route('cancellation.quote', $booking) }}" data-status-url="{{ route('cancellation.status', $booking) }}"
    action="{{ route('cancellation.store', $booking) }}" method="post" class="p-5 sm:p-7">
    @csrf
    <header class="flex items-start justify-between gap-4">
        <div><h2 class="text-xl font-bold text-gray-950">{{ __('Cancellation::cancellation.title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Cancellation::cancellation.intro') }}</p></div>
        <button type="button" data-cancellation-close aria-label="{{ __('Cancellation::cancellation.close') }}" class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-futa-orange-soft"><x-heroicon-o-x-mark class="size-5" /></button>
    </header>
    <ol class="my-6 grid grid-cols-4 gap-2 text-center text-xs sm:text-sm">
        @foreach(['choose', 'action', 'confirm', 'done'] as $step)
            <li data-cancel-progress="{{ $loop->index }}"
                class="relative flex flex-col items-center gap-2 text-slate-500 after:absolute after:left-[calc(50%+1.25rem)] after:top-4
                    after:w-[calc(100%-2rem)] after:border-t after:border-dashed after:border-gray-200 data-[complete=true]:after:border-futa-green last:after:hidden">
                <span data-cancel-circle class="grid size-8 place-items-center rounded-full border border-current font-bold"><span data-cancel-number>{{ $loop->iteration }}</span><x-heroicon-s-check data-cancel-check class="hidden size-4" /></span>{{ __('Cancellation::cancellation.'.$step) }}
            </li>
        @endforeach
    </ol>
    <div role="alert" data-cancel-error class="mb-4 text-sm font-medium text-red-700"></div>
    @foreach($receipts as $receipt)
        <div class="mb-4 rounded-xl border border-futa-green/20 bg-green-50 p-4 text-sm">
            <p data-refund-status="{{ $receipt->reference }}" class="font-bold text-futa-green">{{ __('Cancellation::cancellation.statuses.'.$receipt->status) }}</p>
            <p class="mt-1 wrap-break-word text-slate-600">{{ $receipt->reference }}</p>
            <p class="mt-1 font-semibold">{{ __('Cancellation::cancellation.refund') }}: {{ number_format($receipt->refund_amount, 0, ',', '.') }} VND</p>
        </div>
    @endforeach
    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="rounded-2xl border border-gray-200 p-5">
            <section data-cancel-step="0">
                <h3 class="mb-4 font-bold">{{ __('Cancellation::cancellation.choose') }}</h3>
                <div class="max-h-64 space-y-3 overflow-y-auto">
                    @foreach($tickets as $ticket)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 has-checked:border-futa-orange has-checked:bg-futa-orange-soft">
                            <input type="radio" name="ticket_id" value="{{ $ticket->id }}" required @disabled($ticket->status !== 'active') class="accent-futa-orange">
                            <span class="min-w-0 text-sm"><strong class="block">{{ $ticket->origin_city }} → {{ $ticket->destination_city }}</strong>
                                <span class="block text-slate-500">{{ \Illuminate\Support\Carbon::parse($ticket->departure_time)->format('d/m/Y H:i') }} · {{ __('Cancellation::cancellation.seat') }} {{ $ticket->seat_code }}</span>
                                <span class="block font-semibold text-futa-orange">{{ $ticket->ticket_code }}</span></span>
                        </label>
                    @endforeach
                </div>
            </section>
            <section data-cancel-step="1" hidden>
                <h3 class="mb-3 font-bold">{{ __('Cancellation::cancellation.reason') }}</h3>
                <div class="space-y-2">
                    @foreach(__('Cancellation::cancellation.reasons') as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold has-checked:border-futa-orange has-checked:bg-futa-orange-soft">
                            <input type="radio" name="reason" value="{{ $value }}" class="accent-futa-orange">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <label class="mt-4 block text-sm font-semibold" for="cancel-note">{{ __('Cancellation::cancellation.note') }}</label>
                <textarea id="cancel-note" name="note" maxlength="500" rows="3" class="mt-2 w-full rounded-xl border border-gray-200 p-3 text-sm focus:border-futa-orange focus:outline-none"></textarea>
                <h3 class="mt-5 font-bold">{{ __('Cancellation::cancellation.policy') }}</h3>
                <table class="mt-3 w-full text-left text-xs sm:text-sm"><thead><tr><th class="pb-2">{{ __('Cancellation::cancellation.before') }}</th><th class="pb-2">{{ __('Cancellation::cancellation.fee') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="py-2">{{ __('Cancellation::cancellation.over24') }}</td><td>10%</td></tr>
                        <tr><td class="py-2">{{ __('Cancellation::cancellation.4to24') }}</td><td>30%</td></tr>
                        <tr><td class="py-2">{{ __('Cancellation::cancellation.under4') }}</td><td>{{ __('Cancellation::cancellation.blocked') }}</td></tr>
                    </tbody>
                </table>
            </section>
            <section data-cancel-step="2" hidden>
                <h3 class="font-bold">{{ __('Cancellation::cancellation.confirm') }}</h3>
                <p data-cancel-reason class="mt-3 text-sm font-medium"></p>
                <p class="mt-2 text-sm text-slate-600">{{ __('Cancellation::cancellation.method') }}</p>
                <p class="my-4 rounded-xl bg-amber-50 p-3 text-sm leading-6 text-amber-800">{{ __('Cancellation::cancellation.warning') }}</p>
                <div class="flex items-start gap-3 text-sm leading-6">
                    <input id="cancel-consent" type="checkbox" name="consent" value="1"
                        aria-labelledby="cancel-consent-label cancel-consent-policy" class="mt-1.5 shrink-0 accent-futa-orange">
                    <p>
                        <label id="cancel-consent-label" for="cancel-consent" class="cursor-pointer">{{ __('Cancellation::cancellation.consent_prefix') }}</label>
                        <a id="cancel-consent-policy" href="{{ route('refund') }}#ticket-change-rules" target="_blank" rel="noopener noreferrer" data-no-loader
                            title="{{ __('Cancellation::cancellation.policy_new_tab') }}"
                            class="rounded-sm font-semibold text-futa-orange underline decoration-futa-orange/50 underline-offset-4 hover:text-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange">
                            {{ __('Cancellation::cancellation.policy_link') }}<x-heroicon-o-arrow-top-right-on-square class="ml-0.5 inline size-3.5 align-middle" aria-hidden="true" />
                        </a>.
                    </p>
                </div>
            </section>
            <section data-cancel-step="3" hidden class="text-center">
                <x-heroicon-o-check-circle class="mx-auto size-14 text-futa-green" />
                <h3 class="mt-3 text-xl font-bold">{{ __('Cancellation::cancellation.success') }}</h3>
                <p class="mt-3 text-sm text-slate-500">{{ __('Cancellation::cancellation.reference') }}</p><p data-cancel-reference class="mt-1 wrap-break-word text-sm font-semibold text-futa-orange"></p>
                <p data-cancel-status class="mt-4 font-semibold text-futa-green"></p><p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Cancellation::cancellation.pending_note') }}</p>
                <button type="button" data-cancel-refresh class="mt-5 rounded-xl bg-futa-orange px-4 py-3 text-sm font-bold text-white">{{ __('Cancellation::cancellation.another') }}</button>
            </section>
            <footer data-cancel-nav class="mt-6 flex justify-between border-t border-gray-100 pt-4">
                <button type="button" data-cancel-back class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-bold">{{ __('Cancellation::cancellation.back') }}</button>
                <button type="button" data-cancel-next class="rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50">{{ __('Cancellation::cancellation.next') }}</button>
                <button type="submit" data-cancel-submit hidden disabled class="rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50">{{ __('Cancellation::cancellation.submit') }}</button>
            </footer>
        </div>
        <aside class="rounded-2xl border border-gray-200 bg-slate-50 p-5" aria-live="polite">
            <h3 class="font-bold">{{ __('Cancellation::cancellation.summary') }}</h3><p data-cancel-route class="mt-4 text-sm font-bold"></p><p data-cancel-seat class="mt-1 text-sm text-slate-500"></p>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt>{{ __('Cancellation::cancellation.price') }}</dt><dd data-cancel-amount>—</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ __('Cancellation::cancellation.fee') }} <span data-cancel-percent></span></dt><dd data-cancel-fee>—</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-gray-200 pt-3 font-bold">
                    <dt>{{ __('Cancellation::cancellation.refund') }}</dt><dd data-cancel-refund>—</dd>
                </div>
            </dl>
        </aside>
    </div>
    <input type="hidden" name="expected_refund">
    <script type="application/json" data-cancel-statuses>@json(__('Cancellation::cancellation.statuses'))</script>
</form>

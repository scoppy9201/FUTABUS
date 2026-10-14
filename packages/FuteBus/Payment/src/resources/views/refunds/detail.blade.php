<article class="p-5">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <h2 data-refund-heading tabindex="-1" class="min-w-0 flex-1 wrap-break-word text-base font-extrabold text-slate-950">{{ $refund->reference }}</h2>
        @include('Payment::refunds.status', ['status' => $refund->status])
    </header>
    <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('Payment::refunds.reason') }}: {{ __('Cancellation::cancellation.reasons.'.$refund->reason) }}</p>
    @if($refund->customer_note)<p class="mt-1 whitespace-pre-wrap wrap-break-word text-xs text-slate-500">{{ $refund->customer_note }}</p>@endif
    <h3 class="mt-5 text-xs font-bold uppercase tracking-wide text-futa-orange">{{ __('Payment::refunds.original') }}</h3>
    <dl class="mt-3 space-y-3 text-sm">
        @foreach(['ticket_code' => 'ticket', 'customer_name' => 'customer', 'transaction_id' => 'transaction', 'payment_code' => 'original'] as $field => $label)
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('Payment::refunds.'.$label) }}</dt>
                <dd class="max-w-3/5 wrap-break-word text-right font-semibold text-slate-800">{{ $refund->$field }}</dd>
            </div>
        @endforeach
        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Payment::refunds.method') }}</dt><dd class="font-semibold text-slate-800">{{ __('Payment::refunds.methods.'.$refund->payment_method) }}</dd></div>
        @foreach(['original_amount' => 'collected', 'fee_amount' => 'fee', 'amount' => 'amount'] as $field => $label)
            <div @class(['flex justify-between gap-4', 'border-t border-slate-200 pt-3 font-bold' => $field === 'amount'])>
                <dt class="text-slate-500">{{ __('Payment::refunds.'.$label) }}</dt>
                <dd @class(['whitespace-nowrap font-bold', 'text-futa-orange' => $field === 'amount'])>
                    {{ $field === 'fee_amount' ? '-' : '' }}{{ number_format($refund->$field, 0, ',', '.') }} ₫
                </dd>
            </div>
        @endforeach
        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Payment::refunds.handler') }}</dt><dd class="text-right font-semibold">{{ $refund->handler_name ?? __('Payment::refunds.unassigned') }}</dd></div>
    </dl>
    @if($refund->bank_reference)
        <p class="mt-4 text-sm">{{ __('Payment::refunds.reference') }}: <strong>{{ $refund->bank_reference }}</strong></p>
        <p class="mt-1 text-sm">{{ __('Payment::refunds.paid_at') }}: {{ $refund->bank_paid_at }}</p>
    @endif
    <h3 class="mt-6 text-xs font-bold uppercase tracking-wide text-futa-orange">{{ __('Payment::refunds.history') }}</h3>
    <ol class="mt-3 space-y-3 border-l border-slate-200 pl-3 text-xs">
        <li>
            <p class="font-semibold text-slate-700">{{ __('Payment::refunds.created') }}</p>
            <p class="mt-1 text-slate-500">{{ \Illuminate\Support\Carbon::parse($refund->cancelled_at)->format('d/m/Y H:i') }}</p>
        </li>
        @foreach($events as $event)
            <li>
                <p class="font-semibold text-slate-700">{{ $event->name }} · {{ __('Payment::refunds.statuses.'.$event->to_status) }}</p>
                <p class="mt-1 text-slate-500">{{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d/m/Y H:i') }}</p>
                @if($event->note)<p class="mt-1 whitespace-pre-wrap wrap-break-word text-slate-500">{{ $event->note }}</p>@endif
            </li>
        @endforeach
    </ol>
    <div data-refund-error role="alert" class="mt-4 text-sm font-semibold text-red-700"></div>
    @if(auth()->user()->hasPermissionTo('payment.refund') && $refund->processing_mode === 'manual')
        @if(in_array($refund->status, ['refund_pending', 'refund_failed'], true))
            <form data-refund-form action="{{ route($refundRoutes.'.update', $refund->id) }}" method="post" class="mt-5"
                data-confirm-title="{{ __('Payment::refunds.claim') }}"
                data-confirm-message="{{ __('Payment::refunds.confirmation.claim', ['reference' => $refund->reference]) }}"
                data-confirm-label="{{ __('Payment::refunds.claim') }}">
                @csrf
                @method('patch')
                <input type="hidden" name="version" value="{{ $refund->version }}"><input type="hidden" name="action" value="claim">
                <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::refunds.claim') }}</button>
            </form>
        @elseif($refund->status === 'refund_processing' && (int) $refund->assigned_to === auth()->id())
            <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">{{ __('Payment::refunds.warning') }}</p>
            <form data-refund-form action="{{ route($refundRoutes.'.update', $refund->id) }}" method="post" class="mt-4 grid gap-4"
                data-confirm-title="{{ __('Payment::refunds.complete') }}"
                data-confirm-message="{{ __('Payment::refunds.confirmation.complete', ['reference' => $refund->reference,
                    'amount' => number_format($refund->amount, 0, ',', '.').' ₫']) }}"
                data-confirm-label="{{ __('Payment::refunds.complete') }}">
                @csrf
                @method('patch')
                <input type="hidden" name="version" value="{{ $refund->version }}"><input type="hidden" name="action" value="complete">
                <label class="text-sm font-semibold">{{ __('Payment::refunds.amount') }}
                    <input name="amount" type="number" value="{{ $refund->amount }}" required min="1" step="1" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2.5">
                </label>
                <label class="text-sm font-semibold">{{ __('Payment::refunds.reference') }}
                    <input name="bank_reference" required maxlength="100" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2.5">
                </label>
                <fieldset>
                    <legend class="text-sm font-semibold">{{ __('Payment::refunds.paid_at') }}</legend>
                    <div class="mt-2 grid grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] gap-2">
                        <input name="bank_paid_date" type="date" required aria-label="{{ __('Payment::refunds.paid_at') }}"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5">
                        @foreach(['hour' => 23, 'minute' => 59] as $unit => $maximum)
                            <select name="bank_paid_{{ $unit }}" required aria-label="{{ __('Payment::refunds.'.$unit) }}" class="w-full rounded-xl border border-gray-200 px-3 py-2.5">
                                <option value="">{{ __('Payment::refunds.'.$unit) }}</option>
                                @for($value = 0; $value <= $maximum; $value++)
                                    <option value="{{ str_pad((string) $value, 2, '0', STR_PAD_LEFT) }}">{{ str_pad((string) $value, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        @endforeach
                    </div>
                </fieldset>
                <label class="text-sm font-semibold">{{ __('Payment::refunds.note') }}
                    <textarea name="note" maxlength="1000" rows="2" class="mt-2 w-full rounded-xl border border-gray-200 p-3"></textarea>
                </label>
                <label class="flex items-start gap-3 text-sm">
                    <input name="bank_verified" value="1" type="checkbox" required class="mt-1 accent-futa-orange">{{ __('Payment::refunds.verified') }}
                </label>
                <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::refunds.complete') }}</button>
            </form>
            <form data-refund-form action="{{ route($refundRoutes.'.update', $refund->id) }}" method="post" class="mt-5 border-t border-gray-200 pt-4"
                data-confirm-title="{{ __('Payment::refunds.fail') }}"
                data-confirm-message="{{ __('Payment::refunds.confirmation.fail', ['reference' => $refund->reference]) }}"
                data-confirm-label="{{ __('Payment::refunds.fail') }}">
                @csrf
                @method('patch')
                <input type="hidden" name="version" value="{{ $refund->version }}"><input type="hidden" name="action" value="fail">
                <label class="text-sm font-semibold">{{ __('Payment::refunds.note') }}
                    <textarea name="note" required maxlength="1000" rows="2" class="mt-2 w-full rounded-xl border border-gray-200 p-3"></textarea>
                </label>
                <button class="mt-3 rounded-xl border border-red-200 px-5 py-3 text-sm font-bold text-red-700">{{ __('Payment::refunds.fail') }}</button>
            </form>
        @elseif($refund->status === 'refund_processing')
            <p class="mt-4 text-sm text-slate-500">{{ __('Payment::refunds.assigned_elsewhere') }}</p>
        @endif
    @endif

</article>

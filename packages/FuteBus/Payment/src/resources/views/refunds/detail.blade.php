<article class="p-5 sm:p-7">
    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">{{ __('Payment::refunds.title') }}</h2>
            <p class="mt-1 wrap-break-word text-sm text-futa-orange">{{ $refund->reference }}</p>
        </div>
        <button type="button" data-refund-close aria-label="{{ __('Payment::refunds.close') }}" class="rounded-full bg-slate-100 p-2">
            <x-heroicon-o-x-mark class="size-5" />
        </button>
    </header>
    <p class="mt-4 rounded-xl bg-futa-orange-soft p-3 text-sm font-semibold">{{ __('Payment::refunds.statuses.'.$refund->status) }}</p>
    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
        @foreach(['ticket_code' => 'ticket', 'customer_name' => 'customer', 'transaction_id' => 'transaction', 'payment_code' => 'original'] as $field => $label)
            <div><dt class="text-slate-500">{{ __('Payment::refunds.'.$label) }}</dt><dd class="mt-1 wrap-break-word font-semibold">{{ $refund->$field }}</dd></div>
        @endforeach
        @foreach(['original_amount' => 'collected', 'fee_amount' => 'fee', 'amount' => 'amount'] as $field => $label)
            <div><dt class="text-slate-500">{{ __('Payment::refunds.'.$label) }}</dt><dd class="mt-1 font-bold">{{ number_format($refund->$field, 0, ',', '.') }} ₫</dd></div>
        @endforeach
        <div><dt class="text-slate-500">{{ __('Payment::refunds.handler') }}</dt><dd class="mt-1 font-semibold">{{ $refund->handler_name ?? __('Payment::refunds.unassigned') }}</dd></div>
    </dl>
    @if($refund->bank_reference)
        <p class="mt-4 text-sm">{{ __('Payment::refunds.reference') }}: <strong>{{ $refund->bank_reference }}</strong></p>
        <p class="mt-1 text-sm">{{ __('Payment::refunds.paid_at') }}: {{ $refund->bank_paid_at }}</p>
    @endif
    <div data-refund-error role="alert" class="mt-4 text-sm font-semibold text-red-700"></div>
    @if(auth()->user()->hasPermissionTo('payment.refund') && $refund->processing_mode === 'manual')
        @if(in_array($refund->status, ['refund_pending', 'refund_failed'], true))
            <form data-refund-form action="{{ route('refunds.update', $refund->id) }}" method="post" class="mt-5">
                @csrf
                <input type="hidden" name="version" value="{{ $refund->version }}"><input type="hidden" name="action" value="claim">
                <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::refunds.claim') }}</button>
            </form>
        @elseif($refund->status === 'refund_processing' && (int) $refund->assigned_to === auth()->id())
            <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">{{ __('Payment::refunds.warning') }}</p>
            <form data-refund-form action="{{ route('refunds.update', $refund->id) }}" method="post" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="version" value="{{ $refund->version }}"><input type="hidden" name="action" value="complete">
                <label class="text-sm font-semibold">{{ __('Payment::refunds.amount') }}
                    <input name="amount" type="number" value="{{ $refund->amount }}" required min="1" step="1" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2.5">
                </label>
                <label class="text-sm font-semibold">{{ __('Payment::refunds.reference') }}
                    <input name="bank_reference" required maxlength="100" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2.5">
                </label>
                <fieldset class="sm:col-span-2">
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
                <label class="text-sm font-semibold sm:col-span-2">{{ __('Payment::refunds.note') }}
                    <textarea name="note" maxlength="1000" rows="2" class="mt-2 w-full rounded-xl border border-gray-200 p-3"></textarea>
                </label>
                <label class="flex items-start gap-3 text-sm sm:col-span-2">
                    <input name="bank_verified" value="1" type="checkbox" required class="mt-1 accent-futa-orange">{{ __('Payment::refunds.verified') }}
                </label>
                <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white sm:col-span-2">{{ __('Payment::refunds.complete') }}</button>
            </form>
            <form data-refund-form action="{{ route('refunds.update', $refund->id) }}" method="post" class="mt-5 border-t border-gray-200 pt-4">
                @csrf
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
    <h3 class="mt-6 font-bold">{{ __('Payment::refunds.history') }}</h3>
    <ol class="mt-3 space-y-3">
        @foreach($events as $event)
            <li class="rounded-xl border border-gray-200 p-3 text-sm">
                <p class="font-semibold">{{ $event->name }} · {{ __('Payment::refunds.statuses.'.$event->to_status) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $event->created_at }}</p>
                <p class="mt-2 whitespace-pre-wrap wrap-break-word">{{ $event->note }}</p>
            </li>
        @endforeach
    </ol>
</article>

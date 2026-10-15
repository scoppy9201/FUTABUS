<section class="mt-6 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
    <h3 class="text-sm font-bold text-slate-900">{{ __('Payment::refunds.assign') }}</h3>
    <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('Payment::refunds.assign_hint') }}</p>
    @if($eligibleHandlers->isEmpty())
        <p class="mt-3 text-sm text-slate-600">{{ __('Payment::refunds.no_eligible_handlers') }}</p>
    @else
        <form data-refund-form novalidate action="{{ route($refundRoutes.'.update', $refund->id) }}" method="post" class="mt-4 space-y-4"
            data-confirm-title="{{ __('Payment::refunds.assign') }}"
            data-confirm-message="{{ __('Payment::refunds.confirmation.assign', ['reference' => $refund->reference]) }}"
            data-confirm-label="{{ __('Payment::refunds.assign') }}">
            @csrf
            @method('patch')
            <input type="hidden" name="version" value="{{ $refund->version }}">
            <input type="hidden" name="action" value="assign">
            <label class="block text-sm font-semibold text-slate-800">
                {{ __('Payment::refunds.handler') }}
                <select name="assigned_to" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <option value="">{{ __('Payment::refunds.choose_handler') }}</option>
                    @foreach($eligibleHandlers as $handler)
                        <option value="{{ $handler->id }}" @selected((int) $refund->assigned_to === $handler->id)>{{ $handler->name }}</option>
                    @endforeach
                </select>
                <span id="refund-{{ $refund->id }}-handler-error" data-refund-field-error="assigned_to" hidden
                    class="mt-1 block text-xs font-medium text-red-600" aria-live="polite"></span>
            </label>
            <label class="block text-sm font-semibold text-slate-800">
                {{ __('Payment::refunds.assignment_note') }}
                <textarea name="note" maxlength="1000" rows="2" @required($refund->status === 'refund_processing')
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white p-3"></textarea>
                <span id="refund-{{ $refund->id }}-assign-note-error" data-refund-field-error="note" hidden
                    class="mt-1 block text-xs font-medium text-red-600" aria-live="polite"></span>
            </label>
            <button class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-bold text-white hover:bg-futa-orange-dark">
                <x-heroicon-o-user-plus class="size-4" aria-hidden="true" />{{ __('Payment::refunds.assign') }}
            </button>
        </form>
    @endif
</section>

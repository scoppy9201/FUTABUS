<section class="border-t border-gray-100 pt-4">
    <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-arrows-right-left class="size-5 text-futa-orange" />{{ __('StaffTicketProcessing::exchange.receipt_title') }}</h4>
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 sm:p-5">
        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach (['old_fare', 'new_fare', 'fee'] as $amount)
                <div class="flex items-center justify-between gap-3 text-sm"><dt class="text-slate-500">{{ __('Payment::exchange.'.$amount) }}</dt><dd class="font-semibold">{{ number_format($exchangeDetails[$amount], 0, ',', '.') }}đ</dd></div>
            @endforeach
            <div class="flex items-center justify-between gap-3 text-sm"><dt class="font-semibold">{{ __('StaffTicketProcessing::exchange.additional_collected') }}</dt><dd class="text-xl font-bold text-futa-green">{{ number_format($exchangeDetails['additional'], 0, ',', '.') }}đ</dd></div>
        </dl>
        <div class="mt-4 flex items-start gap-2 border-t border-emerald-100 pt-4 text-sm leading-6 text-futa-green"><x-heroicon-o-check-circle class="mt-0.5 size-5 shrink-0" /><p>{{ __('StaffTicketProcessing::exchange.no_payment') }}</p></div>
    </div>
</section>

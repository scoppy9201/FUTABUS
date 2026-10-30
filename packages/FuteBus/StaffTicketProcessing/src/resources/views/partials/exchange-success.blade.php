<div class="ticket-success mx-auto w-full max-w-300 space-y-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6" data-link-copied="{{ __('Payment::payment.success_link_copied') }}">
    <header class="rounded-xl bg-green-50 p-5 text-center">
        <span class="mx-auto mb-3 grid size-14 place-items-center rounded-full bg-[#93ded3] text-white"><x-heroicon-o-check class="size-8" /></span>
        <h2 id="ticket-success-title" class="text-2xl font-bold text-futa-green">{{ __('Payment::exchange.completed') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Payment::exchange.new_qr_note') }}</p>
    </header>
    <div class="rounded-xl border border-gray-200">
        <h3 class="border-b border-gray-100 bg-gray-50 px-5 py-4 font-bold text-futa-green">{{ __('StaffTicketProcessing::sale.issued_tickets') }}</h3>
        <div class="ticket-success__tickets mx-auto max-w-200 p-4 sm:p-6" data-ticket-list>
            @include('StaffTicketProcessing::partials.issued-ticket')
        </div>
        <div class="flex flex-wrap justify-center gap-3 border-t border-gray-100 p-5 print:hidden">
            <button type="button" data-print-tickets class="inline-flex items-center gap-2 rounded-full bg-futa-orange-soft px-5 py-3 font-bold text-futa-orange"><x-heroicon-o-printer class="size-5" />{{ __('StaffTicketProcessing::sale.print_tickets') }}</button>
            <a href="{{ route('staff.tickets.exchange') }}" class="rounded-full bg-futa-orange px-5 py-3 font-bold text-white">{{ __('StaffTicketProcessing::exchange.another') }}</a>
            <a href="{{ route('staff.tickets') }}" class="rounded-full border border-gray-200 px-5 py-3 font-semibold">{{ __('StaffTicketProcessing::sale.back_processing') }}</a>
        </div>
    </div>
</div>

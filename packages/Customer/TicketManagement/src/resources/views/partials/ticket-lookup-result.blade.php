@php
    $booking = $result['booking'];
    $departure = \Illuminate\Support\Carbon::parse($booking->departure_time);
    $pickup = $booking->origin_station ?: $booking->origin_city;
    $dropoff = $booking->destination_station ?: $booking->destination_city;
    $ticketCount = $result['tickets']->count();
@endphp

<section aria-labelledby="lookup-result-title" class="mx-auto mt-10 max-w-225 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_12px_32px_rgba(27,40,34,.08)]" data-lookup-tickets data-link-copied="{{ __('core::ticket-lookup.link_copied') }}">
    <div class="border-b border-gray-100 bg-[#f6f7f7] px-5 py-3 text-center sm:px-8">
        <h2 id="lookup-result-title" class="text-sm font-extrabold uppercase tracking-wide text-gray-800">{{ __('core::ticket-lookup.purchase_information') }}</h2>
    </div>

    <dl class="grid gap-x-10 gap-y-3 px-5 py-5 text-sm sm:grid-cols-2 sm:px-8">
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.customer_name') }}</dt><dd class="font-semibold text-gray-900">{{ $booking->customer_name }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.total_amount') }}</dt><dd class="font-extrabold text-futa-orange">{{ number_format((float) $booking->total_amount, 0, ',', '.') }}đ</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.customer_phone') }}</dt><dd class="font-semibold text-gray-900">{{ $booking->customer_phone }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.payment_method') }}</dt><dd class="font-semibold text-gray-900">{{ $result['paymentMethod'] ? __('core::ticket-lookup.methods.'.$result['paymentMethod']) : '—' }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.customer_email') }}</dt><dd class="break-all font-semibold text-gray-900">{{ $booking->customer_email ?: '—' }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2">
            <dt class="text-gray-500">{{ __('core::ticket-lookup.payment_status_label') }}</dt>
            <dd @class([
                'font-bold',
                'text-futa-green' => $result['paymentStatus'] === 'completed',
                'text-futa-orange' => in_array($result['paymentStatus'], ['unpaid', 'pending'], true),
                'text-red-600' => in_array($result['paymentStatus'], ['failed', 'refunded'], true),
            ])>{{ __('core::ticket-lookup.payment_status.'.$result['paymentStatus']) }}</dd>
        </div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.booking_code') }}</dt><dd class="break-all font-semibold text-gray-900">{{ $booking->booking_code }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2">
            <dt class="whitespace-nowrap text-gray-500">{{ __('core::ticket-lookup.booking_status_label') }}</dt>
            <dd @class([
                'font-bold',
                'text-futa-green' => in_array($booking->status, ['confirmed', 'completed'], true),
                'text-futa-orange' => $booking->status === 'pending',
                'text-red-600' => $booking->status === 'cancelled',
            ])>{{ __('core::ticket-lookup.booking_status.'.$booking->status) }}</dd>
        </div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.operator') }}</dt><dd class="font-semibold text-gray-900">{{ $booking->company_name }}</dd></div>
        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2"><dt class="text-gray-500">{{ __('core::ticket-lookup.license_plate') }}</dt><dd class="font-semibold text-gray-900">{{ $booking->license_plate }}</dd></div>
    </dl>

    <div class="border-t border-gray-100 bg-[#fcfdfc] px-4 py-6 sm:px-8">
        <div class="relative mx-auto max-w-85">
            @if($ticketCount > 1)
                <button
                    type="button"
                    data-ticket-prev
                    aria-label="{{ __('core::ticket-lookup.previous_ticket') }}"
                    class="absolute top-1/2 -left-3 z-10 grid size-10 -translate-y-1/2 place-items-center rounded-full border border-gray-200 bg-white text-futa-green shadow-sm transition hover:border-futa-orange hover:text-futa-orange disabled:cursor-not-allowed disabled:opacity-40 sm:-left-12"
                ><x-heroicon-o-chevron-left class="size-5" aria-hidden="true" /></button>
                <button
                    type="button"
                    data-ticket-next
                    aria-label="{{ __('core::ticket-lookup.next_ticket') }}"
                    class="absolute top-1/2 -right-3 z-10 grid size-10 -translate-y-1/2 place-items-center rounded-full border border-gray-200 bg-white text-futa-green shadow-sm transition hover:border-futa-orange hover:text-futa-orange disabled:cursor-not-allowed disabled:opacity-40 sm:-right-12"
                ><x-heroicon-o-chevron-right class="size-5" aria-hidden="true" /></button>
            @endif

            @foreach($result['tickets'] as $ticket)
                <article
                    data-ticket-slide
                    data-ticket-share-text="{{ __('core::ticket-lookup.share_text', [
                        'code' => $ticket->ticket_code,
                        'phone' => $booking->customer_phone,
                        'url' => route('ticket-lookup'),
                    ]) }}"
                    @if(! $loop->first) hidden style="display:none" @endif
                    class="flex flex-col overflow-hidden rounded-xl border-2 border-[#e8eced] bg-white"
                >
                    <div class="grid min-h-14 grid-cols-[38px_minmax(0,1fr)_38px] items-center gap-2 px-3 py-2 text-center">
                        @if(isset($qrCodes[$ticket->ticket_code]))
                        <a
                            data-ticket-download
                            href="{{ $qrCodes[$ticket->ticket_code] }}"
                            download="{{ $ticket->ticket_code }}.svg"
                            aria-label="{{ __('core::ticket-lookup.download_ticket', ['code' => $ticket->ticket_code]) }}"
                            class="grid size-9 place-items-center rounded-full bg-[#f2f4f5] text-[#77818a] transition hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-futa-orange"
                        ><x-heroicon-o-arrow-down-tray class="size-5" aria-hidden="true" /></a>
                        @else
                            <span class="size-9" aria-hidden="true"></span>
                        @endif
                        <p class="truncate text-sm font-bold text-gray-900">{{ __('core::ticket-lookup.ticket_code', ['code' => $ticket->ticket_code]) }}</p>
                        <button
                            type="button"
                            data-share-current
                            aria-label="{{ __('core::ticket-lookup.share_ticket', ['code' => $ticket->ticket_code]) }}"
                            class="grid size-9 place-items-center rounded-full bg-[#f2f4f5] text-[#77818a] transition hover:text-futa-orange focus-visible:outline-2 focus-visible:outline-futa-orange"
                        ><x-heroicon-o-share class="size-5" aria-hidden="true" /></button>
                    </div>

                    @if(isset($qrCodes[$ticket->ticket_code]))
                        <img src="{{ $qrCodes[$ticket->ticket_code] }}" alt="{{ __('core::ticket-lookup.qr_alt', ['code' => $ticket->ticket_code]) }}" class="mx-auto my-1 mb-4 aspect-square w-[min(260px,80%)] object-contain">
                    @else
                        <div class="mx-auto my-6 grid size-32 place-items-center rounded-full bg-slate-100 text-slate-400">
                            <x-heroicon-o-no-symbol class="size-16" aria-hidden="true" />
                        </div>
                    @endif

                    <dl class="grid gap-3.5 px-4 pb-5 text-sm">
                        <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('core::ticket-lookup.route') }}</dt><dd class="text-right font-semibold text-futa-green">{{ $booking->origin_city }} – {{ $booking->destination_city }}</dd></div>
                        <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('core::ticket-lookup.departure') }}</dt><dd class="text-right font-semibold text-futa-green">{{ $departure->format('H:i d/m/Y') }}</dd></div>
                        <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('core::ticket-lookup.seat') }}</dt><dd class="text-right font-semibold text-futa-green">{{ $ticket->seat_code ?: __('core::ticket-lookup.unassigned') }}</dd></div>
                        <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('core::ticket-lookup.pickup') }}</dt><dd class="text-right font-semibold text-gray-900">{{ $pickup }}</dd></div>
                        <div class="grid grid-cols-[100px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('core::ticket-lookup.fare') }}</dt><dd class="text-right font-semibold text-gray-900">{{ number_format((float) $ticket->price, 0, ',', '.') }}đ</dd></div>
                    </dl>

                    @if($ticket->status === 'active')
                        <p class="mt-auto bg-[#f5f7f7] px-4 py-3.5 text-center text-xs leading-[1.45] text-[#28744f]">{{ __('Payment::payment.success_ticket_note') }}</p>
                    @else
                        <p class="mt-auto bg-[#f5f7f7] px-4 py-3.5 text-center text-xs font-semibold text-red-600">{{ __('core::ticket-lookup.ticket_status.'.($ticket->display_status ?? $ticket->status)) }}</p>
                    @endif
                </article>
            @endforeach
        </div>

        @if($ticketCount > 1)
            <p class="mt-4 text-center text-sm font-semibold text-gray-600" aria-live="polite"><span data-ticket-current>1</span> / {{ $ticketCount }}</p>
        @endif
        <div class="mt-5 flex flex-wrap justify-center gap-3">
            <button
                type="button"
                data-share-current
                class="inline-flex min-w-34 items-center justify-center gap-2 rounded-full bg-futa-orange-soft px-5 py-2 text-sm font-bold text-futa-orange transition hover:bg-[#ffe8dc] focus-visible:outline-2 focus-visible:outline-futa-orange"
            ><x-heroicon-o-share class="size-4" aria-hidden="true" />{{ __('core::ticket-lookup.share') }}</button>
            <button
                type="button"
                data-print-ticket
                class="inline-flex min-w-34 items-center justify-center gap-2 rounded-full bg-futa-orange-soft px-5 py-2 text-sm font-bold text-futa-orange transition hover:bg-[#ffe8dc] focus-visible:outline-2 focus-visible:outline-futa-orange"
            ><x-heroicon-o-arrow-down-tray class="size-4" aria-hidden="true" />{{ __('core::ticket-lookup.download') }}</button>
        </div>
    </div>
</section>

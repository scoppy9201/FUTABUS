<article class="ticket-success__ticket flex w-[min(340px,85vw)] shrink-0 snap-start flex-col overflow-hidden rounded-2xl border-2 border-[#e8eced] bg-white print:m-[1%] print:inline-flex print:w-[45%] print:break-inside-avoid" id="ticket-{{ $ticket['code'] }}">
    <div class="flex min-h-14.25 items-center justify-between gap-2.5 px-4 py-2 font-bold">
        <span>{{ __('Payment::payment.success_ticket_code', ['code' => $ticket['code']]) }}</span>
        <div class="flex gap-2 print:hidden">
            <a class="grid size-9.5 shrink-0 place-items-center rounded-full bg-[#f2f4f5] text-[#77818a] hover:brightness-95
                focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange [&_svg]:size-5" href="{{ $ticket['qr'] }}" download="{{ $ticket['code'] }}.svg" aria-label="{{ __('Payment::payment.success_download_one', ['code' => $ticket['code']]) }}">
                <x-heroicon-o-arrow-down-tray aria-hidden="true" />
            </a>
            <button type="button" class="grid size-9.5 shrink-0 cursor-pointer place-items-center rounded-full bg-[#f2f4f5] text-[#77818a] hover:brightness-95
                focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange [&_svg]:size-5"
                    data-share-ticket data-share-url="{{ $shareUrl }}"
                    data-share-text="{{ __('Payment::payment.success_share_text', ['code' => $ticket['code']]) }}"
                    aria-label="{{ __('Payment::payment.success_share_one', ['code' => $ticket['code']]) }}">
                <x-heroicon-o-share aria-hidden="true" />
            </button>
        </div>
    </div>
    <img class="mx-auto my-1.25 mb-3.75 block aspect-square w-[min(260px,80%)] object-contain" src="{{ $ticket['qr'] }}" alt="{{ __('Payment::payment.success_qr_alt', ['code' => $ticket['code']]) }}">
    <dl class="grid gap-3.5 px-4 pb-5 text-[15px]">
        <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('Payment::payment.success_route') }}</dt><dd class="m-0 wrap-break-word text-right font-semibold text-futa-green">{{ $trip['origin'] }} – {{ $trip['destination'] }}</dd></div>
        <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('Payment::payment.success_departure') }}</dt><dd class="m-0 wrap-break-word text-right font-semibold text-futa-green">{{ $departure->format('H:i d/m/Y') }}</dd></div>
        <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('Payment::payment.success_seat') }}</dt><dd class="m-0 wrap-break-word text-right font-semibold text-futa-green">{{ $ticket['seat'] }}</dd></div>
        <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2">
            <dt class="text-[#859098]">{{ __('Payment::payment.success_pickup') }}</dt>
            <dd class="m-0 wrap-break-word text-right font-semibold text-futa-green">{{ $preview['pickup']['name'] }}
                <small class="mt-1 block text-xs font-normal text-[#8c979d]">{{ $preview['pickup']['address'] ?? '' }}</small>
            </dd>
        </div>
        <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2"><dt class="text-[#859098]">{{ __('Payment::payment.success_fare') }}</dt><dd class="m-0 wrap-break-word text-right font-semibold text-futa-green">{{ number_format($ticket['price'], 0, ',', '.') }}đ</dd></div>
    </dl>
    <p class="mt-auto bg-[#f5f7f7] px-4 py-3.5 text-center text-[13px] leading-[1.45] text-[#28744f]">{{ __('Payment::payment.success_ticket_note') }}</p>
</article>

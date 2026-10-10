@extends('core::layouts.home')

@push('styles')
    @vite('packages/FuteBus/Payment/src/resources/css/app.css')
@endpush

@push('scripts')
    @vite('packages/FuteBus/Payment/src/resources/js/app.js')
@endpush

@section('title', __('Payment::payment.sepay_success_title'))

@section('content')
    @php
        $trip = $preview['trip'];
        $departure = \Illuminate\Support\Carbon::parse($trip['departure_time']);
        $shareUrl = route('ticket-lookup');
    @endphp
    <div class="min-h-screen bg-[#fff8f5] px-6 pt-7 pb-12 text-[#252b2b] print:bg-white print:p-0 max-sm:p-3">
        <main class="ticket-success mx-auto w-full max-w-375 rounded-[28px] bg-white px-16 pt-4.5 pb-7.5 shadow-[0_18px_48px_rgb(85_57_43_/_12%)] max-[900px]:px-5.5 max-[900px]:pt-5 max-sm:rounded-[18px] max-sm:px-2.5 max-sm:pt-4.5 print:p-0 print:shadow-none" aria-labelledby="ticket-success-title" data-link-copied="{{ __('Payment::payment.success_link_copied') }}">
            <div class="text-center">
                <span class="mx-auto mb-3 grid size-23 place-items-center rounded-full bg-[#93ded3] text-white max-sm:size-17.5" aria-hidden="true">
                    <x-heroicon-o-check class="size-13.75 stroke-[2.5] max-sm:size-10.75" />
                </span>
                <h1 id="ticket-success-title" class="m-0 text-[clamp(27px,2.4vw,38px)] font-bold text-[#23684b]">{{ __('Payment::payment.sepay_success_title') }}</h1>
                <p class="mt-2 mb-7 text-[17px] leading-normal max-sm:text-sm">{{ __('Payment::payment.success_email_note', ['email' => $preview['customer']['email']]) }}</p>
            </div>

            <section class="overflow-hidden rounded-[17px] border-2 border-[#e8eced]" aria-labelledby="ticket-success-info-title">
                <h2 id="ticket-success-info-title" class="m-0 bg-[#f6f6f6] p-4 text-center text-[19px] font-extrabold uppercase">{{ __('Payment::payment.success_info_title') }}</h2>
                <div class="grid grid-cols-2 gap-7 px-7 py-6 max-[900px]:grid-cols-1 max-sm:px-3.5 max-sm:py-4.5 max-sm:text-sm">
                    <dl class="grid gap-3.25">
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_name') }}</dt><dd class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['name'] }}</dd></div>
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_phone') }}</dt><dd class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['phone'] }}</dd></div>
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">Email</dt><dd class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['email'] }}</dd></div>
                    </dl>
                    <dl class="grid gap-3.25">
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_total') }}</dt><dd class="m-0 font-semibold wrap-break-word">{{ number_format($total, 0, ',', '.') }}đ</dd></div>
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_method') }}</dt><dd class="m-0 font-semibold wrap-break-word">SePay</dd></div>
                        <div class="grid grid-cols-[140px_minmax(0,1fr)] gap-2 max-sm:grid-cols-[106px_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_status') }}</dt><dd class="m-0 font-semibold text-[#62ac74]">{{ __('Payment::payment.success_paid') }}</dd></div>
                    </dl>
                </div>

                <div class="relative">
                    <button type="button" class="absolute top-[45%] left-1.25 z-10 grid size-12 cursor-pointer place-items-center rounded-full bg-white text-[#5c666a] shadow-[0_2px_12px_rgb(0_0_0_/_16%)] hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange print:hidden [&_svg]:size-6.25" data-ticket-scroll="prev" aria-label="{{ __('Payment::payment.success_previous') }}">
                        <x-heroicon-o-chevron-left aria-hidden="true" />
                    </button>
                    <div class="ticket-success__tickets flex snap-x snap-mandatory gap-4.5 overflow-x-auto px-7 pb-4.5 scroll-smooth [scrollbar-width:thin] max-sm:px-3 max-sm:pb-3.75 print:block print:overflow-visible" data-ticket-list>
                        @foreach ($tickets as $ticket)
                            @include('Payment::partials.payment-success-ticket', ['ticket' => $ticket, 'trip' => $trip, 'departure' => $departure, 'shareUrl' => $shareUrl])
                        @endforeach
                    </div>
                    <button type="button" class="absolute top-[45%] right-1.25 z-10 grid size-12 cursor-pointer place-items-center rounded-full bg-white text-[#5c666a] shadow-[0_2px_12px_rgb(0_0_0_/_16%)] hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange print:hidden [&_svg]:size-6.25" data-ticket-scroll="next" aria-label="{{ __('Payment::payment.success_next') }}">
                        <x-heroicon-o-chevron-right aria-hidden="true" />
                    </button>
                </div>

                <div class="flex justify-center gap-6.25 px-4 py-7 max-sm:gap-2.5 print:hidden">
                    <button type="button" class="inline-flex min-w-43.75 cursor-pointer items-center justify-center gap-2.5 rounded-full bg-futa-orange-soft px-5.5 py-2.5 text-base font-bold text-futa-orange hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange max-sm:min-w-0 max-sm:text-sm [&_svg]:size-5" data-share-ticket data-share-url="{{ $shareUrl }}" data-share-text="{{ __('Payment::payment.success_share_text', ['code' => $bookingCode]) }}">
                        <x-heroicon-o-share aria-hidden="true" />{{ __('Payment::payment.success_share') }}
                    </button>
                    <button type="button" class="inline-flex min-w-43.75 cursor-pointer items-center justify-center gap-2.5 rounded-full bg-futa-orange-soft px-5.5 py-2.5 text-base font-bold text-futa-orange hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange max-sm:min-w-0 max-sm:text-sm [&_svg]:size-5" data-print-tickets>
                        <x-heroicon-o-arrow-down-tray aria-hidden="true" />{{ __('Payment::payment.success_download') }}
                    </button>
                </div>
            </section>
            <a class="mx-auto mt-5 table font-bold text-futa-orange underline focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange print:hidden" href="{{ route('ticket-lookup') }}">{{ __('Payment::payment.sepay_look_up_ticket') }}</a>
        </main>
    </div>
@endsection

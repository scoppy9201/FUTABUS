
    @php
        $shareUrl = route('ticket-lookup');
    @endphp
    <div class="bg-gray-50 px-2 py-4 text-slate-900 print:bg-white print:p-0 max-sm:p-3">
        <main class="ticket-success mx-auto w-full max-w-300 rounded-2xl border border-gray-100 bg-white px-6 py-6 shadow-sm
            max-[900px]:px-5.5 max-[900px]:pt-5 max-sm:rounded-[18px] max-sm:px-2.5 max-sm:pt-4.5 print:p-0" aria-labelledby="ticket-success-title" data-link-copied="{{ __('Payment::payment.success_link_copied') }}">
            <div class="mb-5 rounded-xl bg-green-50 p-5 text-center">
                <span class="mx-auto mb-3 grid size-14 place-items-center rounded-full bg-[#93ded3] text-white max-sm:size-12" aria-hidden="true">
                    <x-heroicon-o-check class="size-8 stroke-[2.5] max-sm:size-7" />
                </span>
                <h1 id="ticket-success-title" class="m-0 text-2xl font-bold text-[#23684b]">{{ __('StaffTicketProcessing::sale.success_title') }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ ($cashReceipt ?? false) ? __('StaffTicketProcessing::sale.cash_receipt') : __('Payment::payment.success_email_note', ['email' => $preview['customer']['email']]) }}</p>
            </div>

            <section class="overflow-hidden rounded-xl border border-gray-200" aria-labelledby="ticket-success-info-title">
                <h2 id="ticket-success-info-title" class="m-0 border-b border-gray-100 bg-gray-50 px-5 py-4 text-left text-base font-bold text-futa-green">{{ __('Payment::payment.success_info_title') }}</h2>
                <div class="grid grid-cols-2 gap-5 px-5 py-5 max-[900px]:grid-cols-1 max-sm:px-3.5 max-sm:py-4.5 max-sm:text-sm">
                    <dl class="grid content-start gap-4 rounded-xl bg-gray-50 p-4 text-sm">
                        <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('Payment::payment.success_name') }}</dt><dd class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['name'] }}</dd></div>
                        <div
                            class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <dt
                            class="text-[#859098]">{{ __('Payment::payment.success_phone') }}</dt>
                        <dd
                            class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['phone'] }}</dd>
                        </div>
                        <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]"><dt class="text-[#859098]">{{ __('core::booking.email') }}</dt><dd class="m-0 font-semibold wrap-break-word">{{ $preview['customer']['email'] }}</dd></div>
                    </dl>
                    <dl class="grid content-start gap-4 rounded-xl bg-gray-50 p-4 text-sm">
                        <div
                            class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <dt
                            class="text-[#859098]">{{ __('Payment::payment.success_total') }}</dt>
                        <dd
                            class="m-0 text-lg font-bold text-futa-orange wrap-break-word">{{ number_format($total, 0, ',', '.') }}đ</dd>
                        </div>
                        <div
                            class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <dt
                            class="text-[#859098]">{{ __('StaffTicketProcessing::sale.payment_method') }}</dt>
                        <dd
                            class="m-0 font-semibold wrap-break-word" data-payment-method>{{ $paymentMethodLabel }}</dd>
                        </div>
                        <div
                            class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2 max-sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <dt
                            class="text-[#859098]">{{ __('Payment::payment.success_status') }}</dt>
                        <dd
                            class="m-0 font-semibold text-[#62ac74]">{{ __('Payment::payment.success_paid') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative border-t border-gray-100 pt-5"><h2 class="mb-4 px-5 text-base font-bold text-futa-green">{{ __('StaffTicketProcessing::sale.issued_tickets') }}</h2>
                    <button type="button" class="ticket-success__scroll-button absolute top-[45%] left-1.25 z-10 grid size-12 cursor-pointer place-items-center rounded-full bg-white text-[#5c666a]
                        hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3
                        focus-visible:outline-futa-orange print:hidden [&_svg]:size-6.25" data-ticket-scroll="prev" aria-label="{{ __('Payment::payment.success_previous') }}">
                        <x-heroicon-o-chevron-left aria-hidden="true" />
                    </button>
                    <div class="ticket-success__tickets mx-auto flex w-full max-w-200 snap-x snap-mandatory gap-6 overflow-x-auto px-3 pb-6 scroll-smooth print:max-w-none print:block print:overflow-visible" data-ticket-list>
                        @foreach ($tickets as $ticket)
                            @include('StaffTicketProcessing::partials.issued-ticket', [
                                'ticket' => $ticket,
                                'shareUrl' => $shareUrl,
                            ])
                        @endforeach
                    </div>
                    <button type="button" class="ticket-success__scroll-button absolute top-[45%] right-1.25 z-10 grid size-12 cursor-pointer place-items-center rounded-full bg-white text-[#5c666a]
                        hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3
                        focus-visible:outline-futa-orange print:hidden [&_svg]:size-6.25" data-ticket-scroll="next" aria-label="{{ __('Payment::payment.success_next') }}">
                        <x-heroicon-o-chevron-right aria-hidden="true" />
                    </button>
                </div>

                <div class="flex flex-wrap justify-center gap-4 px-4 py-7 print:hidden">
                    <button type="button" class="inline-flex min-w-43.75 cursor-pointer items-center justify-center gap-2.5 rounded-full bg-futa-orange-soft px-5.5 py-2.5
                        text-base font-bold text-futa-orange hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3
                        focus-visible:outline-futa-orange max-sm:min-w-0 max-sm:text-sm [&_svg]:size-5" data-share-ticket data-share-url="{{ $shareUrl }}" data-share-text="{{ __('Payment::payment.success_share_text', ['code' => $bookingCode]) }}">
                        <x-heroicon-o-share aria-hidden="true" />{{ __('Payment::payment.success_share') }}
                    </button>
                    <button type="button" class="inline-flex min-w-43.75 cursor-pointer items-center justify-center gap-2.5 rounded-full bg-futa-orange-soft px-5.5 py-2.5
                        text-base font-bold text-futa-orange hover:brightness-95 focus-visible:outline-3 focus-visible:outline-offset-3
                        focus-visible:outline-futa-orange max-sm:min-w-0 max-sm:text-sm [&_svg]:size-5" data-print-tickets>
                        <x-heroicon-o-printer aria-hidden="true" />{{ __('StaffTicketProcessing::sale.print_tickets') }}
                    </button>
                    <a
                        href="{{ route(Auth::user()->isBusCompany() ? 'dashboard' : 'staff.dashboard') }}"
                        class="inline-flex min-w-43.75 items-center justify-center gap-2.5 rounded-full bg-futa-orange px-5.5 py-2.5 text-base font-bold text-white hover:bg-futa-orange-dark
                            max-sm:min-w-0 max-sm:text-sm">
                    <x-heroicon-o-home
                        class="size-5" />{{ __('StaffDashboard::staff.home') }}</a>
                </div>
            </section>
            <a class="mx-auto mt-5 table font-bold text-futa-orange underline focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-futa-orange print:hidden" href="{{ route('staff.tickets') }}">{{ __('Payment::payment.sepay_look_up_ticket') }}</a>
        </main>
    </div>

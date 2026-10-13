@php
    $outbound = $ticket['trip']['id'] === ($preview['outbound_trip']['id'] ?? null);
    $pickup = $outbound ? $preview['outbound_pickup'] : $preview['pickup'];
    $dropoff = $outbound ? $preview['outbound_dropoff'] : $preview['dropoff'];
    $vehicle = $vehicles->get($ticket['trip']['id']);
    $departure = \Illuminate\Support\Carbon::parse($ticket['trip']['departure_time']);
@endphp
<article class="ticket-success__ticket w-full min-w-0 shrink-0 snap-start overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-lg print:mb-6 print:break-after-page print:rounded-none print:border-0 print:shadow-none" id="ticket-{{ $ticket['code'] }}">
    <header class="relative flex items-center gap-4 overflow-hidden rounded-b-[2rem] bg-linear-to-r from-futa-orange to-red-600 px-6 py-6 text-white sm:px-8">
        <img src="{{ asset('images/booking-guide/futa-logo.png') }}" alt="{{ __('core::app.home.title') }}" class="h-16 w-24 shrink-0 rounded-lg bg-white p-2 object-contain">
        <div class="relative z-10"><p class="text-lg font-extrabold uppercase sm:text-2xl">{{ __('StaffTickets::sale.ticket_brand') }}</p><p class="text-lg font-bold">{{ $vehicle?->name }}</p><p class="mt-1 text-xs">{{ __('StaffTickets::sale.ticket_slogan') }}</p></div>
        <img src="{{ asset('images/auth/transfer-bus.png') }}" alt="" aria-hidden="true" class="absolute right-4 bottom-0 hidden h-28 w-48 object-contain opacity-25 sm:block">
    </header>
    <div class="space-y-5 p-5 text-sm text-slate-900 sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
            <h3
                class="flex items-center gap-2 text-lg font-extrabold uppercase text-slate-900 sm:text-xl">
            <x-heroicon-o-ticket
                class="size-7 text-futa-orange" />{{ __('StaffTickets::sale.electronic_ticket') }}</h3>
            <p
                class="mt-2 text-sm text-slate-500">{{ __('StaffTickets::sale.ticket_thanks') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-2 font-semibold text-green-700"><x-heroicon-o-check-circle class="size-5" />{{ __('StaffTickets::sale.ticket_paid') }}</span>
        </div>
        <div class="grid items-center gap-4 rounded-2xl border border-gray-200 bg-gray-50/60 p-4 sm:grid-cols-[minmax(0,1fr)_9rem] sm:p-5">
            <div
                class="min-w-0">
            <p
                class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('StaffTickets::tickets.code') }}</p>
            <p
                class="mt-2 break-all text-lg font-extrabold leading-snug text-slate-900 sm:text-xl">{{ $ticket['code'] }}</p>
            <p
                class="mt-3 break-all text-xs text-slate-500">{{ __('core::ticket-lookup.booking_code') }}: <span
                class="font-medium">{{ $bookingCodes->get($ticket['code']) }}</span>
            </p>
            </div>
            <img class="mx-auto aspect-square w-36 rounded-lg bg-white object-contain" src="{{ $ticket['qr'] }}" alt="{{ __('Payment::payment.success_qr_alt', ['code' => $ticket['code']]) }}">
        </div>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-map-pin class="size-5 text-futa-orange" />{{ __('StaffTickets::sale.journey') }}</h4>
            <div
                class="grid items-center gap-4 sm:grid-cols-[1fr_auto_1fr]">
            <div>
            <p
                class="text-lg font-extrabold text-slate-900">{{ $ticket['trip']['origin'] }}</p>
            <p
                class="mt-1 text-slate-500">{{ $pickup['name'] }}</p>
            </div>
            <x-heroicon-o-arrow-right
                class="size-7 text-futa-orange" />
            <div
                class="sm:text-right">
            <p
                class="text-lg font-extrabold text-slate-900">{{ $ticket['trip']['destination'] }}</p>
            <p
                class="mt-1 text-slate-500">{{ $dropoff['name'] }}</p>
            </div>
            </div>
        </section>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-truck class="size-5 text-futa-orange" />{{ __('StaffTickets::sale.vehicle_info') }}</h4>
            <dl
                class="grid gap-4 sm:grid-cols-3">
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('core::ticket-lookup.license_plate') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $vehicle?->license_plate ?? '—' }}</dd>
            </div>
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('core::ticket-lookup.operator') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $vehicle?->name ?? '—' }}</dd>
            </div>
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('core::ticket-lookup.vehicle') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $vehicle ? __('core::ticket-lookup.bus_type.'.$vehicle->bus_type) : '—' }}@if($vehicle) · {{ __('StaffTickets::sale.vehicle_capacity', ['count' => $vehicle->capacity]) }}@endif</dd>
            </div>
            </dl>
        </section>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-calendar-days class="size-5 text-futa-orange" />{{ __('core::booking.trip_info') }}</h4>
            <dl
                class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('StaffTickets::tickets.date') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $departure->format('d/m/Y') }}</dd>
            </div>
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('StaffTickets::tickets.time') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $departure->format('H:i') }}</dd>
            </div>
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('StaffTickets::tickets.seat') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ $ticket['seat'] }}</dd>
            </div>
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('core::ticket-lookup.seat_count') }}</dt>
            <dd
                class="mt-1 font-semibold">{{ __('StaffTickets::sale.one_ticket') }}</dd>
            </div>
            </dl>
        </section>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-map-pin class="size-5 text-futa-orange" />{{ __('StaffTickets::sale.pickup_dropoff_info') }}</h4>
            <dl
                class="grid gap-5 sm:grid-cols-2">@foreach(['pickup' => $pickup, 'dropoff' => $dropoff] as $key => $stop)<div>
            <dt
                class="mb-2 text-xs text-slate-500">{{ __('core::booking.'.$key) }}</dt>
            <dd
                class="font-semibold">{{ $stop['name'] }}<span
                class="mt-2 block text-xs font-normal leading-5 text-slate-500">{{ $stop['address'] ?? '' }}</span>
            </dd>
            </div>@endforeach</dl>
        </section>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-user class="size-5 text-futa-orange" />{{ __('Payment::payment.payment_customer_title') }}</h4>
            <dl
                class="grid gap-4 sm:grid-cols-3">@foreach(['name' => 'full_name', 'phone' => 'phone', 'email' => 'email'] as $field => $label)<div
                class="min-w-0">
            <dt
                class="text-xs text-slate-500">{{ __('core::booking.'.$label) }}</dt>
            <dd
                class="mt-1 break-words font-semibold">{{ $preview['customer'][$field] }}</dd>
            </div>@endforeach</dl>
        </section>
        <section class="border-t border-gray-100 pt-4">
            <h4 class="mb-4 flex items-center gap-2 font-bold uppercase text-slate-800"><x-heroicon-o-banknotes class="size-5 text-futa-orange" />{{ __('StaffTickets::sale.payment_info') }}</h4>
            <dl
                class="grid gap-4 rounded-2xl bg-orange-50/70 p-4 sm:grid-cols-2">
            <div>
            <dt
                class="text-xs text-slate-500">{{ __('StaffTickets::sale.payment_method') }}</dt>
            <dd
                class="mt-2 font-semibold">{{ $paymentMethodLabel }}</dd>
            </div>
            <div
                class="sm:text-right">
            <dt
                class="text-xs font-semibold uppercase text-futa-orange">{{ __('core::ticket-lookup.fare') }}</dt>
            <dd
                class="mt-1 text-2xl font-extrabold text-futa-orange">{{ number_format($ticket['price'], 0, ',', '.') }}đ</dd>
            </div>
            </dl>
        </section>

    </div>
</article>

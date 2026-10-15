@php
    $routeDeparture = \Illuminate\Support\Carbon::parse($routeTrip['departure_time']);
@endphp
<section class="mx-auto mt-5 w-[calc(100%-32px)] max-w-282 rounded-xl border border-orange-100 bg-white px-5 py-4 shadow-sm sm:px-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('core::booking.route') }}</p>
            <h2 class="flex flex-wrap items-center gap-3 text-xl font-bold text-futa-green sm:text-2xl">
                <span>{{ $routeTrip['origin'] }}</span>
                <x-heroicon-o-arrow-right class="size-5 shrink-0 text-futa-orange" />
                <span>{{ $routeTrip['destination'] }}</span>
            </h2>
        </div>
        <div class="inline-flex items-center gap-2 rounded-lg bg-futa-orange-soft px-3 py-2 text-sm font-semibold text-futa-orange">
            <x-heroicon-o-calendar-days class="size-5 shrink-0" />
            <span>{{ \Illuminate\Support\Str::title($routeDeparture->locale(app()->getLocale())->translatedFormat('l, d/m/Y')) }}@if($secondDeparture ?? null) · {{ \Illuminate\Support\Str::title($secondDeparture->locale(app()->getLocale())->translatedFormat('l, d/m/Y')) }}@endif</span>
        </div>
    </div>
    <div class="mt-4 border-t border-gray-100 pt-3">
        @if($paymentHeading ?? false)
            <form
                x-ref="paymentBackForm"
                method="POST"
                action="{{ route('staff.ticket-sales.destroy', ['sale' => $preview['token']]) }}" data-confirm
                data-confirm-title="{{ __('StaffTicketProcessing::sale.back_confirm_title') }}"
                data-confirm-message="{{ __('StaffTicketProcessing::sale.back_confirm_message') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline"><x-heroicon-o-arrow-left class="size-4" />{{ __('StaffTicketProcessing::sale.back') }}</button>
            </form>
        @else
            <a href="{{ route('staff.tickets.sale', $criteria) }}" @click="if (!$el.dispatchEvent(new CustomEvent('staff-booking-back', { bubbles: true, cancelable: true }))) $event.preventDefault()" class="inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline"><x-heroicon-o-arrow-left class="size-4" />{{ __('core::booking.back') }}</a>
        @endif
    </div>
</section>

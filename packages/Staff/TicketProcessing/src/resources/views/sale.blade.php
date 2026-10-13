@extends('core::layouts.home')
@section('title', __('StaffDashboard::staff.tickets.actions.sale'))
@push('styles')
    @vite(['packages/Staff/TicketProcessing/src/resources/js/app.js', 'packages/Customer/TripSearch/src/resources/css/app.css', 'packages/Customer/BookingManagement/src/resources/css/app.css', 'packages/FuteBus/Payment/src/resources/css/app.css'])
@endpush
@section('content')
<div data-staff-sale
    x-data="counterSale(@js(['context' => route('api.v1.staff.tickets.context'),
        'trips' => route('api.v1.staff.tickets.trips'),
        'create' => route('api.v1.staff.ticket-sales.store'),
        'status' => route('api.v1.staff.ticket-sales.show',
        ['sale' => '__SALE__']),
        'booking' => route('api.v1.staff.tickets.booking',
        ['trip' => '__TRIP__']),
        'preview' => route('api.v1.staff.ticket-previews.store'),
        'quote' => route('api.v1.staff.ticket-quotes.store'),
        'page' => route('staff.tickets.sale'),
        'csrf' => csrf_token()]),
        @js(['denied' => __('StaffTickets::sale.denied'),
        'error' => __('StaffTickets::sale.error'),
        'seat_count' => __('StaffTickets::sale.seat_count'),
        'unavailable' => __('StaffTickets::sale.unavailable'),
        'vehicles' => ['seat' => __('core::trip-search.seat'),
        'sleeper' => __('core::trip-search.sleeper'),
        'limousine' => __('core::trip-search.limousine')]]),
        @js($initialSale ?? null))"
    @staff-payment-back.window="backFromPayment()"
    @staff-payment-refresh.window="refresh()"
    @staff-trip-selected.window="selectCustomerTrip($event.detail)"
    class="flex min-h-dvh flex-col bg-gray-50">
    <div class="print:hidden">
        @include('StaffDashboard::partials.header', ['operatorProfileRoute' => Auth::user()->isBusCompany() ? 'profile.show' : 'staff.profile', 'operatorPasswordRoute' => Auth::user()->isBusCompany() ? 'profile.password.edit' : 'staff.password'])
        @include('StaffDashboard::partials.notifications')
    </div>
    <div class="grid flex-1 gap-4 p-3 text-sm print:block lg:grid-cols-[15%_minmax(0,1fr)]">
        <div class="print:hidden">@include('StaffDashboard::partials.sidebar', ['sidebarHome' => Auth::user()->isBusCompany() ? 'dashboard' : 'staff.dashboard'])</div>
        <main class="min-w-0 space-y-5">
            <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 bg-white px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange"><x-heroicon-o-ticket class="size-7" /></span>
                    <div><h1 class="text-2xl font-bold text-futa-green">{{ __('StaffDashboard::staff.tickets.actions.sale') }}</h1><p class="mt-1 text-sm text-slate-500">{{ __('StaffTickets::sale.subtitle') }}</p></div>
                </div>
                <a x-show="!trip && !sale" href="{{ route('staff.tickets') }}" class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-futa-orange hover:underline"><x-heroicon-o-arrow-left class="size-4" />{{ __('StaffTickets::sale.back_processing') }}</a>
            </div>
            <p x-show="ready && !access" x-cloak class="rounded-xl bg-orange-50 p-4 text-futa-orange">{{ __('StaffTickets::sale.denied') }}</p>
            <div x-show="access && !sale" x-cloak class="space-y-5">
                <div x-ref="customerSearch" x-show="!trip">
                    @include('StaffTickets::partials.search-form', ['bookingLocations' => [], 'searchCriteria' => request()->only(['departure', 'destination', 'departure_date', 'return_date', 'trip_type', 'quantity'])])
                </div>
                <div x-show="!trip && searched" class="mx-auto w-full max-w-282 px-3 sm:px-4" x-html="resultsHtml"></div>
                <div x-show="trip" x-cloak x-html="bookingHtml" :class="busy || uncertain ? 'pointer-events-none opacity-60' : ''"></div>
                <button x-show="uncertain" type="button" @click="refresh()" :disabled="busy" class="rounded-full border border-futa-orange px-6 py-3 text-futa-orange">{{ __('StaffTickets::sale.refresh') }}</button>
            </div>
            @if(isset($initialSale))
                <div x-ref="paymentView" x-show="sale" x-cloak>{!! $initialSale['html'] !!}</div>
            @else
                <div x-ref="paymentView" x-show="sale" x-cloak x-html="paymentHtml"></div>
            @endif

        </main>
    </div>
</div>
@endsection

@extends('core::layouts.home')
@section('title', __('StaffDashboard::staff.tickets.actions.lookup'))
@push('styles')
    @vite(['packages/FuteBus/StaffTicketProcessing/src/resources/css/app.css', 'packages/FuteBus/StaffTicketProcessing/src/resources/js/app.js'])
@endpush
@section('content')
<div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" class="flex min-h-dvh flex-col bg-gray-50">
    @include('StaffDashboard::partials.header')
    @include('StaffDashboard::partials.notifications')
    <div class="grid flex-1 gap-3 p-2 text-sm lg:grid-cols-[15%_minmax(0,1fr)]">
        @include('StaffDashboard::partials.sidebar')
        <main x-data="staffTicketList(@js(route('api.v1.staff.tickets.index')), @js(['denied' => __('StaffTicketProcessing::tickets.denied'), 'error' => __('StaffTicketProcessing::tickets.error')]))" class="min-w-0 space-y-3">
            <section class="relative flex min-h-36 items-center overflow-hidden rounded-xl bg-orange-50 p-5 sm:p-6">
                <img src="{{ asset('images/banners/home-banner.jpg') }}" alt="" class="absolute inset-y-0 right-0 h-full w-1/2 object-cover object-right">
                <div class="relative flex items-center gap-4 rounded-xl bg-orange-50/95 p-3">
                    <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-futa-orange text-white"><x-heroicon-o-magnifying-glass class="size-8" /></span>
                    <div><h1 class="text-3xl font-bold text-futa-orange">{{ __('StaffDashboard::staff.tickets.actions.lookup') }}</h1><p class="mt-2 text-gray-500">{{ __('StaffTicketProcessing::tickets.lookup_intro') }}</p></div>
                </div>
            </section>
            @include('StaffTicketProcessing::partials.ticket-lookup')
        </main>
    </div>
</div>
@endsection

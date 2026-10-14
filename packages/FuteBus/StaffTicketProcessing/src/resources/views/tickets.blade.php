@extends('core::layouts.home')
@section('title', __('StaffDashboard::staff.ticket_menu'))
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
                    <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-futa-orange text-white"><x-heroicon-o-ticket class="size-8" /></span>
                    <div><h1 class="text-3xl font-bold text-futa-orange">{{ __('StaffDashboard::staff.ticket_menu') }}</h1><p class="mt-2 text-gray-500">{{ __('StaffTicketProcessing::tickets.intro') }}</p></div>
                </div>
            </section>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach(['sale' => ['ticket', 'bg-rose-50 text-red-600'], 'exchange' => ['arrows-right-left', 'bg-blue-50 text-blue-600'], 'cancellation' => ['x-circle', 'bg-green-50 text-green-600']] as $action => [$icon, $colors])
                <button
                    type="button" @if($action === 'sale')
                    @click="groups?.tickets.sale ? window.location.assign(@js(route('staff.tickets.sale'))) : window.FutaNotify.warning(@js(__('StaffTicketProcessing::sale.denied')), { duration: 4000 })" @else
                    @click="window.FutaNotify.info(@js(__('StaffDashboard::staff.unavailable')), { duration: 4000 })" @endif
                    class="flex items-center gap-4 rounded-xl border-4 border-white p-4 text-left shadow-sm {{ $colors }}">
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-8 shrink-0" />
                    <span class="min-w-0 flex-1"><strong class="block text-base text-gray-900">{{ __('StaffDashboard::staff.tickets.actions.'.$action) }}</strong><span class="mt-1 block text-xs text-gray-500">{{ __('StaffTicketProcessing::tickets.'.$action.'_hint') }}</span></span>
                    <x-heroicon-o-arrow-right-circle class="size-5 shrink-0" />
                </button>
                @endforeach
            </div>
            @include('StaffTicketProcessing::partials.ticket-lookup')
        </main>
    </div>
</div>
@endsection

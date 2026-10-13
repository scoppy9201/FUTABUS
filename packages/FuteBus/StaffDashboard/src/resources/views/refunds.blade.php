@extends('core::layouts.home')
@section('title', __('Payment::refunds.title'))
@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush
@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" class="flex min-h-dvh flex-col bg-gray-50">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div class="grid flex-1 gap-3 p-2 text-sm lg:grid-cols-[15%_minmax(0,1fr)]">
            @include('StaffDashboard::partials.sidebar')
            <main class="min-w-0 p-2 sm:p-6">
                @include('Payment::refunds.list')
            </main>
        </div>
    </div>
@endsection

@extends('StaffDashboard::layouts.staff')
@section('title', __('Payment::refunds.title'))
@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush
@section('content')
    <div x-data="staffDashboard(@js(route('api.v1.staff.dashboard.show')))" class="flex h-dvh min-h-0 flex-col overflow-hidden bg-gray-50 print:h-auto print:overflow-visible">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0 [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
            @include('StaffDashboard::partials.sidebar')
            <main class="min-w-0 p-2 sm:p-6">
                @include('Payment::refunds.list')
            </main>
        </div>
    </div>
@endsection

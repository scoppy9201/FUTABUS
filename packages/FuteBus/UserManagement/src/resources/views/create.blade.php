@extends('Dashboard::layouts.admin')
@section('title', __('UserManagement::app.create_account'))
@section('content')
    <header class="mb-6">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company->name }}</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ __('UserManagement::app.title') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('UserManagement::app.description') }}</p>
    </header>
    <div class="flex flex-wrap gap-3">
        @if(auth()->user()->hasPermissionTo('user.view'))
            <a href="{{ route('staff-accounts.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600">{{ __('UserManagement::app.back') }}</a>
        @endif
        <a data-staff-create data-no-loader href="{{ route('staff-accounts.create') }}" class="rounded-xl bg-futa-orange px-4 py-3 text-sm font-bold text-white">{{ __('UserManagement::app.create') }}</a>
    </div>
    @include('UserManagement::partials.create-modal')
    @vite('packages/FuteBus/UserManagement/src/resources/js/app.js')
@endsection

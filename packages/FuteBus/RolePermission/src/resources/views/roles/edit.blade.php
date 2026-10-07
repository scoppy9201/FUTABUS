@extends('Dashboard::layouts.admin')

@section('title', __('RolePermission::app.edit_role'))
@section('page_title', __('RolePermission::app.edit_role'))

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-orange-100 bg-gradient-to-r from-white via-orange-50 to-orange-100 px-5 py-4 shadow-sm sm:px-6">
        <div class="flex items-center gap-3">
            <span class="grid size-11 place-items-center rounded-xl bg-white text-[#F26522] shadow-sm"><x-heroicon-o-shield-check class="size-6" /></span>
            <div><p class="text-xs font-bold uppercase tracking-wider text-[#ef5222]">{{ $company->name }}</p><h1 class="mt-0.5 text-xl font-extrabold text-slate-900 sm:text-2xl">{{ __('RolePermission::app.edit_role') }}</h1></div>
        </div>
        <a href="{{ route('access-management.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"><x-heroicon-o-arrow-left class="size-4" />{{ __('RolePermission::app.back') }}</a>
    </div>
    @php($activeAccessTab = 'roles')
    @include('RolePermission::partials.navigation')
    <form method="post" action="{{ route('access-management.roles.update', $role->id) }}" class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        @csrf
        @method('put')
        @include('RolePermission::roles.form', ['role' => $role, 'selectedPermissionIds' => $selectedPermissionIds])
    </form>
@endsection

@extends('Dashboard::layouts.admin')
@section('title', __('RolePermission::app.edit_role'))
@section('content')
    @php($activeAccessTab = 'roles')
    @include('RolePermission::partials.header', ['hideCreate' => true])
    @include('RolePermission::partials.navigation')
    <div class="mt-5 flex flex-wrap gap-3">
        @if(auth()->user()->hasPermissionTo('role.view'))
            <a href="{{ route('access-management.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600">{{ __('RolePermission::app.back') }}</a>
        @endif
        <button type="button" data-access-target="role-wizard" class="rounded-xl bg-futa-orange px-4 py-3 text-sm font-bold text-white">{{ __('RolePermission::app.edit_role') }}</button>
    </div>
    @include('RolePermission::roles.modal', ['role' => $role, 'selectedPermissionIds' => $selectedPermissionIds])
    @vite('packages/FuteBus/RolePermission/src/resources/js/app.js')
@endsection

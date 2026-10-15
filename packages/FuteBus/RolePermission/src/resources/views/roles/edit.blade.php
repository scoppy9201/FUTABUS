@extends('Dashboard::layouts.admin')
@section('title', __('RolePermission::app.edit_role'))
@section('content')
    @php($activeAccessTab = 'roles')
    @include('RolePermission::partials.header', ['hideCreate' => true])
    @include('RolePermission::partials.navigation')
    @include('RolePermission::roles.modal', ['role' => $role, 'selectedPermissionIds' => $selectedPermissionIds])
    @vite('packages/FuteBus/RolePermission/src/resources/js/app.js')
@endsection

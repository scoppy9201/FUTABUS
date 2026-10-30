@extends('Dashboard::layouts.admin')

@section('title', __('RolePermission::app.function_catalog'))
@section('page_title', __('RolePermission::app.function_catalog'))

@section('content')
    @php
        $permissionTotal = $permissionGroups->flatten(1)->count();
        $activeTotal = $permissionGroups->flatten(1)->where('is_active', true)->count();
    @endphp
    @php($activeAccessTab = 'catalog')
    @include('RolePermission::partials.header')
    @include('RolePermission::partials.navigation')


    <section class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="max-w-3xl">
            <h2 class="text-sm font-bold text-slate-800">{{ __('RolePermission::app.catalog_source_heading') }}</h2>
            <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('RolePermission::app.catalog_source_hint') }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-600">{{ __('RolePermission::app.catalog_pending', ['count' => $pendingTotal]) }}</p>
        </div>
        @if($canManageCatalog)
            <form method="POST" action="{{ route('access-management.catalog.sync') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-futa-orange px-4 py-2.5 text-sm font-bold text-white hover:bg-futa-orange-dark">
                    <x-heroicon-o-arrow-path class="size-4" />{{ __('RolePermission::app.catalog_sync') }}
                </button>
            </form>
        @endif
    </section>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm">
            <span class="grid size-10 place-items-center rounded-lg bg-orange-50 text-futa-orange"><x-heroicon-o-squares-2x2 class="size-5" /></span>
            <div><p class="text-sm font-medium text-slate-500">{{ __('RolePermission::app.function_groups') }}</p><p class="text-xl font-extrabold text-slate-900">{{ $permissionGroups->count() }}</p></div>
        </article>
        <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm">
            <span class="grid size-10 place-items-center rounded-lg bg-sky-50 text-sky-600"><x-heroicon-o-shield-check class="size-5" /></span>
            <div><p
                class="text-sm font-medium text-slate-500">{{ __('RolePermission::app.active_permissions_count') }}</p><p
                class="text-xl font-extrabold text-slate-900">{{ $activeTotal }} <span
                class="text-sm font-semibold text-slate-400">/ {{ $permissionTotal }}</span></p></div>
        </article>
    </div>

    <div data-access-catalog class="mt-4" x-data="{ query: '' }">
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('RolePermission::app.search_catalog') }}
            <span class="relative mt-1.5 block">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" x-model.debounce.150ms="query" placeholder="{{ __('RolePermission::app.search_catalog_hint') }}"
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm font-normal text-slate-800 outline-none transition focus:border-futa-orange focus:ring-2 focus:ring-orange-100">
            </span>
        </label>
        @if($permissionGroups->isEmpty())<x-core::empty-state :title="__('RolePermission::app.no_search_results')" />@endif
        <div class="mt-3 grid items-start gap-3 xl:grid-cols-2">
            @foreach($permissionGroups as $group => $permissions)
                @php($groupLabel = $permissions->first()->group_label)
                <section
                    x-show="[...$el.querySelectorAll('[data-permission-item]')].some(item =>
                        !query || item.dataset.search.includes(query.toLowerCase()))"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white text-slate-500 shadow-sm"><x-heroicon-o-squares-2x2 class="size-4" /></span>
                            <h2 class="truncate text-sm font-bold text-slate-800">{{ $groupLabel }}</h2>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-500">{{ $permissions->count() }}</span>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @foreach($permissions as $permission)
                            @php($label = $permission->label)
                            @php($isEditing = (int) old('_catalog_permission') === (int) $permission->id)
                            <li data-permission-item
                                data-search="{{ mb_strtolower($label.' '.$permission->display_name.' '.$permission->name.' '.$permission->slug.' '.$groupLabel) }}"
                                x-show="!query || $el.dataset.search.includes(query.toLowerCase())"
                                class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 {{ $permission->is_active ? '' : 'bg-slate-50/80' }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium {{ $permission->is_active ? 'text-slate-700' : 'text-slate-400' }}">{{ $label }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $permission->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                            {{ __('RolePermission::app.'.($permission->id === null ? 'permission_pending' : ($permission->is_active ? 'permission_active' : 'permission_inactive'))) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    
                                    @if($canManageCatalog && $permission->id !== null)
                                        <button type="button" data-access-target="permission-{{ $permission->id }}"
                                            aria-label="{{ __('RolePermission::app.edit') }}"
                                            class="rounded-lg border border-slate-200 p-2 text-slate-500">
                                            <x-heroicon-o-pencil-square
                                            class="size-4" /></button>
                                        <dialog id="permission-{{ $permission->id }}" data-access-modal
                                            data-auto-open="{{ $errors->any() && (int) old('_catalog_permission') === (int) $permission->id ? '1' : '0' }}"
                                            aria-labelledby="permission-title-{{ $permission->id }}"
                                            class="m-auto max-h-[calc(100dvh-2rem)] w-[min(30rem,calc(100vw-2rem))] overflow-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/50">
                                            <header class="flex items-center justify-between gap-4 border-b border-slate-100 p-5">
                                                <h3 id="permission-title-{{ $permission->id }}" class="text-lg font-bold text-futa-green">{{ __('RolePermission::app.rename_permission') }}</h3>
                                                <button type="button" data-access-close aria-label="{{ __('RolePermission::app.cancel') }}" class="rounded-full bg-slate-100 p-2"><x-heroicon-o-x-mark class="size-4" /></button>
                                            </header>
                                            <form method="POST" novalidate action="{{ route('access-management.catalog.update', $permission->id) }}" class="grid gap-4 p-5">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="_catalog_permission" value="{{ $permission->id }}">
                                                <p class="text-sm leading-6 text-slate-500">{{ __('RolePermission::app.rename_permission_hint') }}</p>
                                                <div class="space-y-2">
                                                    <label for="permission-name-{{ $permission->id }}" class="block text-sm font-semibold text-slate-700">{{ __('RolePermission::app.permission_name') }} <span class="text-futa-orange">*</span></label>
                                                    <input id="permission-name-{{ $permission->id }}" name="display_name"
                                                        value="{{ $isEditing ? old('display_name') : $label }}" required maxlength="120"
                                                        aria-invalid="{{ $isEditing && $errors->has('display_name') ? 'true' : 'false' }}"
                                                        aria-describedby="permission-name-error-{{ $permission->id }}"
                                                        class="min-h-11 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-futa-orange focus:ring-orange-100">
                                                    <p id="permission-name-error-{{ $permission->id }}" role="alert" class="text-sm text-red-600">{{ $isEditing ? $errors->first('display_name') : '' }}</p>
                                                </div>
                                                <div class="mt-2 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                                                    <button type="button" data-access-close class="min-h-11 rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600">{{ __('RolePermission::app.cancel') }}</button>
                                                    <button type="submit" class="min-h-11 rounded-lg bg-futa-orange px-5 py-2.5 text-sm font-bold text-white">{{ __('RolePermission::app.save_changes') }}</button>
                                                </div>
                                            </form>
                                        </dialog>
                                        <form method="POST" action="{{ route('access-management.catalog.status', $permission->id) }}" data-confirm
                                            data-confirm-title="{{ __('RolePermission::app.'.($permission->is_active ? 'disable_permission_title' : 'enable_permission_title'), ['name' => $label]) }}"
                                            data-confirm-message="{{ __('RolePermission::app.toggle_permission_confirmation') }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $permission->is_active ? 0 : 1 }}">
                                            <button
                                                class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold {{ $permission->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                                    {{ __('RolePermission::app.'.($permission->is_active ? 'deactivate' : 'activate')) }}
                                                </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
        <x-core::empty-state x-cloak x-show="query && ![...$el.parentElement.querySelectorAll('[data-permission-item]')].some(item => item.dataset.search.includes(query.toLowerCase()))"
            :title="__('RolePermission::app.no_search_results')" compact />
    </div>
    @vite('packages/FuteBus/RolePermission/src/resources/js/app.js')
@endsection

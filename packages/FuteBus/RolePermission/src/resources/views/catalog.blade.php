@extends('Dashboard::layouts.admin')

@section('title', __('RolePermission::app.function_catalog'))
@section('page_title', __('RolePermission::app.function_catalog'))

@section('content')
    @php
        $permissionLabels = __('RolePermission::app.permissions');
        $permissionTotal = $permissionGroups->flatten(1)->count();
        $activeTotal = $permissionGroups->flatten(1)->where('is_active', true)->count();
    @endphp
    <section class="flex items-center gap-3 rounded-xl border border-orange-100 bg-gradient-to-r from-white via-orange-50 to-orange-100 px-5 py-4 shadow-sm sm:px-6">
        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white text-[#F26522] shadow-sm"><x-heroicon-o-squares-2x2 class="size-6" /></span>
        <div class="min-w-0">
            <p class="truncate text-xs font-bold uppercase tracking-wider text-[#ef5222]">{{ $company->name }}</p>
            <h1 class="mt-0.5 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ __('RolePermission::app.function_catalog') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('RolePermission::app.catalog_description') }}</p>
        </div>
    </section>

    @php($activeAccessTab = 'catalog')
    @include('RolePermission::partials.navigation')

    @if(session('status'))
        <div role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    @if($canManageCatalog && $availablePermissionSlugs->isNotEmpty())
        <details class="mt-4 rounded-xl border border-slate-200 bg-white shadow-sm">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5">
                <span class="text-sm font-bold text-slate-700">{{ __('RolePermission::app.register_permission') }}</span>
                <x-heroicon-o-plus class="size-5 text-[#F26522]" />
            </summary>
            <form method="POST" action="{{ route('access-management.catalog.store') }}" class="grid gap-3 border-t border-slate-100 p-4 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
                @csrf
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('RolePermission::app.permission_key') }}
                    <select name="slug" required class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#F26522] focus:ring-orange-100">
                        <option value="">{{ __('RolePermission::app.choose_permission_key') }}</option>
                        @foreach($availablePermissionSlugs as $slug)<option value="{{ $slug }}" @selected(old('slug') === $slug)>{{ $slug }}</option>@endforeach
                    </select>
                    @error('slug')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('RolePermission::app.permission_name') }}
                    <input name="display_name" value="{{ old('display_name') }}" required maxlength="120" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-normal text-slate-700 focus:border-[#F26522] focus:ring-orange-100">
                    @error('display_name')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('RolePermission::app.display_group') }}
                    <input name="display_group" value="{{ old('display_group') }}" maxlength="80" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-normal text-slate-700 focus:border-[#F26522] focus:ring-orange-100">
                    @error('display_group')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                </label>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#d95318]"><x-heroicon-o-plus class="size-4" />{{ __('RolePermission::app.register_permission') }}</button>
            </form>
        </details>
    @endif

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
            <span class="grid size-10 place-items-center rounded-lg bg-orange-50 text-[#F26522]"><x-heroicon-o-squares-2x2 class="size-5" /></span>
            <div><p class="text-sm font-medium text-slate-500">{{ __('RolePermission::app.function_groups') }}</p><p class="text-xl font-extrabold text-slate-900">{{ $permissionGroups->count() }}</p></div>
        </article>
        <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
            <span class="grid size-10 place-items-center rounded-lg bg-sky-50 text-sky-600"><x-heroicon-o-shield-check class="size-5" /></span>
            <div><p class="text-sm font-medium text-slate-500">{{ __('RolePermission::app.active_permissions_count') }}</p><p class="text-xl font-extrabold text-slate-900">{{ $activeTotal }} <span class="text-sm font-semibold text-slate-400">/ {{ $permissionTotal }}</span></p></div>
        </article>
    </div>

    <div class="mt-4" x-data="{ query: '' }">
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('RolePermission::app.search_catalog') }}
            <span class="relative mt-1.5 block">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" x-model.debounce.150ms="query" placeholder="{{ __('RolePermission::app.search_catalog_hint') }}" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm font-normal text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
            </span>
        </label>
        <div class="mt-3 grid items-start gap-3 xl:grid-cols-2">
            @foreach($permissionGroups as $group => $permissions)
                <section x-show="!query || $el.innerText.toLowerCase().includes(query.toLowerCase())" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white text-slate-500 shadow-sm"><x-heroicon-o-squares-2x2 class="size-4" /></span>
                            <h2 class="truncate text-sm font-bold text-slate-800">{{ $permissions->first()->display_group ?: (__('RolePermission::app.groups.'.$group) === 'RolePermission::app.groups.'.$group ? $group : __('RolePermission::app.groups.'.$group)) }}</h2>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-500">{{ $permissions->count() }}</span>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @foreach($permissions as $permission)
                            @php($label = $permission->display_name ?: ($permissionLabels[$permission->slug] ?? $permission->name))
                            <li data-search="{{ mb_strtolower($label.' '.$permission->slug) }}" x-show="!query || $el.dataset.search.includes(query.toLowerCase())" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 {{ $permission->is_active ? '' : 'bg-slate-50/80' }}">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="text-sm font-medium {{ $permission->is_active ? 'text-slate-700' : 'text-slate-400' }}">{{ $label }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $permission->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">{{ __('RolePermission::app.'.($permission->is_active ? 'permission_active' : 'permission_inactive')) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <code class="rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-500">{{ $permission->slug }}</code>
                                    @if($canManageCatalog)
                                        <details class="relative">
                                            <summary class="grid size-8 cursor-pointer list-none place-items-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="{{ __('RolePermission::app.edit') }}"><x-heroicon-o-pencil-square class="size-4" /></summary>
                                            <form method="POST" action="{{ route('access-management.catalog.update', $permission->id) }}" class="absolute right-0 z-10 mt-1 grid w-64 gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                                                @csrf @method('PUT')
                                                <p class="text-[11px] font-semibold text-slate-500">{{ $permission->slug }}</p>
                                                <input name="display_name" value="{{ $permission->display_name ?: ($permissionLabels[$permission->slug] ?? $permission->name) }}" required maxlength="120" aria-label="{{ __('RolePermission::app.permission_name') }}" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs focus:border-[#F26522] focus:ring-orange-100">
                                                <input name="display_group" value="{{ $permission->display_group ?: (__('RolePermission::app.groups.'.$group) === 'RolePermission::app.groups.'.$group ? $group : __('RolePermission::app.groups.'.$group)) }}" maxlength="80" aria-label="{{ __('RolePermission::app.display_group') }}" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs focus:border-[#F26522] focus:ring-orange-100">
                                                <button class="rounded-lg bg-[#F26522] px-3 py-2 text-xs font-bold text-white">{{ __('RolePermission::app.save_changes') }}</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ route('access-management.catalog.status', $permission->id) }}" onsubmit="return confirm('{{ __('RolePermission::app.toggle_permission_confirmation') }}')">
                                            @csrf @method('PATCH')
                                            <button class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold {{ $permission->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">{{ __('RolePermission::app.'.($permission->is_active ? 'deactivate' : 'activate')) }}</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
@endsection

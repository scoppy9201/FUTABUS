<section data-role-step="1" class="grid gap-4 sm:grid-cols-2">
    <label class="text-sm font-semibold text-slate-700">
        {{ __('RolePermission::app.fields.name') }}
        <input type="text" name="name"
            value="{{ old('name', $role?->name) }}" required maxlength="255"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-futa-orange focus:ring-2 focus:ring-orange-100">
        @error('name')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-semibold text-slate-700">
        {{ __('RolePermission::app.fields.description') }}
        <textarea name="description" rows="3" maxlength="1000"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-futa-orange focus:ring-2 focus:ring-orange-100">{{ old('description', $role?->description) }}</textarea>
        @error('description')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
    </label>
</section>

<section data-role-step="2" hidden class="space-y-4">
    <input type="hidden" name="permission_ids_present" value="1">
    @php($checkedPermissions = old('permission_ids_present') ? old('permission_ids', []) : $selectedPermissionIds)
    @php($permissionLabels = __('RolePermission::app.permissions'))
    @php($groupLabels = __('RolePermission::app.groups'))
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-orange-100 bg-orange-50/60 p-4">
        <div><h2 class="text-base font-bold text-slate-900">{{ __('RolePermission::app.permissions_heading') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('RolePermission::app.permissions_hint') }}</p></div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-600"><x-heroicon-o-shield-check class="size-4 text-futa-orange" />{{ $permissionGroups->sum(fn ($permissions) => $permissions->count()) }}</span>
    </div>
    <label class="block text-sm font-semibold text-slate-600">{{ __('RolePermission::app.search_catalog') }}
        <input type="search" data-role-permission-search class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3" placeholder="{{ __('RolePermission::app.search_catalog_hint') }}">
    </label>
    <div class="grid items-start gap-3">
        @foreach($permissionGroups as $group => $permissions)
            @php($groupLabel = $groupLabels[$group] ?? ($permissions->first()->display_group ?: $group))
            <fieldset data-role-permission-group class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <legend class="sr-only">{{ $groupLabel }}</legend>
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <h3 class="text-sm font-bold text-slate-800">{{ $groupLabel }}</h3>
                    <button type="button" data-role-group-toggle class="text-xs font-semibold text-futa-orange">{{ __('RolePermission::app.wizard.select_group') }}</button>
                </div>
                <div class="grid gap-2 p-3 sm:grid-cols-2">
                    @foreach($permissions as $permission)
                        @php($permissionLabel = $permissionLabels[$permission->slug] ?? ($permission->display_name ?: $permission->name))
                        <label data-role-permission-item class="flex min-w-0 items-start gap-2.5 rounded-lg border border-slate-100 px-3 py-2.5 text-sm transition hover:border-orange-200 hover:bg-orange-50/40">
                            <input data-role-permission type="checkbox" name="permission_ids[]"
                                value="{{ $permission->id }}" @checked(in_array($permission->id, $checkedPermissions, true) || in_array((string) $permission->id, $checkedPermissions, true))
                                class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-futa-orange focus:ring-futa-orange">
                            <span class="min-w-0">
                                <span data-role-permission-label class="block wrap-break-word font-medium text-slate-700">
                                    {{ $permissionLabel }}
                                </span>
                                
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
    <x-core::empty-state data-role-no-results hidden :title="__('RolePermission::app.no_search_results')" compact />
    @error('permission_ids.*')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
    @error('permission_ids')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
</section>

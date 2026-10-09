<div class="grid gap-4 lg:grid-cols-2">
    <label class="text-sm font-semibold text-slate-700">
        {{ __('RolePermission::app.fields.name') }}
        <input type="text" name="name" value="{{ old('name', $role?->name) }}" required maxlength="255" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
        @error('name')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-semibold text-slate-700">
        {{ __('RolePermission::app.fields.description') }}
        <textarea name="description" rows="3" maxlength="1000" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">{{ old('description', $role?->description) }}</textarea>
        @error('description')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
    </label>
</div>

<div class="mt-6">
    <input type="hidden" name="permission_ids_present" value="1">
    @php($checkedPermissions = old('permission_ids_present') ? old('permission_ids', []) : $selectedPermissionIds)
    @php($permissionLabels = __('RolePermission::app.permissions'))
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-orange-100 bg-orange-50/60 p-4">
        <div><h2 class="text-base font-bold text-slate-900">{{ __('RolePermission::app.permissions_heading') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('RolePermission::app.permissions_hint') }}</p></div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-600"><x-heroicon-o-shield-check class="size-4 text-[#F26522]" />{{ $permissionGroups->sum(fn ($permissions) => $permissions->count()) }}</span>
    </div>
    <div class="mt-3 grid items-start gap-3 xl:grid-cols-2">
        @foreach($permissionGroups as $group => $permissions)
            <fieldset class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <legend class="sr-only">{{ __('RolePermission::app.groups.'.$group) }}</legend>
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <h3 class="text-sm font-bold text-slate-800">{{ __('RolePermission::app.groups.'.$group) }}</h3>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-500">{{ $permissions->count() }}</span>
                </div>
                <div class="grid gap-2 p-3 sm:grid-cols-2">
                    @foreach($permissions as $permission)
                        <label class="flex min-w-0 items-start gap-2.5 rounded-lg border border-slate-100 px-3 py-2.5 text-sm transition hover:border-orange-200 hover:bg-orange-50/40">
                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $checkedPermissions, true) || in_array((string) $permission->id, $checkedPermissions, true)) class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-[#F26522] focus:ring-[#F26522]">
                            <span class="min-w-0"><span class="block break-words font-medium text-slate-700">{{ $permission->display_name ?: ($permissionLabels[$permission->slug] ?? $permission->name) }}</span><code class="mt-1 block break-all text-[11px] text-slate-400">{{ $permission->slug }}</code></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
    @error('permission_ids')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mt-5 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4">
    <a href="{{ route('access-management.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">{{ __('RolePermission::app.cancel') }}</a>
    <button type="submit" class="rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#d95318]">{{ __($role ? 'RolePermission::app.save_changes' : 'RolePermission::app.create_role') }}</button>
</div>

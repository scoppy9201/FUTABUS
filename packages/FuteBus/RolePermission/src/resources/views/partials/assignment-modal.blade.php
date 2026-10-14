<dialog id="assign-{{ $staff->id }}" data-access-modal
    data-auto-open="{{ $errors->any() && (int) old('_assignment_staff') === (int) $staff->id ? '1' : '0' }}"
    aria-labelledby="assign-title-{{ $staff->id }}"
    class="m-auto max-h-[calc(100dvh-2rem)] w-[min(38rem,calc(100vw-2rem))] overflow-auto rounded-2xl border border-slate-200 bg-white p-0 text-left shadow-2xl backdrop:bg-slate-950/50">
    <form method="post" action="{{ route('access-management.users.assign', $staff->id) }}">
        @csrf @method('put')
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="_assignment_staff" value="{{ $staff->id }}">
        <header class="flex items-center justify-between gap-4 border-b border-slate-100 p-5">
            <div>
                <h2 id="assign-title-{{ $staff->id }}" class="text-lg font-bold text-slate-950">{{ __('RolePermission::app.roles_for', ['name' => $staff->name]) }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $staff->email }}</p>
            </div>
            <button type="button" data-access-close aria-label="{{ __('RolePermission::app.cancel') }}" class="rounded-full bg-slate-100 p-2"><x-heroicon-o-x-mark class="size-5" /></button>
        </header>
        <div class="space-y-4 p-5">
            @if($roles->isNotEmpty())<p class="text-sm leading-6 text-slate-500">{{ __('RolePermission::app.assignment_list_hint') }}</p>@endif
            @error('role_ids')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
            @error('role_ids.*')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
            <fieldset @class(['grid gap-3 sm:grid-cols-2' => $roles->isNotEmpty()])>
                <legend class="sr-only">{{ __('RolePermission::app.assign_roles') }}</legend>
                @forelse($roles as $assignmentRole)
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 has-checked:border-futa-orange/40 has-checked:bg-futa-orange-soft/50">
                        <input type="checkbox" name="role_ids[]" value="{{ $assignmentRole->id }}"
                            @checked(in_array((string) $assignmentRole->id, array_map('strval', (int) old('_assignment_staff') === (int) $staff->id ? old('role_ids', []) : $staff->role_ids), true))
                            class="mt-1 size-4 shrink-0 rounded border-slate-300 accent-futa-orange">
                        <span class="min-w-0">
                            <span class="block wrap-break-word text-sm font-semibold text-slate-800">{{ $assignmentRole->name }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $assignmentRole->permission_count }} {{ __('RolePermission::app.columns.permission_count') }}</span>
                        </span>
                    </label>
                @empty
                    <x-core::empty-state :title="__('RolePermission::app.empty_roles')">
                        @if(auth()->user()->hasPermissionTo('role.create'))
                            <a href="{{ route('access-management.roles.create') }}" class="inline-flex rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-bold text-white">{{ __('RolePermission::app.create_first_role') }}</a>
                        @endif
                    </x-core::empty-state>
                @endforelse
            </fieldset>
        </div>
        <footer class="flex justify-end gap-3 border-t border-slate-100 p-5">
            <button type="button" data-access-close class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600">{{ __('RolePermission::app.cancel') }}</button>
            <button type="submit" @disabled($roles->isEmpty()) @if($roles->isEmpty()) data-permanent-disabled @endif
                class="rounded-xl bg-futa-orange px-4 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{{ __('RolePermission::app.save_assignments') }}</button>
        </footer>
    </form>
</dialog>

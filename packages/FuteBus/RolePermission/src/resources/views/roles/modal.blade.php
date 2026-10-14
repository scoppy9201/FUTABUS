<dialog id="role-wizard" data-access-modal data-role-wizard-modal
    data-auto-open="{{ request()->routeIs('access-management.roles.create', 'access-management.roles.edit') || ($errors->any() && old('_role_wizard')) ? '1' : '0' }}"
    aria-labelledby="role-wizard-title"
    class="m-auto max-h-[calc(100dvh-2rem)] w-[min(50rem,calc(100vw-2rem))] overflow-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/50">
    <form data-role-wizard method="post" novalidate
        action="{{ $role ? route('access-management.roles.update', $role->id) : route('access-management.roles.store') }}"
        data-initial-step="{{ $errors->has('name') || $errors->has('description') ? 1 : ($errors->any() ? 2 : 1) }}">
        @csrf
        @if($role) @method('put') @endif
        <input type="hidden" name="_role_wizard" value="1">
        <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-4 sm:px-6">
            <div>
                <h2 id="role-wizard-title" class="text-xl font-extrabold text-slate-950">{{ __($role ? 'RolePermission::app.edit_role' : 'RolePermission::app.create_role') }}</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('RolePermission::app.permissions_hint') }}</p>
            </div>
            <button type="button" data-access-close aria-label="{{ __('RolePermission::app.cancel') }}" class="rounded-full bg-slate-100 p-2 text-slate-500"><x-heroicon-o-x-mark class="size-5" /></button>
        </header>
        <div class="p-5 sm:p-6">
            <ol aria-label="{{ __('RolePermission::app.wizard.progress') }}" class="mb-6 flex">
                @foreach(['information', 'permissions', 'review'] as $key)
                    <li data-role-marker="{{ $loop->iteration }}" class="relative flex-1 text-center text-xs font-semibold">
                        @unless($loop->last)<span data-role-connector aria-hidden="true" class="absolute left-1/2 top-4 w-full border-t-2 border-dashed"></span>@endunless
                        <span data-role-circle class="relative mx-auto mb-2 grid size-9 place-items-center rounded-full border-2 bg-white text-sm">{{ $loop->iteration }}</span>
                        {{ __('RolePermission::app.wizard.'.$key) }}
                    </li>
                @endforeach
            </ol>
            @if($errors->any())<p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ __('RolePermission::app.wizard.fix_errors') }}</p>@endif
            @include('RolePermission::roles.form')
            <section data-role-step="3" hidden class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="font-bold text-slate-950">{{ __('RolePermission::app.wizard.review_title') }}</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-slate-500">{{ __('RolePermission::app.fields.name') }}</dt><dd data-role-summary-name class="mt-1 wrap-break-word font-semibold"></dd></div>
                    <div><dt class="text-slate-500">{{ __('RolePermission::app.fields.description') }}</dt><dd data-role-summary-description class="mt-1 whitespace-pre-wrap wrap-break-word"></dd></div>
                </dl>
                <p class="mt-5 font-semibold text-slate-800">{{ __('RolePermission::app.wizard.selected') }}: <span data-role-count>0</span></p>
                <ul data-role-summary-permissions class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2"></ul>
                <p data-role-zero class="mt-3 text-sm leading-6 text-slate-500">{{ __('RolePermission::app.wizard.no_permissions') }}</p>
            </section>
        </div>
        <footer class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-slate-100 bg-white px-5 py-4 sm:px-6">
            <button type="button" data-role-back class="mr-auto rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-600">{{ __('RolePermission::app.wizard.previous') }}</button>
            <button type="button" data-role-next class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('RolePermission::app.wizard.next') }}</button>
            <button type="submit" data-role-submit hidden class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __($role ? 'RolePermission::app.save_changes' : 'RolePermission::app.create_role') }}</button>
        </footer>
    </form>
</dialog>

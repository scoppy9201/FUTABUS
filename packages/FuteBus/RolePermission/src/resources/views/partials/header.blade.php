<header class="mb-5 flex flex-wrap items-center justify-between gap-4">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company->name }}</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('RolePermission::app.title') }}</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ __('RolePermission::app.description') }}</p>
    </div>
    @if(!($hideCreate ?? false) && ($activeAccessTab ?? 'roles') === 'roles' && auth()->user()->hasPermissionTo('role.create'))
        <a data-role-create data-no-loader href="{{ route('access-management.roles.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-futa-orange-dark">
            <x-heroicon-o-plus class="size-4" />{{ __('RolePermission::app.create_role') }}
        </a>
    @endif
</header>
@if(session('status'))
    <div x-data x-init="$nextTick(() => window.FutaNotify?.success(@js(session('status'))))"></div>
@endif

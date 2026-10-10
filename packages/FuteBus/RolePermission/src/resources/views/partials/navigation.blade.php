<nav aria-label="{{ __('RolePermission::app.navigation') }}" class="mt-5 flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
    @foreach([
        ['key' => 'roles', 'route' => 'access-management.index', 'active' => 'roles'],
        ['key' => 'staff_assignments', 'route' => 'access-management.users', 'active' => 'users'],
        ['key' => 'function_catalog', 'route' => 'access-management.catalog', 'active' => 'catalog'],
    ] as $tab)
        <a href="{{ route($tab['route']) }}" @class([
            'inline-flex items-center gap-2 rounded-lg px-3.5 py-2.5 text-sm font-semibold transition sm:px-4',
            'bg-orange-50 text-[#F26522]' => $activeAccessTab === $tab['active'],
            'text-slate-600 hover:bg-slate-50 hover:text-[#F26522]' => $activeAccessTab !== $tab['active'],
        ]) @if($activeAccessTab === $tab['active']) aria-current="page" @endif>
            @if($tab['active'] === 'roles') <x-heroicon-o-key class="size-4" />
            @elseif($tab['active'] === 'users') <x-heroicon-o-users class="size-4" />
            @else <x-heroicon-o-squares-2x2 class="size-4" /> @endif
            {{ __('RolePermission::app.'.$tab['key']) }}
        </a>
    @endforeach
</nav>

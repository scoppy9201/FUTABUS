@extends('Dashboard::layouts.admin')

@section('title', __('RolePermission::app.staff_assignments'))
@section('page_title', __('RolePermission::app.staff_assignments'))

@section('content')
    <section class="flex items-center gap-3 rounded-xl border border-orange-100 bg-gradient-to-r from-white via-orange-50 to-orange-100 px-5 py-4 shadow-sm sm:px-6">
        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white text-[#F26522] shadow-sm"><x-heroicon-o-users class="size-6" /></span>
        <div class="min-w-0">
            <p class="truncate text-xs font-bold uppercase tracking-wider text-[#ef5222]">{{ $company->name }}</p>
            <h1 class="mt-0.5 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ __('RolePermission::app.staff_assignments') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('RolePermission::app.assignments_description') }}</p>
        </div>
    </section>

    @php($activeAccessTab = 'users')
    @include('RolePermission::partials.navigation')

    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5">
            <div><h2 class="text-base font-bold text-slate-900">{{ __('RolePermission::app.staff_assignments') }}</h2><p class="mt-0.5 text-xs text-slate-500">{{ __('RolePermission::app.assignment_list_hint') }}</p></div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $staffAccounts->total() }} {{ __('RolePermission::app.staff_short') }}</span>
        </div>

        <form method="GET" action="{{ route('access-management.users') }}" role="search" class="flex flex-col gap-2 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:px-5">
            <label for="staff-search" class="sr-only">{{ __('RolePermission::app.search_staff') }}</label>
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="staff-search" name="q" value="{{ $search }}" type="search" maxlength="100" placeholder="{{ __('RolePermission::app.search_staff_hint') }}" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
            </div>
            <button type="submit" class="rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#d95318]">{{ __('RolePermission::app.search_button') }}</button>
            @if($search !== '')
                <a href="{{ route('access-management.users') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('RolePermission::app.clear_search') }}</a>
            @endif
        </form>

        @if($staffAccounts->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto grid size-12 place-items-center rounded-full bg-orange-50 text-[#F26522]"><x-heroicon-o-users class="size-6" /></span>
                <p class="mt-3 text-sm font-semibold text-slate-600">{{ $search !== '' ? __('RolePermission::app.no_staff_search_results') : __('RolePermission::app.empty_staff') }}</p>
                @if($search === '')
                    <a href="{{ route('staff-accounts.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#F26522] px-4 py-2 text-sm font-bold text-white hover:bg-[#d95318]"><x-heroicon-o-arrow-right class="size-4" />{{ __('RolePermission::app.manage_staff') }}</a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-sm">
                    <caption class="sr-only">{{ __('RolePermission::app.staff_assignments') }}</caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="w-[23%] px-4 py-3.5 sm:px-5">{{ __('RolePermission::app.columns.staff') }}</th>
                            <th scope="col" class="w-[20%] px-4 py-3.5 sm:px-5">{{ __('RolePermission::app.columns.current_roles') }}</th>
                            <th scope="col" class="px-4 py-3.5 sm:px-5">{{ __('RolePermission::app.assign_roles') }}</th>
                            <th scope="col" class="w-40 px-3 py-3.5 text-right sm:px-4">{{ __('RolePermission::app.columns.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staffAccounts as $staff)
                            <tr class="align-top transition hover:bg-orange-50/20">
                                <td class="px-4 py-4 sm:px-5">
                                    <div class="flex items-start gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-orange-50 text-sm font-bold text-[#F26522]">{{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-900">{{ $staff->name }}</p>
                                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $staff->email }}</p>
                                            <span @class(['mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold', 'bg-emerald-50 text-emerald-700' => $staff->is_active, 'bg-slate-100 text-slate-500' => ! $staff->is_active])>{{ __('RolePermission::app.'.($staff->is_active ? 'staff_active' : 'staff_inactive')) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 sm:px-5">
                                    @forelse($staff->role_names as $roleName)
                                        <span class="mb-1 mr-1 inline-flex rounded-full border border-orange-100 bg-orange-50 px-2.5 py-1 text-xs font-semibold text-[#d7461a]">{{ $roleName }}</span>
                                    @empty
                                        <span class="text-sm text-slate-400">{{ __('RolePermission::app.no_roles') }}</span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-4 sm:px-5">
                                    <form id="staff-role-form-{{ $staff->id }}" method="post" action="{{ route('access-management.users.assign', $staff->id) }}">
                                        @csrf @method('put')
                                        <input type="hidden" name="q" value="{{ $search }}">
                                        <fieldset class="grid gap-x-4 gap-y-2 sm:grid-cols-2">
                                            <legend class="sr-only">{{ __('RolePermission::app.roles_for', ['name' => $staff->name]) }}</legend>
                                            @forelse($roles as $role)
                                                <label class="inline-flex min-w-0 items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, $staff->role_ids, true)) class="size-4 shrink-0 rounded border-slate-300 text-[#F26522] focus:ring-[#F26522]">
                                                    <span class="truncate">{{ $role->name }}</span>
                                                </label>
                                            @empty
                                                <a href="{{ route('access-management.roles.create') }}" class="font-semibold text-[#F26522] hover:text-[#d95318]">{{ __('RolePermission::app.create_first_role') }}</a>
                                            @endforelse
                                        </fieldset>
                                    </form>
                                </td>
                                <td class="px-3 py-4 text-right sm:px-4">
                                    @if($roles->isNotEmpty())
                                        <button type="submit" form="staff-role-form-{{ $staff->id }}" class="inline-flex whitespace-nowrap rounded-lg bg-[#F26522] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#d95318]">{{ __('RolePermission::app.save_assignments') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($staffAccounts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $staffAccounts->links() }}</div>
            @endif
        @endif
    </section>

    @if(session('status'))
        <p role="status" class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</p>
    @endif
    @error('role_ids')<p role="alert" class="mt-4 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
@endsection

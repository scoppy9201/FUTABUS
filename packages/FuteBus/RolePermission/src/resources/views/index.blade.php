@extends('Dashboard::layouts.admin')

@section('title', __('RolePermission::app.title'))
@section('page_title', __('RolePermission::app.title'))

@section('content')
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-orange-100 bg-gradient-to-r from-white via-orange-50 to-orange-100 px-5 py-4 shadow-sm sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white text-[#F26522] shadow-sm"><x-heroicon-o-key class="size-6" /></span>
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wider text-[#ef5222]">{{ $company->name }}</p>
                <h1 class="mt-0.5 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ __('RolePermission::app.title') }}</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ __('RolePermission::app.description') }}</p>
            </div>
        </div>
        <a href="{{ route('access-management.roles.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#d95318]">
            <x-heroicon-o-plus class="size-5" />{{ __('RolePermission::app.create_role') }}
        </a>
    </section>

    @php($activeAccessTab = 'roles')
    @include('RolePermission::partials.navigation')

    @if(session('status'))
        <p role="status" class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</p>
    @endif

    <section aria-label="{{ __('RolePermission::app.statistics') }}" class="mt-4 grid gap-3 sm:grid-cols-3">
        @foreach([
            ['label' => 'roles_total', 'value' => $roles->count(), 'icon' => 'key', 'color' => 'bg-orange-50 text-[#F26522]'],
            ['label' => 'permissions_attached', 'value' => $roles->sum('permission_count'), 'icon' => 'shield-check', 'color' => 'bg-sky-50 text-sky-600'],
            ['label' => 'staff_assignments_total', 'value' => $roles->sum('user_count'), 'icon' => 'users', 'color' => 'bg-emerald-50 text-emerald-600'],
        ] as $stat)
            <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $stat['color'] }}">
                    @if($stat['icon'] === 'key') <x-heroicon-o-key class="size-5" />
                    @elseif($stat['icon'] === 'shield-check') <x-heroicon-o-shield-check class="size-5" />
                    @else <x-heroicon-o-users class="size-5" /> @endif
                </span>
                <div><p class="text-sm font-medium text-slate-500">{{ __('RolePermission::app.'.$stat['label']) }}</p><p class="text-xl font-extrabold text-slate-900">{{ $stat['value'] }}</p></div>
            </article>
        @endforeach
    </section>

    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5">
            <div><h2 class="text-base font-bold text-slate-900">{{ __('RolePermission::app.roles') }}</h2><p class="mt-0.5 text-xs text-slate-500">{{ __('RolePermission::app.roles_list_hint') }}</p></div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $roles->count() }} {{ __('RolePermission::app.roles_short') }}</span>
        </div>
        @if($roles->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto grid size-12 place-items-center rounded-full bg-orange-50 text-[#F26522]"><x-heroicon-o-key class="size-6" /></span>
                <p class="mt-3 text-sm font-semibold text-slate-600">{{ __('RolePermission::app.empty_roles') }}</p>
                <a href="{{ route('access-management.roles.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#F26522] px-4 py-2 text-sm font-bold text-white hover:bg-[#d95318]"><x-heroicon-o-plus class="size-4" />{{ __('RolePermission::app.create_role') }}</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <caption class="sr-only">{{ __('RolePermission::app.roles') }}</caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach(['name', 'description', 'permission_count', 'user_count', 'actions'] as $column)
                                <th scope="col" class="whitespace-nowrap px-4 py-3.5 sm:px-5">{{ __('RolePermission::app.columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($roles as $role)
                            <tr class="transition hover:bg-orange-50/30">
                                <td class="px-4 py-4 font-bold text-slate-900 sm:px-5">{{ $role->name }}</td>
                                <td class="max-w-sm px-4 py-4 text-slate-600 sm:px-5">{{ $role->description ?: '—' }}</td>
                                <td class="px-4 py-4"><span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">{{ $role->permission_count }}</span></td>
                                <td class="px-4 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $role->user_count }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('access-management.roles.edit', $role->id) }}" class="inline-flex items-center gap-1.5 font-semibold text-[#F26522] transition hover:text-[#d95318]"><x-heroicon-o-pencil-square class="size-4" />{{ __('RolePermission::app.edit') }}</a>
                                        <form method="post" action="{{ route('access-management.roles.delete', $role->id) }}" data-confirm data-confirm-title="{{ __('RolePermission::app.delete_role') }}" data-confirm-message="{{ __('RolePermission::app.delete_role_confirmation', ['name' => $role->name]) }}">
                                            @csrf @method('delete')
                                            <button type="submit" class="inline-flex items-center gap-1.5 font-semibold text-rose-600 transition hover:text-rose-700"><x-heroicon-o-trash class="size-4" />{{ __('RolePermission::app.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

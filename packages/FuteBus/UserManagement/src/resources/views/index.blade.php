@extends('Dashboard::layouts.admin')

@section('title', __('UserManagement::app.title'))
@section('page_title', __('UserManagement::app.title'))

@section('content')
    <section class="relative isolate -mx-4 -mt-4 mb-4 flex min-h-24 items-center justify-between gap-4 overflow-hidden border-y border-orange-100 bg-gradient-to-r from-white via-orange-50 to-orange-100 px-5 py-3 shadow-sm md:-mx-6 md:-mt-6 md:px-8 lg:-mx-8 lg:-mt-8 lg:px-10">
        <div class="relative z-10 flex min-w-0 flex-1 items-center gap-3 sm:pr-44">
            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-white/90 text-[#F26522] shadow-sm"><x-heroicon-o-users class="size-5" /></span>
            <div class="min-w-0">
                <h1 class="truncate text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ __('UserManagement::app.title') }}</h1>
                <p class="mt-0.5 line-clamp-2 max-w-2xl text-sm font-medium text-slate-600">{{ __('UserManagement::app.description') }}</p>
            </div>
        </div>
        <img src="{{ asset('images/auth/transfer-bus.png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute right-5 bottom-0 z-0 hidden h-20 w-52 object-contain object-right-bottom opacity-90 sm:block">
    </section>

    @if(session('status'))
        <div role="status" x-data="{ visible: true }" x-show="visible" x-transition.opacity.duration.300ms x-init="setTimeout(() => visible = false, 4000)" class="fixed bottom-5 right-5 z-50 max-w-[calc(100vw-2.5rem)] rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800 shadow-lg">
            {{ session('status') }}
        </div>
    @endif

    <form method="get" action="{{ route('staff-accounts.index') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <label class="min-w-60 flex-1 text-xs font-semibold text-slate-700">
            {{ __('UserManagement::app.search') }}
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('UserManagement::app.search_hint') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
        </label>
        <label class="min-w-40 text-xs font-semibold text-slate-700">
            {{ __('UserManagement::app.status') }}
            <select name="status" x-on:change="$el.form.requestSubmit()" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
                <option value="">{{ __('UserManagement::app.all_statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('UserManagement::app.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('UserManagement::app.inactive') }}</option>
            </select>
        </label>
    </form>

    <section aria-label="{{ __('UserManagement::app.statistics') }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['key' => 'total', 'label' => 'total_staff', 'icon' => 'users', 'icon_class' => 'bg-orange-50 text-[#F26522]'],
            ['key' => 'active', 'label' => 'active', 'icon' => 'user-circle', 'icon_class' => 'bg-emerald-50 text-emerald-600'],
            ['key' => 'inactive', 'label' => 'locked', 'icon' => 'lock-closed', 'icon_class' => 'bg-rose-50 text-rose-600'],
        ] as $stat)
            <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $stat['icon_class'] }}">
                    @if($stat['icon'] === 'users') <x-heroicon-o-users class="size-5" />
                    @elseif($stat['icon'] === 'user-circle') <x-heroicon-o-user-circle class="size-5" />
                    @else <x-heroicon-o-lock-closed class="size-5" /> @endif
                </span>
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ __('UserManagement::app.'.$stat['label']) }}</p>
                    <p class="text-xl font-extrabold text-slate-900">{{ $statistics[$stat['key']] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="relative mt-3 overflow-visible rounded-xl border border-slate-200 bg-white shadow-sm" x-data="{ openActionMenu: null }" @click="if (!$event.target.closest('[data-staff-action-menu]')) openActionMenu = null" @keydown.escape.window="openActionMenu = null">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-2.5">
            <h2 class="text-base font-bold text-slate-900">{{ __('UserManagement::app.list_title') }}</h2>
            <a href="{{ route('staff-accounts.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#d95318]">
                <x-heroicon-o-plus class="size-5" />
                {{ __('UserManagement::app.create') }}
            </a>
        </div>
        <div class="overflow-x-auto overflow-y-visible lg:overflow-visible">
            <table class="w-full min-w-[880px] table-fixed text-left text-sm">
                <caption class="sr-only">{{ __('UserManagement::app.title') }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="w-[5%] px-2 py-2 text-center">{{ __('UserManagement::app.columns.serial') }}</th>
                        <th scope="col" class="w-[21%] px-2 py-2">{{ __('UserManagement::app.columns.name') }}</th>
                        <th scope="col" class="w-[14%] px-2 py-2">{{ __('UserManagement::app.columns.phone') }}</th>
                        <th scope="col" class="w-[24%] px-2 py-2">{{ __('UserManagement::app.columns.email') }}</th>
                        <th scope="col" class="w-[14%] px-2 py-2">{{ __('UserManagement::app.columns.status') }}</th>
                        <th scope="col" class="w-[14%] px-2 py-2">{{ __('UserManagement::app.columns.created_at') }}</th>
                        <th scope="col" class="w-[8%] px-2 py-2 text-center">{{ __('UserManagement::app.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staffAccounts as $staff)
                        <tr class="transition hover:bg-orange-50/30">
                            <td class="whitespace-nowrap px-2 py-2 text-center text-sm text-slate-500">{{ $staffAccounts->firstItem() + $loop->index }}</td>
                            <td class="px-2 py-2">
                                <span class="block truncate font-semibold text-slate-900" title="{{ $staff->name }}">{{ $staff->name }}</span>
                            </td>
                            <td class="truncate whitespace-nowrap px-2 py-2 text-sm text-slate-600" title="{{ $staff->phone }}">{{ $staff->phone ?: '—' }}</td>
                            <td class="truncate whitespace-nowrap px-2 py-2 text-sm text-slate-600" title="{{ $staff->email }}">{{ $staff->email }}</td>
                            <td class="whitespace-nowrap px-2 py-2">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $staff->is_active,
                                    'bg-amber-50 text-amber-700' => ! $staff->is_active,
                                ])>{{ __('UserManagement::app.'.($staff->is_active ? 'active' : 'locked')) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-2 py-2 text-xs text-slate-600">{{ \Illuminate\Support\Carbon::parse($staff->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap px-2 py-2 text-center">
                                <div class="relative w-full" data-staff-action-menu>
                                    <button type="button" @click="openActionMenu = openActionMenu === {{ $staff->id }} ? null : {{ $staff->id }}" :aria-expanded="(openActionMenu === {{ $staff->id }}).toString()" aria-haspopup="menu" aria-label="{{ __('UserManagement::app.actions_for', ['name' => $staff->name]) }}" class="mx-auto grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F26522]">
                                        <x-heroicon-o-ellipsis-vertical class="size-4" />
                                    </button>
                                    <div x-cloak x-show="openActionMenu === {{ $staff->id }}" x-transition.origin.top.right role="menu" class="absolute right-0 top-full z-50 mt-1 w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                                        <a role="menuitem" href="{{ route('staff-accounts.show', $staff->id) }}" @click="openActionMenu = null" class="block rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">{{ __('UserManagement::app.view_action') }}</a>
                                        <a role="menuitem" href="{{ route('staff-accounts.edit', $staff->id) }}" @click="openActionMenu = null" class="block rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">{{ __('UserManagement::app.edit_action') }}</a>
                                        <form method="post" action="{{ route('staff-accounts.status', $staff->id) }}" data-confirm data-confirm-title="{{ __($staff->is_active ? 'UserManagement::app.deactivate_title' : 'UserManagement::app.activate_title') }}" data-confirm-message="{{ __($staff->is_active ? 'UserManagement::app.deactivate_message' : 'UserManagement::app.activate_message', ['name' => $staff->name]) }}">
                                            @csrf @method('patch')
                                            <input type="hidden" name="is_active" value="{{ $staff->is_active ? 0 : 1 }}">
                                            <button type="submit" @click="openActionMenu = null" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">{{ __($staff->is_active ? 'UserManagement::app.deactivate' : 'UserManagement::app.activate') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('staff-accounts.destroy', $staff->id) }}" data-confirm data-confirm-title="{{ __('UserManagement::app.delete_title') }}" data-confirm-message="{{ __('UserManagement::app.delete_message', ['name' => $staff->name]) }}">
                                            @csrf @method('delete')
                                            <button type="submit" @click="openActionMenu = null" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-700 transition hover:bg-rose-50">{{ __('UserManagement::app.delete_action') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-full bg-slate-50 text-slate-300"><x-heroicon-o-inbox class="size-8" /></span>
                                <p class="mt-3 text-sm font-semibold text-slate-500">{{ __('UserManagement::app.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-2.5">
            <p class="text-xs font-medium text-slate-500">
                @if($staffAccounts->total() > 0)
                    {{ __('UserManagement::app.showing', ['from' => $staffAccounts->firstItem(), 'to' => $staffAccounts->lastItem(), 'total' => $staffAccounts->total()]) }}
                @else
                    Hiển thị 0 / 0 nhân viên
                @endif
            </p>
            @php
                $totalPages = $staffAccounts->lastPage();
                $currentPage = $staffAccounts->currentPage();

                if ($totalPages <= 4) {
                    $paginationRange = range(1, $totalPages);
                } elseif ($currentPage <= 2) {
                    $paginationRange = [1, 2, '...', $totalPages];
                } elseif ($currentPage === 3) {
                    $paginationRange = [1, 2, 3, '...', $totalPages];
                } elseif ($currentPage >= $totalPages - 2) {
                    $paginationRange = array_merge([1, 2, '...'], range($totalPages - 2, $totalPages));
                } else {
                    $paginationRange = array_merge([1, 2, '...'], range($currentPage - 1, $currentPage + 1), ['...', $totalPages]);
                }
            @endphp
            <nav aria-label="{{ __('UserManagement::app.pagination_label') }}" class="flex items-center gap-1">
                @if($staffAccounts->previousPageUrl())
                    <a href="{{ $staffAccounts->previousPageUrl() }}" aria-label="{{ __('UserManagement::app.previous_page') }}" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-[#F26522] hover:text-[#F26522]"><x-heroicon-o-chevron-left class="size-4" /></a>
                @else
                    <span aria-disabled="true" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300"><x-heroicon-o-chevron-left class="size-4" /></span>
                @endif

                @foreach($paginationRange as $item)
                    @if($item === '...')
                        <span class="px-1 text-slate-400">…</span>
                    @elseif($item === $currentPage)
                        <span aria-current="page" class="grid size-8 place-items-center rounded-lg bg-[#F26522] text-xs font-bold text-white shadow-sm">{{ $item }}</span>
                    @else
                        <a href="{{ $staffAccounts->url($item) }}" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:border-[#F26522] hover:text-[#F26522]">{{ $item }}</a>
                @endif
                @endforeach

                @if($staffAccounts->nextPageUrl())
                    <a href="{{ $staffAccounts->nextPageUrl() }}" aria-label="{{ __('UserManagement::app.next_page') }}" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-[#F26522] hover:text-[#F26522]"><x-heroicon-o-chevron-right class="size-4" /></a>
                @else
                    <span aria-disabled="true" class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300"><x-heroicon-o-chevron-right class="size-4" /></span>
                @endif
            </nav>
        </div>
    </section>
@endsection

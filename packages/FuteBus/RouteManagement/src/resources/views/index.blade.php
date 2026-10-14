@extends('Dashboard::layouts.admin')
@section('title', __('RouteManagement::app.title'))
@section('content')
    @include('RouteManagement::partials.header', ['recordCount' => $routes->total()])
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="get" action="{{ route('route-management.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <label for="route-search" class="sr-only">{{ __('RouteManagement::app.search') }}</label>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="route-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('RouteManagement::app.search_hint') }}"
                    class="w-72 max-w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-800 shadow-sm outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/20">
            </div>
            <label for="route-status" class="sr-only">{{ __('RouteManagement::app.fields.is_active') }}</label>
            <select id="route-status" name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                @foreach(['active', 'inactive', 'all'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('RouteManagement::app.'.$status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white hover:bg-futa-orange-dark">{{ __('RouteManagement::app.search') }}</button>
            @if(($filters['search'] ?? '') || $filters['status'] !== 'active')
                <a href="{{ route('route-management.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('RouteManagement::app.clear_filters') }}</a>
            @endif
        </form>
            @if(auth()->user()->hasPermissionTo('route.create'))
                <button type="button" data-route-open class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-futa-orange-dark hover:shadow-lg">
                    <x-heroicon-o-plus class="size-4" />{{ __('RouteManagement::app.create') }}
                </button>
            @endif
    </div>
    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <caption class="sr-only">{{ __('RouteManagement::app.routes') }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-4">{{ __('RouteManagement::app.number') }}</th>
                        @foreach(['code', 'origin_city', 'vehicle_type', 'distance_km', 'base_price', 'is_active'] as $field)
                            <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('RouteManagement::app.fields.'.$field) }}</th>
                        @endforeach
                        <th scope="col" class="px-5 py-4 text-center">{{ __('RouteManagement::app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($routes as $r)
                        <tr class="transition-colors hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $routes->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ $r->code }}</td>
                            <td class="min-w-60 px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $r->origin_city }} → {{ $r->destination_city }}</p>
                                <p class="mt-1 max-w-xs truncate text-xs text-slate-400" title="{{ $r->origin_station }} → {{ $r->destination_station }}">{{ $r->origin_station ?? __('RouteManagement::app.not_set') }} → {{ $r->destination_station ?? __('RouteManagement::app.not_set') }}</p>
                                <p class="mt-1 text-xs {{ $r->stop_count >= 2 ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ __('RouteManagement::app.'.($r->stop_count >= 2 ? 'ready' : 'incomplete')) }} · {{ $r->duration_minutes ?: 0 }} {{ __('RouteManagement::app.minutes') }}
                                </p>
                            </td>
                            <td class="px-5 py-4">{{ __('RouteManagement::app.vehicles')[$r->vehicle_type] ?? $r->vehicle_type }}</td>
                            <td class="px-5 py-4">{{ $r->distance_km }} km</td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold">{{ number_format($r->base_price, 0, ',', '.') }} đ</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $r->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ __('RouteManagement::app.'.($r->is_active ? 'active' : 'inactive')) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('route-management.stops.index', ['route_id' => $r->id]) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 px-3 py-1.5 text-xs font-semibold text-futa-orange hover:bg-futa-orange-soft">
                                        <x-heroicon-o-map-pin class="size-3.5" />{{ __('RouteManagement::app.stops') }}
                                    </a>
                                    @if(auth()->user()->hasPermissionTo('route.update'))
                                        <button type="button" data-route-open data-record="{{ json_encode($r) }}" aria-label="{{ __('RouteManagement::app.edit') }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                            <x-heroicon-o-pencil-square class="size-3.5" />{{ __('RouteManagement::app.edit_short') }}
                                        </button>
                                    @endif
                                    @if(auth()->user()->hasPermissionTo('route.delete'))
                                        <form method="post" action="{{ route('route-management.destroy', $r->id) }}" data-confirm
                                            data-confirm-title="{{ __('RouteManagement::app.delete') }}" data-confirm-message="{{ __('RouteManagement::app.delete_route_confirm', ['name' => $r->name]) }}">
                                            @csrf @method('DELETE')
                                            <button @disabled($r->schedule_total > 0 || $r->trip_count > 0)
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 disabled:cursor-not-allowed disabled:border-slate-100 disabled:text-slate-300">
                                                <x-heroicon-o-trash class="size-3.5" />{{ __('RouteManagement::app.delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-10"><x-core::empty-state :title="__('RouteManagement::app.empty')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-core::data-pagination :paginator="$routes" class="border-t border-slate-100 px-5 py-4" />
    </section>
    @include('RouteManagement::partials.panel', ['kind' => 'route'])
    @vite('packages/FuteBus/RouteManagement/src/resources/js/app.js')
@endsection

@extends('Dashboard::layouts.admin')
@section('title', __('RouteManagement::app.title'))
@section('content')
    @include('RouteManagement::partials.header', ['recordCount' => $stops->count()])
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="get" action="{{ route('route-management.stops.index') }}" class="flex flex-wrap items-center gap-2">
            <label for="stop-route" class="sr-only">{{ __('RouteManagement::app.select_route') }}</label>
            <select id="stop-route" name="route_id" class="w-96 max-w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                @forelse($routes as $option)
                    <option value="{{ $option->id }}" @selected($route?->id === $option->id)>
                        {{ $option->code }} &middot; {{ $option->name }}{{ $option->is_active ? '' : ' - '.__('RouteManagement::app.inactive') }}
                    </option>
                @empty
                    <option value="">{{ __('RouteManagement::app.select_route') }}</option>
                @endforelse
            </select>
            <button class="rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white hover:bg-futa-orange-dark">{{ __('RouteManagement::app.search') }}</button>
        </form>
        @if($route && auth()->user()->hasPermissionTo('route.update'))
            <button type="button" data-stop-open
                class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-futa-orange-dark hover:shadow-lg">
                <x-heroicon-o-plus class="size-4" />{{ __('RouteManagement::app.add_stop') }}
            </button>
        @endif
    </div>
    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($route)
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">{{ $route->name }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $route->code }} &middot; {{ $route->distance_km }} km &middot; {{ $route->duration_minutes }} {{ __('RouteManagement::app.minutes') }}</p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $stops->count() >= 2 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ __('RouteManagement::app.'.($stops->count() >= 2 ? 'ready' : 'incomplete')) }}
                </span>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <caption class="sr-only">{{ __('RouteManagement::app.stops') }} - {{ $route->name }}</caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach(['position', 'name', 'stop_type', 'leg_minutes', 'offset'] as $field)
                                <th scope="col" class="px-5 py-4">{{ __('RouteManagement::app.fields.'.$field) }}</th>
                            @endforeach
                            <th scope="col" class="px-5 py-4 text-center">{{ __('RouteManagement::app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stops as $stop)
                            <tr class="transition-colors hover:bg-futa-orange-soft/30">
                                <td class="px-5 py-4 text-slate-500">{{ $stop->stop_order }}</td>
                                <td class="min-w-60 px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $stop->name }}</p>
                                    <p class="mt-1 max-w-sm text-xs leading-5 text-slate-400">{{ $stop->address }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">{{ __('RouteManagement::app.types.'.$stop->stop_type) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">{{ $stop->leg_minutes }} {{ __('RouteManagement::app.minutes') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">+{{ $stop->offset_minutes }} {{ __('RouteManagement::app.minutes') }}</td>
                                <td class="px-5 py-4">
                                    @if(auth()->user()->hasPermissionTo('route.update'))
                                        <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                            <button type="button" data-stop-open data-record="{{ json_encode((array) $stop + ['position' => $stop->stop_order]) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                                <x-heroicon-o-pencil-square class="size-3.5" />{{ __('RouteManagement::app.edit_short') }}
                                            </button>
                                            <form method="post" action="{{ route('route-management.stops.destroy', [$route->id, $stop->id]) }}" data-confirm
                                                data-confirm-title="{{ __('RouteManagement::app.delete') }}" data-confirm-message="{{ __('RouteManagement::app.delete_stop_confirm', ['name' => $stop->name]) }}">
                                                @csrf @method('DELETE')
                                                <button class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                                    <x-heroicon-o-trash class="size-3.5" />{{ __('RouteManagement::app.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10"><x-core::empty-state :title="__('RouteManagement::app.empty_stops')" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="flex items-start gap-2 border-t border-slate-100 px-5 py-4 text-xs leading-5 text-slate-500">
                <x-heroicon-o-information-circle class="mt-0.5 size-4 shrink-0" />{{ __('RouteManagement::app.timing_hint') }}
            </footer>
        @else
            <x-core::empty-state :title="__('RouteManagement::app.no_route')" />
        @endif
    </section>
    @if($route) @include('RouteManagement::partials.panel', ['kind' => 'stop']) @endif
    @vite('packages/FuteBus/RouteManagement/src/resources/js/app.js')
@endsection

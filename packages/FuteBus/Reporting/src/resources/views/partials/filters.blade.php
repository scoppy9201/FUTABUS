@php
    $advancedCount = collect(['route_id', 'trip_id', 'bus_id', 'status', 'trip_status'])
        ->filter(fn ($key) => !empty($filters[$key]) && $filters[$key] !== 'all')->count();
@endphp
<form id="report-filters" method="get" action="{{ route('reporting.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-4 [&>label]:min-w-44 [&>label]:flex-1">

            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.report_type') }}
                <select name="type" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['overview', 'revenue', 'payments', 'tickets', 'trips'] as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ __('Reporting::app.'.$type) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.period') }}
                <select name="period" data-report-period class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['today', '7days', '30days', 'custom'] as $period)
                        <option value="{{ $period }}" @selected($filters['period'] === $period)>{{ __('Reporting::app.'.$period) }}</option>
                    @endforeach
                </select>
            </label>
            <label data-report-date-field @if($filters['period'] !== 'custom') hidden @endif class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.from') }}
                <input type="date" name="from" value="{{ $filters['from'] }}" required data-report-date class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>
            <label data-report-date-field @if($filters['period'] !== 'custom') hidden @endif class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.to') }}
                <input type="date" name="to" value="{{ $filters['to'] }}" required data-report-date class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-report-advanced-open aria-haspopup="dialog" aria-controls="report-advanced-filters"
                class="inline-flex items-center gap-2 rounded-lg border border-futa-green/20 bg-futa-green/5 px-4 py-2.5 text-sm font-semibold text-futa-green hover:bg-futa-green/10">
                <x-heroicon-o-adjustments-horizontal class="size-4" />{{ __('Reporting::filters.advanced') }}
                @if($advancedCount)<span class="grid size-5 place-items-center rounded-full bg-futa-green text-xs text-white">{{ $advancedCount }}</span>@endif
            </button>
            <button type="submit" data-report-submit name="run_report" value="1"
                class="rounded-lg bg-futa-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-futa-orange-dark">{{ __('Reporting::app.apply') }}</button>
            <a href="{{ route('reporting.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Reporting::app.reset') }}</a>
        </div>
    </div>
    <p class="mt-3 text-xs text-slate-500">{{ __('Reporting::app.from') }}: {{ $filters['from'] }} &middot; {{ __('Reporting::app.to') }}: {{ $filters['to'] }}</p>
</form>
<x-core::side-panel id="report-advanced-filters" :title="__('Reporting::filters.advanced')" :description="__('Reporting::filters.intro')">
    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6" data-report-advanced-fields>
            <label class="block text-sm font-semibold text-slate-700">{{ __('Reporting::app.route') }}
                <select form="report-filters" name="route_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_routes') }}</option>
                    @foreach($options['routes'] as $route)
                        <option value="{{ $route->id }}" @selected((string) $filters['route_id'] === (string) $route->id)>{{ $route->origin_city }} → {{ $route->destination_city }} ({{ $route->code }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-semibold text-slate-700">{{ __('Reporting::app.trip') }}
                <select form="report-filters" name="trip_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_trips') }}</option>
                    @foreach($options['trips'] as $trip)
                        <option value="{{ $trip->id }}" @selected((string) $filters['trip_id'] === (string) $trip->id)>{{ $trip->origin_city }} → {{ $trip->destination_city }} · {{ $trip->departure_time }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-semibold text-slate-700">{{ __('Reporting::app.bus') }}
                <select form="report-filters" name="bus_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_buses') }}</option>
                    @foreach($options['buses'] as $bus)
                        <option value="{{ $bus->id }}" @selected((string) $filters['bus_id'] === (string) $bus->id)>{{ $bus->license_plate }}</option>
                    @endforeach
                </select>
            </label>
            @if(in_array($filters['type'], ['payments', 'tickets', 'trips'], true))
                <label class="block text-sm font-semibold text-slate-700">{{ __('Reporting::app.status') }}
                    <select form="report-filters" name="status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                        @php
                            $statuses = match($filters['type']) {
                                'payments' => ['all', 'completed', 'refunded', 'pending', 'failed'],
                                'tickets' => ['all', 'active', 'used', 'cancelled', 'refunded'],
                                default => ['all', 'scheduled', 'departed', 'arrived', 'cancelled'],
                            };
                        @endphp
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('Reporting::app.'.$status) }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="block text-sm font-semibold text-slate-700">{{ __('Reporting::app.trip_status') }}
                <select form="report-filters" name="trip_status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['all', 'scheduled', 'departed', 'arrived', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($filters['trip_status'] === $status)>{{ __('Reporting::app.'.$status) }}</option>
                    @endforeach
                </select>
            </label>

    </div>
    <footer class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-6 py-4">
        <button type="button" data-report-advanced-reset class="text-sm font-semibold text-slate-500 hover:text-futa-green">{{ __('Reporting::filters.clear') }}</button>
        <div class="flex gap-2">
            <button type="button" data-report-advanced-cancel class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">{{ __('Reporting::filters.cancel') }}</button>
            <button type="button" data-report-advanced-apply class="rounded-lg bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white hover:bg-futa-orange-dark">{{ __('Reporting::filters.apply') }}</button>
        </div>
    </footer>
</x-core::side-panel>

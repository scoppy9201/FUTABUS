@extends('Dashboard::layouts.admin')

@section('title', __('Reporting::app.title'))
@section('page_title', __('Reporting::app.title'))

@section('content')
    @vite('packages/FuteBus/Reporting/src/resources/js/app.js')
    <div class="space-y-6" data-reporting-chart
        data-labels="{{ json_encode($report['labels']) }}"
        data-values="{{ json_encode($report['series']) }}"
        data-counts="{{ json_encode($report['rows']->pluck('count')->all()) }}"
        data-refunds="{{ json_encode($report['refund_series']) }}"
        data-status="{{ json_encode($report['status']) }}"
        data-chart-label="{{ __('Reporting::app.chart_value') }}"
        data-report-type="{{ $filters['type'] }}"
        data-total-revenue-label="{{ __('Reporting::app.donut_total_revenue') }}"
        data-total-count-label="{{ __('Reporting::app.donut_total_count') }}"
        data-million-unit="{{ __('Reporting::app.million') }}">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Reporting::app.title') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Reporting::app.description') }}</p>
            </div>
            <button type="button" class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-bold text-orange-800 hover:bg-orange-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                data-report-export="{{ route('reporting.export', array_merge(request()->except(['run_report', 'format']), ['format' => 'pdf'])) }}"
                data-export-success="{{ __('Reporting::app.export_success') }}"
                data-export-error="{{ __('Reporting::app.export_failed') }}">
                <x-heroicon-o-arrow-down-tray class="mr-1 inline size-4" />
                {{ __('Reporting::app.export_pdf') }}
            </button>
        </header>

        <form method="get" action="{{ route('reporting.index') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
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
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.from') }}
                <input type="date" name="from" value="{{ $filters['from'] }}" required data-report-date class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.to') }}
                <input type="date" name="to" value="{{ $filters['to'] }}" required data-report-date class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.route') }}
                <select name="route_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_routes') }}</option>
                    @foreach($options['routes'] as $route)
                        <option value="{{ $route->id }}" @selected((string) $filters['route_id'] === (string) $route->id)>{{ $route->origin_city }} → {{ $route->destination_city }} ({{ $route->code }})</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.trip') }}
                <select name="trip_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_trips') }}</option>
                    @foreach($options['trips'] as $trip)
                        <option value="{{ $trip->id }}" @selected((string) $filters['trip_id'] === (string) $trip->id)>{{ $trip->origin_city }} → {{ $trip->destination_city }} · {{ $trip->departure_time }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.bus') }}
                <select name="bus_id" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">{{ __('Reporting::app.all_buses') }}</option>
                    @foreach($options['buses'] as $bus)
                        <option value="{{ $bus->id }}" @selected((string) $filters['bus_id'] === (string) $bus->id)>{{ $bus->license_plate }}</option>
                    @endforeach
                </select>
            </label>
            @if(in_array($filters['type'], ['payments', 'tickets', 'trips'], true))
                <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.status') }}
                    <select name="status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
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
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.trip_status') }}
                <select name="trip_status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['all', 'scheduled', 'departed', 'arrived', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($filters['trip_status'] === $status)>{{ __('Reporting::app.'.$status) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="flex-1 rounded-lg bg-futa-orange px-4 py-2.5 text-sm font-bold text-white hover:bg-orange-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600" type="submit" name="run_report" value="1">{{ __('Reporting::app.apply') }}</button>
                <a href="{{ route('reporting.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Reporting::app.reset') }}</a>
            </div>
        </form>

        <p class="text-xs text-slate-500">{{ __('Reporting::app.date_basis_payment') }} {{ __('Reporting::app.refund_basis_note') }}</p>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Reporting::app.title') }}">
            @php
                $kpis = [
                    ['label' => 'total_amount', 'value' => number_format($report['kpis']['net_revenue']).' ₫', 'type' => 'revenue'],
                    ['label' => 'tickets_sold', 'value' => number_format($report['kpis']['tickets_sold']), 'type' => 'tickets'],
                    ['label' => 'occupancy', 'value' => number_format($report['kpis']['occupancy'], 1).'%', 'type' => 'trips'],
                    ['label' => 'completed_trips', 'value' => number_format($report['kpis']['completed_trips']), 'type' => 'trips'],
                ];
            @endphp
            @foreach($kpis as $kpi)
                <a href="{{ route('reporting.index', array_merge(request()->except('run_report'), ['type' => $kpi['type'], 'status' => 'all'])) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-orange-300">
                    <p class="text-sm font-semibold text-slate-500">{{ __('Reporting::app.'.$kpi['label']) }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $kpi['value'] }}</p>
                    @if($kpi['label'] === 'total_amount')
                        <p class="mt-1 text-xs text-slate-500">{{ __('Reporting::app.previous_period') }}: {{ number_format($report['comparison']['revenue']) }} ₫</p>
                    @endif
                </a>
            @endforeach
        </section>
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['gross_revenue', number_format($report['kpis']['gross_revenue']).' ₫'],
                ['average_fare', number_format($report['kpis']['average_fare']).' ₫'],
                ['cancellation_rate', number_format($report['kpis']['cancellation_rate'], 1).'%'],
                ['payment_success_rate', number_format($report['kpis']['payment_success_rate'], 1).'%'],
            ] as [$label, $value])
                <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold text-slate-500">{{ __('Reporting::app.'.$label) }}</p>
                    <p class="mt-1 text-lg font-bold text-slate-800">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid min-w-0 gap-4 lg:grid-cols-2" aria-label="{{ __('Reporting::app.chart_title') }}">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h2 class="text-base font-bold text-slate-900">{{ __('Reporting::app.column_chart') }}</h2>
                @if(in_array($filters['type'], ['revenue', 'payments', 'tickets'], true) && array_sum($report['refund_series']) > 0)
                    <ul class="mt-3 flex gap-4 text-xs text-slate-600">
                        <li>
                            <span class="mr-2 inline-block size-2.5 rounded-sm bg-orange-500"></span>
                            {{ in_array($filters['type'], ['revenue', 'overview'], true)
                                ? __('Reporting::app.total_amount')
                                : ($filters['type'] === 'tickets' ? __('Reporting::app.active') : __('Reporting::app.completed')) }}
                        </li>
                        <li><span class="mr-2 inline-block size-2.5 rounded-sm bg-rose-600"></span>{{ __('Reporting::app.refunded') }}</li>
                    </ul>
                @endif
                @if($report['rows']->isEmpty())
                    <div class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</div>
                @else
                    <div class="mt-4 h-80 w-full"><canvas class="block h-full w-full" data-report-bar role="img" aria-label="{{ __('Reporting::app.column_chart') }}"></canvas></div>
                @endif
            </article>
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">{{ __('Reporting::app.line_chart') }}</h2>
                @if($report['rows']->isEmpty())
                    <div class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</div>
                @else
                    <div class="mt-4 h-72 w-full"><canvas class="block h-full w-full" data-report-line role="img" aria-label="{{ __('Reporting::app.line_chart') }}"></canvas></div>
                @endif
            </article>
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">{{ __('Reporting::app.donut_chart') }}</h2>
                @if(empty($report['status']))
                    <div class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</div>
                @else
                    <div class="mt-4 h-64"><canvas class="block h-full w-full" data-report-donut role="img" aria-label="{{ __('Reporting::app.donut_chart') }}"></canvas></div>
                    <ul class="mt-4 flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-slate-600">
                        @php
                            $donutTotal = array_sum($report['status']);
                            $isRevenueDonut = in_array($filters['type'], ['overview', 'revenue'], true);
                        @endphp
                        @foreach($report['status'] as $key => $value)
                            @php
                                $colors = ['#f97316', '#3b82f6', '#16a34a', '#64748b'];
                                $color = $isRevenueDonut
                                    ? ($colors[$loop->index] ?? '#64748b')
                                    : ([
                                        'completed' => '#f97316', 'pending' => '#0f766e', 'failed' => '#2563eb',
                                        'refunded' => '#dc2626', 'active' => '#0f766e', 'used' => '#2563eb',
                                        'cancelled' => '#64748b', 'scheduled' => '#2563eb', 'departed' => '#f97316',
                                        'arrived' => '#16a34a',
                                    ][$key] ?? '#64748b');
                                $share = $donutTotal > 0 ? ((float) $value / $donutTotal) * 100 : 0;
                            @endphp
                            <li class="flex items-center gap-2"><span class="size-2.5 rounded-full" style="background-color: {{ $color }}"></span>
                                {{ $isRevenueDonut ? $key : __('Reporting::app.'.$key) }} · {{ number_format($value) }}
                                @if($isRevenueDonut) ₫ @endif · {{ number_format($share, 2) }}%
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <h2 class="px-5 py-4 text-lg font-bold text-slate-900">{{ __('Reporting::app.top_routes') }}</h2>
                <div class="overflow-x-auto"><table class="w-full min-w-[480px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('Reporting::app.route_name') }}</th><th class="px-5 py-3">{{ __('Reporting::app.tickets_sold') }}</th><th class="px-5 py-3">{{ __('Reporting::app.total_amount') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['top_routes'] as $route)
                            <tr>
                                <td class="px-5 py-3 font-semibold">
                                    <a class="text-slate-800 hover:text-futa-orange"
                                        href="{{ route('reporting.index', array_merge(request()->except('run_report'), ['type' => 'trips', 'status' => 'all', 'route_id' => $route->route_id, 'trip_id' => null])) }}">
                                        {{ $route->origin_city }} → {{ $route->destination_city }}
                                    </a>
                                </td>
                                <td class="px-5 py-3">{{ number_format($route->tickets_sold) }}</td>
                                <td class="px-5 py-3">{{ number_format($route->revenue) }} ₫</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">{{ __('Reporting::app.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <h2 class="px-5 py-4 text-lg font-bold text-slate-900">{{ __('Reporting::app.top_trips') }}</h2>
                <div class="overflow-x-auto"><table class="w-full min-w-[480px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ __('Reporting::app.route_name') }}</th>
                            <th class="px-5 py-3">{{ __('Reporting::app.trip_departure') }}</th>
                            <th class="px-5 py-3">{{ __('Reporting::app.seats_sold') }}</th>
                            <th class="px-5 py-3">{{ __('Reporting::app.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['top_trips'] as $trip)
                            <tr>
                                <td class="px-5 py-3 font-semibold">
                                    <a class="text-slate-800 hover:text-futa-orange"
                                        href="{{ route('reporting.index', array_merge(request()->except('run_report'), ['type' => 'trips', 'status' => 'all', 'trip_id' => $trip->id, 'route_id' => null])) }}">
                                        {{ $trip->origin_city }} → {{ $trip->destination_city }}
                                        <span class="block text-xs font-normal text-slate-500">{{ $trip->license_plate }}</span>
                                    </a>
                                </td>
                                <td class="px-5 py-3">{{ $trip->departure_time }}</td>
                                <td class="px-5 py-3">{{ number_format($trip->tickets_sold) }} / {{ number_format($trip->capacity) }}</td>
                                <td class="px-5 py-3">{{ number_format($trip->revenue) }} ₫</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">{{ __('Reporting::app.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </article>
        </section>

        @if($filters['type'] === 'payments' && $report['methods']->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <h2 class="px-5 py-4 text-lg font-bold text-slate-900">{{ __('Reporting::app.payment_method') }}</h2>
                <div class="overflow-x-auto"><table class="w-full min-w-[480px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('Reporting::app.payment_method') }}</th><th class="px-5 py-3">{{ __('Reporting::app.payment_count') }}</th><th class="px-5 py-3">{{ __('Reporting::app.amount') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['methods'] as $method)
                            <tr><td class="px-5 py-3 font-semibold">{{ __('Reporting::app.'.$method->method) }}</td><td class="px-5 py-3">{{ number_format($method->payment_count) }}</td><td class="px-5 py-3">{{ number_format($method->amount) }} ₫</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            </section>
        @endif
        @if($filters['type'] === 'tickets')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ __('Reporting::app.order_type') }}</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach($report['order_types'] as $type => $count)
                        <div class="rounded-xl bg-slate-50 p-4"><p class="text-sm text-slate-500">{{ __('Reporting::app.'.$type) }}</p><p class="mt-1 text-xl font-extrabold text-slate-900">{{ number_format($count) }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="report-table-title">
            <h2 id="report-table-title" class="px-5 py-4 text-lg font-bold text-slate-900">{{ __('Reporting::app.table_title') }}</h2>
            <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm">
                <thead class="border-y border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>
                    <th class="px-5 py-3">{{ __('Reporting::app.date') }}</th>
                    <th class="px-5 py-3">{{ $filters['type'] === 'trips' ? __('Reporting::app.completed_trips') : __('Reporting::app.count') }}</th>
                    @if($filters['type'] === 'trips')<th class="px-5 py-3">{{ __('Reporting::app.seats_sold') }}</th>@endif
                    <th class="px-5 py-3">{{ __('Reporting::app.amount') }}</th>
                    @if(in_array($filters['type'], ['payments', 'tickets'], true))<th class="px-5 py-3">{{ __('Reporting::app.refund_amount') }}</th>@endif
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['rows'] as $row)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $row->period }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ number_format($row->count) }}</td>
                            @if($filters['type'] === 'trips')<td class="px-5 py-3">{{ number_format($row->tickets ?? 0) }} / {{ number_format($row->capacity ?? 0) }} · {{ number_format($row->occupancy ?? 0, 1) }}%</td>@endif
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($row->amount) }} ₫</td>
                            @if(in_array($filters['type'], ['payments', 'tickets'], true))<td class="px-5 py-3 font-semibold text-rose-700">{{ number_format($row->refund_amount) }} ₫</td>@endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ in_array($filters['type'], ['payments', 'tickets', 'trips'], true) ? 4 : 3 }}" class="px-5 py-10 text-center text-slate-500">{{ __('Reporting::app.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </section>
    </div>
    @if(session('success') || session('warning') || $errors->any() || $didRunReport)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.FutaNotify) return;
                @if(session('success')) window.FutaNotify.success(@json(session('success'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)); @endif
                @if(session('warning')) window.FutaNotify.warning(@json(session('warning'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)); @endif
                @if($errors->any()) window.FutaNotify.error(@json(implode(' • ', $errors->all()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)); @endif
                @if($didRunReport && $reportFailed) window.FutaNotify.error(@json(__('Reporting::app.report_error')));
                @elseif($didRunReport && $report['rows']->isEmpty()) window.FutaNotify.warning(@json(__('Reporting::app.report_no_data')));
                @elseif($didRunReport) window.FutaNotify.success(@json(__('Reporting::app.report_loaded'))); @endif
            });
        </script>
    @endif
@endsection

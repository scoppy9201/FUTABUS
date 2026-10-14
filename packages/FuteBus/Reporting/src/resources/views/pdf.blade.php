<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 24px 28px; }
        body { color: #172033; font-family: "DejaVu Sans", sans-serif; font-size: 9px; }
        h1, h2, p { margin: 0; }
        h1 { font-size: 20px; margin-bottom: 5px; }
        h2 { font-size: 12px; margin-bottom: 10px; }
        .muted { color: #64748b; }
        .header { border-bottom: 2px solid #f4511e; margin-bottom: 14px; padding-bottom: 12px; }
        .header table, .metrics, .charts, .details { border-collapse: collapse; width: 100%; }
        .header td { vertical-align: top; }
        .header .company { color: #f4511e; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .header .filters { color: #475569; line-height: 1.7; text-align: right; }
        .metrics { margin-bottom: 14px; }
        .metrics td { background: #fff; border: 1px solid #dbe3ec; padding: 10px 14px; width: 25%; }
        .metrics td + td { border-left: 8px solid #fff; }
        .metric-label { color: #64748b; font-size: 8px; font-weight: bold; }
        .metric-value { color: #172033; font-size: 18px; font-weight: bold; margin-top: 5px; }
        .chart-card { border: 1px solid #dbe3ec; padding: 12px; vertical-align: top; }
        .bar-card { margin-bottom: 12px; }
        .charts { margin-bottom: 14px; }
        .charts td { width: 50%; }
        .charts td + td { border-left: 10px solid #fff; }
        .chart-image { display: block; height: auto; width: 100%; }
        .legend { margin: 8px auto 0; }
        .legend td { padding: 3px 5px; vertical-align: middle; }
        .swatch { border-radius: 50%; display: inline-block; height: 8px; width: 8px; }
        .details { margin-top: 3px; }
        .details th { background: #f1f5f9; color: #64748b; font-size: 8px; padding: 8px; text-align: left; text-transform: uppercase; }
        .details td { border-bottom: 1px solid #e2e8f0; padding: 7px 8px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <table><tr>
            <td>
                <p class="company">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
                <h1>{{ __('Reporting::app.title') }}</h1>
                <p class="muted">{{ __('Reporting::app.'.$filters['type']) }}</p>
            </td>
            <td class="filters">
                @php $selectedRoute = $options['routes']->firstWhere('id', $filters['route_id']); @endphp
                <strong>{{ __('Reporting::app.from') }}:</strong> {{ $filters['from'] }}<br>
                <strong>{{ __('Reporting::app.to') }}:</strong> {{ $filters['to'] }}<br>
                <span>{{ __('Reporting::app.date_basis_payment') }}</span>
                @if($filters['route_id'])<br><strong>{{ __('Reporting::app.route') }}:</strong> {{ $selectedRoute ? $selectedRoute->origin_city.' → '.$selectedRoute->destination_city : $filters['route_id'] }}@endif
                @if($filters['trip_id'])<br><strong>{{ __('Reporting::app.trip') }}:</strong> {{ $filters['trip_id'] }}@endif
                @if($filters['bus_id'])<br><strong>{{ __('Reporting::app.bus') }}:</strong> {{ $options['buses']->firstWhere('id', $filters['bus_id'])?->license_plate ?? $filters['bus_id'] }}@endif
                @if($filters['status'] !== 'all')<br><strong>{{ __('Reporting::app.status') }}:</strong> {{ __('Reporting::app.'.$filters['status']) }}@endif
            </td>
        </tr></table>
    </div>

    <table class="metrics"><tr>
        <td><p class="metric-label">{{ __('Reporting::app.total_amount') }}</p><p class="metric-value">{{ number_format($report['kpis']['net_revenue']) }} VND</p></td>
        <td><p class="metric-label">{{ __('Reporting::app.tickets_sold') }}</p><p class="metric-value">{{ number_format($report['kpis']['tickets_sold']) }}</p></td>
        <td><p class="metric-label">{{ __('Reporting::app.occupancy') }}</p><p class="metric-value">{{ number_format($report['kpis']['occupancy'], 1) }}%</p></td>
        <td><p class="metric-label">{{ __('Reporting::app.completed_trips') }}</p><p class="metric-value">{{ number_format($report['kpis']['completed_trips']) }}</p></td>
    </tr></table>

    @if($report['rows']->isEmpty())
        <p class="muted">{{ __('Reporting::app.empty') }}</p>
    @else
        <div class="chart-card bar-card">
            <h2>{{ __('Reporting::app.column_chart') }}</h2>
            @if(in_array($filters['type'], ['revenue', 'payments', 'tickets'], true) && array_sum($report['refund_series']) > 0)
                <p class="muted">
                    <span style="color:#f97316">&#9679;</span>
                    {{ in_array($filters['type'], ['revenue', 'overview'], true)
                        ? __('Reporting::app.total_amount')
                        : ($filters['type'] === 'tickets' ? __('Reporting::app.active') : __('Reporting::app.completed')) }}
                    &nbsp;&nbsp; <span style="color:#dc2626">&#9679;</span> {{ __('Reporting::app.refunded') }}
                </p>
            @endif
            <img class="chart-image" src="data:image/png;base64,{{ $charts['bar'] }}" alt="{{ __('Reporting::app.column_chart') }}">
        </div>

        <table class="charts"><tr>
            <td><div class="chart-card">
                <h2>{{ __('Reporting::app.line_chart') }}</h2>
                <img class="chart-image" src="data:image/png;base64,{{ $charts['line'] }}" alt="{{ __('Reporting::app.line_chart') }}">
            </div></td>
            <td><div class="chart-card">
                <h2>{{ __('Reporting::app.donut_chart') }}</h2>
                <img class="chart-image" src="data:image/png;base64,{{ $charts['donut'] }}" alt="{{ __('Reporting::app.donut_chart') }}">
                @php
                    $donutTotal = array_sum($report['status']);
                    $isRevenue = in_array($filters['type'], ['overview', 'revenue'], true);
                    $donutColors = $isRevenue
                        ? ['#f97316', '#3b82f6', '#16a34a', '#64748b']
                        : ['completed' => '#f97316', 'pending' => '#0f766e', 'failed' => '#2563eb', 'refunded' => '#dc2626', 'active' => '#0f766e', 'used' => '#2563eb', 'cancelled' => '#64748b', 'scheduled' => '#2563eb', 'departed' => '#f97316', 'arrived' => '#16a34a'];
                @endphp
                @if($donutTotal > 0)
                    <table class="legend">
                        @foreach($report['status'] as $key => $value)
                            @php
                                $legendColor = $isRevenue
                                    ? ($donutColors[$loop->index] ?? '#64748b')
                                    : ($donutColors[$key] ?? '#7c3aed');
                                $legendName = $isRevenue ? $key : __('Reporting::app.'.$key);
                            @endphp
                            <tr>
                                <td><span class="swatch" style="background:{{ $legendColor }}"></span></td>
                                <td>{{ $legendName }}</td>
                                <td class="muted">{{ $isRevenue ? number_format($value).' VND' : number_format($value) }} &middot; {{ number_format(((float) $value / $donutTotal) * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <p class="muted">{{ __('Reporting::app.empty') }}</p>
                @endif
            </div></td>
        </tr></table>

        <h2>{{ __('Reporting::app.table_title') }}</h2>
        <table class="details">
            <thead>
                <tr>
                    <th>{{ __('Reporting::app.date') }}</th>
                    <th>{{ __('Reporting::app.count') }}</th>
                    <th>{{ __('Reporting::app.amount') }}</th>
                    @if($filters['type'] === 'trips')<th>{{ __('Reporting::app.seats_sold') }}</th>@endif
                    @if(in_array($filters['type'], ['payments', 'tickets'], true))<th>{{ __('Reporting::app.refund_amount') }}</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        <td>{{ $row->period }}</td>
                        <td>{{ number_format($row->count) }}</td>
                        <td>{{ number_format($row->amount) }} VND</td>
                        @if($filters['type'] === 'trips')
                            <td>{{ number_format($row->tickets ?? 0) }} ({{ number_format($row->occupancy ?? 0, 1) }}%)</td>
                        @endif
                        @if(in_array($filters['type'], ['payments', 'tickets'], true))
                            <td>{{ number_format($row->refund_amount) }} VND</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($filters['type'] === 'payments' && $report['methods']->isNotEmpty())
            <h2 style="margin-top:14px">{{ __('Reporting::app.payment_method') }}</h2>
            <table class="details">
                <thead><tr><th>{{ __('Reporting::app.payment_method') }}</th><th>{{ __('Reporting::app.payment_count') }}</th><th>{{ __('Reporting::app.amount') }}</th></tr></thead>
                <tbody>
                    @foreach($report['methods'] as $method)
                        <tr><td>{{ __('Reporting::app.'.$method->method) }}</td><td>{{ number_format($method->payment_count) }}</td><td>{{ number_format($method->amount) }} VND</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if($filters['type'] === 'tickets')
            <h2 style="margin-top:14px">{{ __('Reporting::app.order_type') }}</h2>
            <table class="details">
                <thead><tr><th>{{ __('Reporting::app.order_type') }}</th><th>{{ __('Reporting::app.count') }}</th></tr></thead>
                <tbody>
                    @foreach($report['order_types'] as $type => $count)
                        <tr><td>{{ __('Reporting::app.'.$type) }}</td><td>{{ number_format($count) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</body>
</html>

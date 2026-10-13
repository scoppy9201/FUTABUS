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
        .metrics td { background: #fff; border: 1px solid #dbe3ec; padding: 10px 14px; width: 33.33%; }
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
        .empty { border: 1px dashed #cbd5e1; color: #64748b; padding: 30px; text-align: center; }
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
                <strong>{{ __('Reporting::app.from') }}:</strong> {{ $filters['from'] }}<br>
                <strong>{{ __('Reporting::app.to') }}:</strong> {{ $filters['to'] }}
                @if($filters['type'] === 'payments')<br><strong>{{ __('Reporting::app.status') }}:</strong> {{ __('Reporting::app.'.$filters['status']) }}@endif
            </td>
        </tr></table>
    </div>

    <table class="metrics"><tr>
        <td><p class="metric-label">{{ __('Reporting::app.total_count') }}</p><p class="metric-value">{{ number_format($report['totals']['count']) }}</p></td>
        <td>
            <p class="metric-label">{{ $filters['type'] === 'tickets' ? __('Reporting::app.tickets') : __('Reporting::app.total_amount') }}</p>
            <p class="metric-value">
                {{ $filters['type'] === 'tickets' ? number_format($report['totals']['count']) : number_format($report['totals']['amount']).' VND' }}
            </p>
        </td>
        @if($filters['type'] === 'payments')<td><p class="metric-label">{{ __('Reporting::app.refund_amount') }}</p><p class="metric-value">{{ number_format($report['totals']['refunded']) }} VND</p></td>@endif
    </tr></table>

    @if($report['rows']->isEmpty())
        <div class="empty">{{ __('Reporting::app.empty') }}</div>
    @else
        <div class="chart-card bar-card">
            <h2>{{ __('Reporting::app.column_chart') }}</h2>
            @if($filters['type'] === 'payments')
                <p class="muted"><span style="color:#f97316">&#9679;</span> {{ __('Reporting::app.completed') }} &nbsp;&nbsp; <span style="color:#dc2626">&#9679;</span> {{ __('Reporting::app.refunded') }}</p>
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
                    $donutColors = $filters['type'] === 'revenue'
                        ? ['#f97316', '#3b82f6', '#16a34a', '#64748b']
                        : ['completed' => '#f97316', 'pending' => '#0f766e', 'failed' => '#2563eb', 'refunded' => '#dc2626', 'active' => '#0f766e', 'used' => '#2563eb'];
                @endphp
                @if($donutTotal > 0)
                    <table class="legend">
                        @foreach($report['status'] as $key => $value)
                            @php
                                $legendColor = $filters['type'] === 'revenue'
                                    ? ($donutColors[$loop->index] ?? '#64748b')
                                    : ($donutColors[$key] ?? '#7c3aed');
                                $legendName = in_array($filters['type'], ['payments', 'tickets'], true) ? __('Reporting::app.'.$key) : $key;
                            @endphp
                            <tr>
                                <td><span class="swatch" style="background:{{ $legendColor }}"></span></td>
                                <td>{{ $legendName }}</td>
                                <td class="muted">{{ $filters['type'] === 'revenue' ? number_format($value / 1_000_000, 2).' '.__('Reporting::app.million') : number_format($value) }} &middot; {{ number_format(((float) $value / $donutTotal) * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty">{{ __('Reporting::app.empty') }}</div>
                @endif
            </div></td>
        </tr></table>

        <h2>{{ __('Reporting::app.table_title') }}</h2>
        <table class="details">
            <thead><tr><th>{{ __('Reporting::app.date') }}</th><th>{{ __('Reporting::app.count') }}</th>@if($filters['type'] !== 'tickets')<th>{{ __('Reporting::app.amount') }}</th>@endif @if($filters['type'] === 'payments')<th>{{ __('Reporting::app.refund_amount') }}</th>@endif</tr></thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr><td>{{ $row->period }}</td><td>{{ number_format($row->count) }}</td>@if($filters['type'] !== 'tickets')<td>{{ number_format($row->amount) }} VND</td>@endif @if($filters['type'] === 'payments')<td>{{ number_format($row->refund_amount) }} VND</td>@endif</tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

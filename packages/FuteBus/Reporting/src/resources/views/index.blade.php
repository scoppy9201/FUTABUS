@extends('Dashboard::layouts.admin')

@section('title', __('Reporting::app.title'))
@section('page_title', __('Reporting::app.title'))

@section('content')
    <div class="space-y-6" data-reporting-chart data-labels="{{ json_encode($report['labels']) }}" data-values="{{ json_encode($report['series']) }}" data-counts="{{ json_encode($report['rows']->pluck('count')->all()) }}" data-refunds="{{ json_encode($report['refund_series']) }}" data-status="{{ json_encode($report['status']) }}" data-chart-label="{{ __('Reporting::app.chart_value') }}">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Reporting::app.title') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Reporting::app.description') }}</p>
            </div>
            <a class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-bold text-orange-800 hover:bg-orange-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                href="{{ route('reporting.export', request()->except('run_report')) }}" data-report-pdf-export
                data-export-success="{{ __('Reporting::app.pdf_exported') }}" data-export-error="{{ __('Reporting::app.pdf_export_failed') }}">
                <x-heroicon-o-arrow-down-tray class="mr-1 inline size-4" />{{ __('Reporting::app.export') }}
            </a>
        </div>

        <form method="get" action="{{ route('reporting.index') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.report_type') }}
                <select name="type" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['revenue', 'payments', 'tickets'] as $type)<option value="{{ $type }}" @selected($filters['type'] === $type)>{{ __('Reporting::app.'.$type) }}</option>@endforeach
                </select>
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.from') }}
                <input type="date" name="from" value="{{ $filters['from'] }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>
            <label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.to') }}
                <input type="date" name="to" value="{{ $filters['to'] }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            </label>
            @if($filters['type'] === 'payments')<label class="text-sm font-semibold text-slate-700">{{ __('Reporting::app.status') }}
                <select name="status" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500">
                    @foreach(['all', 'completed', 'refunded', 'pending', 'failed'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('Reporting::app.'.$status) }}</option>@endforeach
                </select>
            </label>@endif
            <button class="self-end rounded-lg bg-futa-orange px-4 py-2.5 text-sm font-bold text-white hover:bg-orange-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600" type="submit" name="run_report" value="1">{{ __('Reporting::app.apply') }}</button>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="{{ __('Reporting::app.title') }}">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">{{ __('Reporting::app.total_count') }}</p><p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['totals']['count']) }}</p></article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">{{ $filters['type'] === 'tickets' ? __('Reporting::app.tickets') : __('Reporting::app.total_amount') }}</p><p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $filters['type'] === 'tickets' ? number_format($report['totals']['count']) : number_format($report['totals']['amount']).' ₫' }}</p></article>
            @if($filters['type'] === 'payments')<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">{{ __('Reporting::app.refund_amount') }}</p><p class="mt-2 text-3xl font-extrabold text-rose-700">{{ number_format($report['totals']['refunded']) }} <span class="text-base">₫</span></p></article>@endif
        </section>

        <section class="grid min-w-0 gap-4 lg:grid-cols-2" aria-label="{{ __('Reporting::app.chart_title') }}">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2" aria-labelledby="report-column-title">
                <h2 id="report-column-title" class="text-base font-bold text-slate-900">{{ __('Reporting::app.column_chart') }}</h2>
                @if($filters['type'] === 'payments')
                    <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-600">
                        <li class="flex items-center gap-2"><span class="size-2.5 rounded-sm bg-orange-500"></span>{{ __('Reporting::app.completed') }}</li>
                        <li class="flex items-center gap-2"><span class="size-2.5 rounded-sm bg-rose-600"></span>{{ __('Reporting::app.refunded') }}</li>
                    </ul>
                @endif
                @if($report['rows']->isEmpty())<p class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</p>
                @else <div class="mt-4 h-80 w-full"><canvas class="block h-full w-full" data-report-bar role="img" aria-label="{{ __('Reporting::app.column_chart') }}"></canvas></div>@endif
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="report-line-title">
                <h2 id="report-line-title" class="text-base font-bold text-slate-900">{{ __('Reporting::app.line_chart') }}</h2>
                @if($report['rows']->isEmpty())<p class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</p>
                @else <div class="mt-4 h-72 w-full"><canvas class="block h-full w-full" data-report-line role="img" aria-label="{{ __('Reporting::app.line_chart') }}"></canvas></div>@endif
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="report-donut-title">
                <h2 id="report-donut-title" class="text-base font-bold text-slate-900">{{ __('Reporting::app.donut_chart') }}</h2>
                @if (empty($report['status']))
                    <p class="py-12 text-center text-sm text-slate-500">{{ __('Reporting::app.empty') }}</p>
                @else
                    <div class="mt-4 h-56 w-full"><canvas class="block h-full w-full" data-report-donut data-total-label="{{ $filters['type'] === 'revenue' ? __('Reporting::app.donut_total_revenue') : __('Reporting::app.donut_total_count') }}" data-total-unit="{{ __('Reporting::app.million') }}" data-report-type="{{ $filters['type'] }}" role="img" aria-label="{{ __('Reporting::app.donut_chart') }}"></canvas></div>
                    <ul class="mt-4 {{ $filters['type'] === 'revenue' ? 'mx-auto flex max-w-xl flex-col gap-3' : 'flex flex-wrap justify-center gap-x-4 gap-y-2' }} text-xs text-slate-600">
                        @php
                            $donutTotal = array_sum($report['status']);
                        @endphp
                        @foreach($report['status'] as $key => $value)
                            @php
                                $share = $donutTotal > 0 ? ((float) $value / $donutTotal) * 100 : 0;
                                $segmentColor = $filters['type'] === 'revenue'
                                    ? (['#f97316', '#3b82f6', '#16a34a', '#64748b'][$loop->index] ?? '#64748b')
                                    : (['completed' => '#f97316', 'pending' => '#0f766e', 'failed' => '#2563eb', 'refunded' => '#dc2626', 'active' => '#0f766e', 'used' => '#2563eb'][$key] ?? '#7c3aed');
                            @endphp
                            <li class="flex items-start gap-2"><span class="mt-1 size-2.5 shrink-0 rounded-full" style="background-color: {{ $segmentColor }}"></span><span>{{ in_array($filters['type'], ['payments', 'tickets'], true) ? __('Reporting::app.'.$key) : $key }}:
                                @if ($filters['type'] === 'revenue')
                                    {{ number_format($value / 1_000_000, 2) }} {{ __('Reporting::app.million') }}
                                @else
                                    {{ number_format($value) }}
                                @endif
                                · {{ number_format($share, 2) }}%</span></li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="report-table-title">
            <h2 id="report-table-title" class="px-5 py-4 text-lg font-bold text-slate-900">{{ __('Reporting::app.table_title') }}</h2>
            <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm">
                <thead class="border-y border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">{{ __('Reporting::app.date') }}</th><th class="px-5 py-3">{{ __('Reporting::app.count') }}</th>@if($filters['type'] !== 'tickets')<th class="px-5 py-3">{{ __('Reporting::app.amount') }}</th>@endif @if($filters['type'] === 'payments')<th class="px-5 py-3">{{ __('Reporting::app.refund_amount') }}</th>@endif</tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($report['rows'] as $row)<tr><td class="px-5 py-3 font-medium text-slate-800">{{ $row->period }}</td><td class="px-5 py-3 text-slate-600">{{ number_format($row->count) }}</td>@if($filters['type'] !== 'tickets')<td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($row->amount) }} ₫</td>@endif @if($filters['type'] === 'payments')<td class="px-5 py-3 font-semibold text-rose-700">{{ number_format($row->refund_amount) }} ₫</td>@endif</tr>@empty<tr><td colspan="{{ $filters['type'] === 'tickets' ? 2 : ($filters['type'] === 'payments' ? 4 : 3) }}" class="px-5 py-10 text-center text-slate-500">{{ __('Reporting::app.empty') }}</td></tr>@endforelse</tbody>
            </table></div>
        </section>
    </div>
    @if(session('success') || session('warning') || $errors->any() || $didRunReport)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.FutaNotify) return;
                @if(session('success'))
                    window.FutaNotify.success(@json(session('success'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-success' });
                @endif
                @if(session('warning'))
                    window.FutaNotify.warning(@json(session('warning'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-warning' });
                @endif
                @if($errors->any())
                    window.FutaNotify.error(@json(implode(' • ', $errors->all()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'flash-error', duration: 8000 });
                @endif
                @if($didRunReport && $reportFailed)
                    window.FutaNotify.error(@json(__('Reporting::app.report_error'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'report-result', duration: 8000 });
                @elseif($didRunReport && $report['rows']->isEmpty())
                    window.FutaNotify.warning(@json(__('Reporting::app.report_no_data'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'report-result' });
                @elseif($didRunReport)
                    window.FutaNotify.success(@json(__('Reporting::app.report_loaded'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { key: 'report-result' });
                @endif
            });
        </script>
    @endif
@endsection

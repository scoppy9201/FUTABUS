<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use FuteBus\Dashboard\Services\OwnerDashboardService;
use FuteBus\Reporting\Services\ReportChartRenderer;
use FuteBus\Reporting\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportingController extends Controller
{
    public function index(Request $request, OwnerDashboardService $dashboard, ReportService $reports): View
    {
        abort_unless($request->user()?->hasPermissionTo('report.view'), 403);

        $filters = $this->filters($request);
        $company = $dashboard->company($request->user());
        $reportFailed = false;

        try {
            $report = $company ? $reports->build((int) $company->id, $filters) : $reports->empty($filters);
        } catch (\Throwable $exception) {
            report($exception);
            $report = $reports->empty($filters);
            $reportFailed = true;
        }

        return view('Reporting::index', [
            'company'      => $company,
            'filters'      => $filters,
            'report'       => $report,
            'reportFailed' => $reportFailed,
            'didRunReport' => $request->boolean('run_report'),
        ]);
    }

    public function export(Request $request, OwnerDashboardService $dashboard, ReportService $reports, ReportChartRenderer $chartRenderer): Response
    {
        abort_unless($request->user()?->hasPermissionTo('report.view'), 403);

        try {
            $filters = $this->filters($request);
            $company = $dashboard->company($request->user());
            $report = $company ? $reports->build((int) $company->id, $filters) : $reports->empty($filters);
            $charts = $chartRenderer->render($report, $filters);
            $filename = sprintf('futabus-%s-%s-%s.pdf', $filters['type'], $filters['from'], $filters['to']);

            return Pdf::loadView('Reporting::pdf', [
                'company' => $company,
                'filters' => $filters,
                'report'  => $report,
                'charts'  => $charts,
            ])->setPaper('a4', 'landscape')->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ])->download($filename);
        } catch (ValidationException $exception) {
            if (! $request->expectsJson()) {
                throw $exception;
            }

            return response()->json([
                'message' => implode(' ', array_merge(...array_values($exception->errors()))),
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => __('Reporting::app.pdf_export_failed')], 500);
        }
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'type'   => ['sometimes', 'in:revenue,payments,tickets'],
            'from'   => ['sometimes', 'date_format:Y-m-d'],
            'to'     => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', 'in:all,completed,refunded,pending,failed'],
        ]);
        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        if ($from > $to) {
            throw ValidationException::withMessages(['from' => __('Reporting::app.invalid_range')]);
        }

        return [
            'type'   => $validated['type'] ?? 'revenue',
            'from'   => $from,
            'to'     => $to,
            'status' => $validated['status'] ?? 'all',
        ];
    }
}

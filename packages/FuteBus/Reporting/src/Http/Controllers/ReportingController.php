<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use FuteBus\Dashboard\Services\OwnerDashboardService;
use FuteBus\Reporting\Http\Requests\ReportFilterRequest;
use FuteBus\Reporting\Services\ReportChartRenderer;
use FuteBus\Reporting\Services\ReportService;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportingController extends Controller
{
    public function index(ReportFilterRequest $request, OwnerDashboardService $dashboard, ReportService $reports): View
    {
        abort_unless($request->user()?->hasPermissionTo('report.view'), 403);

        $filters = $request->filters();
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

    public function export(ReportFilterRequest $request, OwnerDashboardService $dashboard, ReportService $reports, ReportChartRenderer $chartRenderer): Response
    {
        abort_unless($request->user()?->hasPermissionTo('report.view'), 403);

        try {
            $filters = $request->filters();
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
}

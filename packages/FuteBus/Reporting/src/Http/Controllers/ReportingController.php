<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use FuteBus\Reporting\Http\Requests\ReportFilterRequest;
use FuteBus\Reporting\Services\ReportChartRenderer;
use FuteBus\Reporting\Services\ReportService;
use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportingController extends Controller
{
    public function index(ReportFilterRequest $request, AccessControlService $access, ReportService $reports): View
    {
        $company = $access->companyFor($request->user());
        $filters = $request->filters();
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
            'options'      => $company ? $reports->options((int) $company->id) : ['routes' => collect(), 'trips' => collect(), 'buses' => collect()],
            'reportFailed' => $reportFailed,
            'didRunReport' => $request->boolean('run_report'),
        ]);
    }

    public function data(ReportFilterRequest $request, AccessControlService $access, ReportService $reports): Response
    {
        $company = $access->companyFor($request->user());
        $filters = $request->filters();
        $report = $company ? $reports->build((int) $company->id, $filters) : $reports->empty($filters);

        return response()->json(['filters' => $filters, 'report' => $report]);
    }

    public function export(ReportFilterRequest $request, AccessControlService $access, ReportService $reports, ReportChartRenderer $chartRenderer): Response
    {

        try {
            $company = $access->companyFor($request->user());
            $filters = $request->filters();
            $report = $company ? $reports->build((int) $company->id, $filters) : $reports->empty($filters);
            $options = $company ? $reports->options((int) $company->id) : ['routes' => collect(), 'trips' => collect(), 'buses' => collect()];
            $filename = sprintf('futabus-%s-%s-%s', $filters['type'], $filters['from'], $filters['to']);

            $charts = $chartRenderer->render($report, $filters);

            return Pdf::loadView('Reporting::pdf', [
                'company' => $company, 'filters' => $filters, 'report' => $report, 'charts' => $charts, 'options' => $options,
            ])->setPaper('a4', 'landscape')->setOptions([
                'defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false, 'isHtml5ParserEnabled' => true,
            ])->download($filename.'.pdf');
        } catch (ValidationException $exception) {
            if (! $request->expectsJson()) {
                throw $exception;
            }

            return response()->json(['message' => implode(' ', array_merge(...array_values($exception->errors())))], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => __('Reporting::app.export_failed')], 500);
        }
    }
}

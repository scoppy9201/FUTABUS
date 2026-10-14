<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use FuteBus\Reporting\Services\ReportChartRenderer;
use FuteBus\Reporting\Services\ReportService;
use FuteBus\RolePermission\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportingController extends Controller
{
    public function index(Request $request, AccessControlService $access, ReportService $reports): View
    {
        $this->authorizeReport($request);
        $company = $access->companyFor($request->user());
        $filters = $this->filters($request, $company?->id);
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

    public function data(Request $request, AccessControlService $access, ReportService $reports): Response
    {
        $this->authorizeReport($request);
        $company = $access->companyFor($request->user());
        $filters = $this->filters($request, $company?->id);
        $report = $company ? $reports->build((int) $company->id, $filters) : $reports->empty($filters);

        return response()->json(['filters' => $filters, 'report' => $report]);
    }

    public function export(Request $request, AccessControlService $access, ReportService $reports, ReportChartRenderer $chartRenderer): Response
    {
        $this->authorizeReport($request);

        try {
            $company = $access->companyFor($request->user());
            $filters = $this->filters($request, $company?->id);
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

    private function authorizeReport(Request $request): void
    {
        abort_unless($request->user()?->hasPermissionTo('report.view'), 403);
    }

    private function filters(Request $request, ?int $companyId): array
    {
        $rules = [
            'type'        => ['sometimes', Rule::in(['overview', 'revenue', 'payments', 'tickets', 'trips'])],
            'period'      => ['sometimes', Rule::in(['today', '7days', '30days', 'custom'])],
            'from'        => ['sometimes', 'date_format:Y-m-d'], 'to' => ['sometimes', 'date_format:Y-m-d'],
            'format'      => ['sometimes', Rule::in(['pdf'])],
            'status'      => ['sometimes', Rule::in(['all', 'completed', 'refunded', 'pending', 'failed', 'active', 'used', 'cancelled', 'scheduled', 'departed', 'arrived'])],
            'trip_status' => ['sometimes', Rule::in(['all', 'scheduled', 'departed', 'arrived', 'cancelled'])],
            'route_id'    => ['nullable', 'integer', $companyId ? Rule::exists('routes', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
            'trip_id'     => ['nullable', 'integer', $companyId ? Rule::exists('trips', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
            'bus_id'      => ['nullable', 'integer', $companyId ? Rule::exists('buses', 'id')->where('bus_company_id', $companyId) : 'prohibited'],
        ];
        $validated = $request->validate($rules);
        $period = $validated['period'] ?? '7days';
        $today = now('Asia/Ho_Chi_Minh')->toDateString();
        $defaultFrom = match ($period) {
            'today'  => $today,
            '30days' => now('Asia/Ho_Chi_Minh')->subDays(29)->toDateString(),
            'custom' => now('Asia/Ho_Chi_Minh')->startOfMonth()->toDateString(),
            default  => now('Asia/Ho_Chi_Minh')->subDays(6)->toDateString(),
        };
        $from = $validated['from'] ?? $defaultFrom;
        $to = $validated['to'] ?? $today;
        if ($from > $to) {
            throw ValidationException::withMessages(['from' => __('Reporting::app.invalid_range')]);
        }

        return [
            'type'        => $validated['type'] ?? 'overview', 'period' => $period, 'from' => $from, 'to' => $to,
            'format'      => $validated['format'] ?? 'pdf', 'status' => $validated['status'] ?? 'all',
            'trip_status' => $validated['trip_status'] ?? 'all',
            'route_id'    => $validated['route_id'] ?? null, 'trip_id' => $validated['trip_id'] ?? null,
            'bus_id'      => $validated['bus_id'] ?? null,
        ];
    }
}

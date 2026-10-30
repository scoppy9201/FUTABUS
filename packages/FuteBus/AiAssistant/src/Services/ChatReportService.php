<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use App\Models\User;
use FuteBus\Reporting\Services\ReportService;
use FuteBus\RolePermission\Services\AccessControlService;

class ChatReportService
{
    public function reply(?User $user): array
    {
        if (! $user?->hasPermissionTo('report.view')) {
            return ['message' => __('AiAssistant::app.report_permission_required')];
        }

        $company = app(AccessControlService::class)->companyFor($user);
        if ($company === null) {
            return ['message' => __('AiAssistant::app.report_unavailable')];
        }

        $filters = [
            'type'        => 'overview',
            'period'      => '7days',
            'from'        => now('Asia/Ho_Chi_Minh')->subDays(6)->toDateString(),
            'to'          => now('Asia/Ho_Chi_Minh')->toDateString(),
            'status'      => 'all',
            'trip_status' => 'all',
            'route_id'    => null,
            'trip_id'     => null,
            'bus_id'      => null,
        ];

        try {
            $report = app(ReportService::class)->build((int) $company->id, $filters);
        } catch (\Throwable $e) {
            report($e);

            return ['message' => __('AiAssistant::app.report_failed')];
        }

        return [
            'message' => __('AiAssistant::app.report_summary'),
            'metrics' => [
                ['label' => __('AiAssistant::app.revenue'),      'value' => (float) $report['kpis']['net_revenue'],  'format' => 'currency'],
                ['label' => __('AiAssistant::app.tickets_sold'), 'value' => (int) $report['kpis']['tickets_sold'], 'format' => 'number'],
                ['label' => __('AiAssistant::app.occupancy'),    'value' => (float) $report['kpis']['occupancy'],    'format' => 'percent'],
            ],
            'pending_action' => [
                'label'       => __('AiAssistant::app.open_report'),
                'url'         => route('reporting.index'),
                'description' => __('AiAssistant::app.open_report_description'),
            ],
        ];
    }
}

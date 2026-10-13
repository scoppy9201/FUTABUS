<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Services;

use Illuminate\Support\Facades\DB;
use stdClass;

class ReportService
{
    public function empty(array $filters): array
    {
        return ['totals' => ['count' => 0, 'amount' => 0, 'refunded' => 0], 'rows' => collect(), 'labels' => [], 'series' => [], 'refund_series' => [], 'status' => []];
    }

    public function build(int $companyId, array $filters): array
    {
        $type = $filters['type'];
        $query = match ($type) {
            'revenue', 'payments' => DB::table('payments')
                ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->whereDate($type === 'payments' ? 'payments.created_at' : 'payments.paid_at', '>=', $filters['from'])
                ->whereDate($type === 'payments' ? 'payments.created_at' : 'payments.paid_at', '<=', $filters['to'])
                ->when($type === 'revenue', fn ($q) => $q->where('payments.status', 'completed'))
                ->when($type === 'payments' && $filters['status'] !== 'all', fn ($q) => $q->where('payments.status', $filters['status'])),
            'tickets' => DB::table('tickets')
                ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
                ->join('trips', 'trips.id', '=', 'tickets.trip_id')
                ->join('payments', 'payments.booking_id', '=', 'bookings.id')
                ->where('trips.bus_company_id', $companyId)
                ->where('payments.status', 'completed')
                ->whereNotNull('payments.paid_at')
                ->whereDate('payments.paid_at', '>=', $filters['from'])
                ->whereDate('payments.paid_at', '<=', $filters['to'])
                ->whereNotIn('tickets.status', ['cancelled', 'refunded']),
        };

        $dateColumn = $type === 'payments' ? 'payments.created_at' : 'payments.paid_at';
        $grouped = (clone $query)
            ->selectRaw("DATE({$dateColumn}) AS period")
            ->when($type === 'tickets', fn ($q) => $q->selectRaw('COUNT(DISTINCT tickets.id) AS item_count')->selectRaw('NULL AS item_amount')->selectRaw('NULL AS refund_amount'),
                fn ($q) => $q->selectRaw('COUNT(DISTINCT payments.id) AS item_count')
                    ->selectRaw("SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END) AS item_amount")
                    ->selectRaw("SUM(CASE WHEN payments.status = 'refunded' THEN payments.amount ELSE 0 END) AS refund_amount"))
            ->groupByRaw("DATE({$dateColumn})")
            ->orderBy('period')->get();

        $rows = $grouped->map(fn (stdClass $row): stdClass => (object) [
            'period'        => $row->period,
            'label'         => $row->period,
            'count'         => (int) $row->item_count,
            'amount'        => $row->item_amount === null ? null : (float) $row->item_amount,
            'refund_amount' => $row->refund_amount === null ? null : (float) $row->refund_amount,
        ]);
        $totals = [
            'count'    => (int) $rows->sum('count'),
            'amount'   => (float) $rows->sum('amount'),
            'refunded' => $type === 'payments' ? (float) $rows->sum('refund_amount') : 0.0,
        ];
        $status = match ($type) {
            'payments' => DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->whereDate('payments.created_at', '>=', $filters['from'])
                ->whereDate('payments.created_at', '<=', $filters['to'])
                ->select('payments.status')->selectRaw('COUNT(DISTINCT payments.id) AS item_count')
                ->groupBy('payments.status')->pluck('item_count', 'status')->all(),
            'tickets' => (clone $query)->select('tickets.status')->selectRaw('COUNT(DISTINCT tickets.id) AS item_count')
                ->groupBy('tickets.status')->pluck('item_count', 'status')->all(),
            default => $this->revenueByRoute($companyId, $filters),
        };

        return [
            'totals'        => $totals,
            'rows'          => $rows,
            'labels'        => $rows->pluck('label')->all(),
            'series'        => $rows->pluck($type === 'tickets' ? 'count' : 'amount')->all(),
            'refund_series' => $rows->pluck('refund_amount')->all(),
            'status'        => $status,
        ];
    }

    private function revenueByRoute(int $companyId, array $filters): array
    {
        $routes = DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->where('trips.bus_company_id', $companyId)
            ->where('payments.status', 'completed')
            ->whereDate('payments.paid_at', '>=', $filters['from'])
            ->whereDate('payments.paid_at', '<=', $filters['to'])
            ->select('routes.id', 'routes.code', 'routes.origin_city', 'routes.destination_city')
            ->selectRaw('SUM(payments.amount) AS revenue_amount')
            ->groupBy('routes.id', 'routes.code', 'routes.origin_city', 'routes.destination_city')
            ->get()
            ->sortByDesc('revenue_amount')
            ->values();

        $segments = $routes->take(3)->mapWithKeys(fn (stdClass $route): array => [
            $route->origin_city.' → '.$route->destination_city => (float) $route->revenue_amount,
        ]);
        $otherRevenue = (float) $routes->slice(3)->sum('revenue_amount');

        if ($otherRevenue > 0) {
            $segments[__('Reporting::app.other_routes')] = $otherRevenue;
        }

        return $segments->all();
    }
}

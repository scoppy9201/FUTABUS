<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class ReportService
{
    public function empty(array $filters): array
    {
        return [
            'totals' => ['count' => 0, 'amount' => 0.0, 'gross' => 0.0, 'refunded' => 0.0],
            'kpis'   => ['net_revenue' => 0.0, 'gross_revenue' => 0.0, 'tickets_sold' => 0,
                'occupancy'            => 0.0, 'completed_trips' => 0, 'average_fare' => 0.0,
                'cancellation_rate'    => 0.0, 'payment_success_rate' => 0.0],
            'comparison' => ['revenue' => 0.0, 'tickets' => 0, 'trips' => 0],
            'rows'       => collect(), 'labels' => [], 'series' => [], 'refund_series' => [], 'status' => [],
            'top_routes' => collect(), 'top_trips' => collect(), 'methods' => collect(), 'order_types' => [],
        ];
    }

    public function options(int $companyId): array
    {
        return [
            'routes' => DB::table('routes')->where('bus_company_id', $companyId)
                ->orderBy('origin_city')->orderBy('destination_city')
                ->get(['id', 'code', 'origin_city', 'destination_city']),
            'buses' => DB::table('buses')->where('bus_company_id', $companyId)
                ->orderBy('license_plate')->get(['id', 'license_plate']),
            'trips' => DB::table('trips')->join('routes', 'routes.id', '=', 'trips.route_id')
                ->where('trips.bus_company_id', $companyId)->orderByDesc('trips.departure_time')
                ->limit(500)->get(['trips.id', 'trips.departure_time', 'routes.origin_city', 'routes.destination_city']),
        ];
    }

    public function build(int $companyId, array $filters): array
    {
        $empty = $this->empty($filters);
        $type = $filters['type'];
        $tickets = $this->tickets($companyId, $filters, false);
        $trips = $this->trips($companyId, $filters, false);
        $revenueLegs = DB::query()->fromSub($this->revenueLegs($companyId, $filters), 'revenue_legs');
        $gross = (float) (clone $revenueLegs)->sum('gross_amount');
        $refunded = (float) (clone $revenueLegs)->sum('refund_amount');
        $net = $gross - $refunded;
        $soldTickets = (int) (clone $tickets)->whereIn('tickets.status', ['active', 'used'])->count('tickets.id');
        $ticketCount = (int) (clone $tickets)->count('tickets.id');
        $cancelledTickets = (int) (clone $tickets)->where('tickets.status', 'cancelled')->count('tickets.id');
        $completedTrips = (int) (clone $trips)->where('trips.status', 'arrived')->count('trips.id');
        $capacity = (int) (clone $trips)->selectRaw(<<<'SQL'
            SUM(CASE
                WHEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1) > 0
                THEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1)
                ELSE buses.capacity
            END) AS capacity
            SQL)->value('capacity');
        $periodTickets = (int) (clone $this->tripTickets($companyId, $filters))->count('tickets.id');
        $occupancy = $capacity > 0 ? min(100, ($periodTickets / $capacity) * 100) : 0.0;

        $kpis = [
            'net_revenue'          => $net,
            'gross_revenue'        => $gross,
            'tickets_sold'         => $soldTickets,
            'occupancy'            => $occupancy,
            'completed_trips'      => $completedTrips,
            'average_fare'         => $soldTickets > 0 ? $net / $soldTickets : 0.0,
            'cancellation_rate'    => $ticketCount > 0 ? ($cancelledTickets / $ticketCount) * 100 : 0.0,
            'payment_success_rate' => $this->paymentSuccessRate($companyId, $filters),
        ];
        $routePerformance = $this->routeRevenueRows($companyId, $filters);

        $report = match ($type) {
            'payments' => $this->paymentReport($companyId, $filters),
            'tickets'  => $this->ticketReport($companyId, $filters),
            'trips'    => $this->tripReport($companyId, $filters),
            default    => $this->revenueReport($companyId, $filters, $routePerformance),
        };

        return array_merge($empty, $report, [
            'kpis'       => $kpis,
            'comparison' => $this->comparison($companyId, $filters),
            'top_routes' => $routePerformance->take(5)->values(),
            'top_trips'  => $this->topTrips($companyId, $filters),
        ]);
    }

    private function paymentReport(int $companyId, array $filters): array
    {
        $query = $this->payments($companyId, $filters);
        $rows = (clone $query)->selectRaw('DATE(payments.created_at) AS period')
            ->selectRaw('COUNT(DISTINCT payments.id) AS item_count')
            ->selectRaw("SUM(CASE WHEN payments.status IN ('completed', 'refunded') THEN payments.amount ELSE 0 END) AS gross_amount")
            ->selectRaw("SUM(CASE WHEN payments.status = 'refunded' THEN payments.amount ELSE 0 END) AS refund_amount")
            ->groupByRaw('DATE(payments.created_at)')->orderBy('period')->get()
            ->map(fn (stdClass $row): stdClass => (object) [
                'period'        => $row->period, 'label' => $row->period, 'count' => (int) $row->item_count,
                'amount'        => (float) $row->gross_amount,
                'refund_amount' => (float) $row->refund_amount,
            ]);
        $status = (clone $query)->select('payments.status')
            ->selectRaw('COUNT(DISTINCT payments.id) AS item_count')->groupBy('payments.status')
            ->pluck('item_count', 'status')->all();

        return array_merge(
            $this->reportResult($rows, (int) $rows->sum('count'), (float) $rows->sum('amount') - (float) $rows->sum('refund_amount'),
                (float) $rows->sum('refund_amount'), $status),
            ['methods' => (clone $query)->select('payments.method')
                ->selectRaw('COUNT(DISTINCT payments.id) AS payment_count')
                ->selectRaw("SUM(CASE WHEN payments.status IN ('completed', 'refunded') THEN payments.amount ELSE 0 END) AS amount")
                ->groupBy('payments.method')->orderByDesc('payment_count')->get()]
        );
    }

    private function revenueReport(int $companyId, array $filters, $routePerformance): array
    {
        $rows = DB::query()->fromSub($this->revenueLegs($companyId, $filters), 'revenue_legs')
            ->select('period')->selectRaw('COUNT(DISTINCT payment_id) AS item_count')
            ->selectRaw('SUM(gross_amount) AS gross_amount')->selectRaw('SUM(refund_amount) AS refund_amount')
            ->groupBy('period')->orderBy('period')->get()
            ->map(fn (stdClass $row): stdClass => (object) [
                'period'        => $row->period, 'label' => $row->period, 'count' => (int) $row->item_count,
                'amount'        => (float) $row->gross_amount - (float) $row->refund_amount,
                'refund_amount' => (float) $row->refund_amount,
            ]);

        return $this->reportResult($rows, (int) $rows->sum('count'), (float) $rows->sum('amount'),
            (float) $rows->sum('refund_amount'), $this->revenueByRoute($routePerformance));
    }

    private function ticketReport(int $companyId, array $filters): array
    {
        $query = $this->tickets($companyId, $filters, true, true);
        $rows = (clone $query)->selectRaw('DATE(tickets.created_at) AS period')
            ->selectRaw('COUNT(tickets.id) AS item_count')
            ->selectRaw("SUM(CASE WHEN tickets.status IN ('active', 'used') THEN tickets.price ELSE 0 END) AS item_amount")
            ->selectRaw("SUM(CASE WHEN tickets.status = 'refunded' THEN tickets.price ELSE 0 END) AS refund_amount")
            ->groupByRaw('DATE(tickets.created_at)')->orderBy('period')->get()
            ->map(fn (stdClass $row): stdClass => (object) [
                'period' => $row->period, 'label' => $row->period, 'count' => (int) $row->item_count,
                'amount' => (float) $row->item_amount, 'refund_amount' => (float) $row->refund_amount,
            ]);
        $status = (clone $query)->select('tickets.status')->selectRaw('COUNT(tickets.id) AS item_count')
            ->groupBy('tickets.status')->pluck('item_count', 'status')->all();

        return array_merge(
            $this->reportResult($rows, (int) $rows->sum('count'), (float) $rows->sum('amount') - (float) $rows->sum('refund_amount'),
                (float) $rows->sum('refund_amount'), $status),
            ['order_types' => $this->orderTypes($companyId, $filters)]
        );
    }

    private function tripReport(int $companyId, array $filters): array
    {
        $query = $this->trips($companyId, $filters);
        $rows = (clone $query)->selectRaw('DATE(trips.departure_time) AS period')
            ->selectRaw('COUNT(DISTINCT trips.id) AS item_count')
            ->selectRaw(<<<'SQL'
                SUM((SELECT COUNT(*)
                    FROM tickets JOIN bookings ON bookings.id = tickets.booking_id
                    WHERE tickets.trip_id = trips.id AND tickets.status IN ('active', 'used')
                    AND (EXISTS (SELECT 1 FROM payments WHERE payments.booking_id = bookings.id AND payments.status = 'completed')
                        OR EXISTS (SELECT 1 FROM sepay_payment_intents JOIN payments ON payments.payment_code = sepay_payment_intents.code
                            WHERE sepay_payment_intents.secondary_booking_id = bookings.id AND payments.status = 'completed'))
                )) AS seats_sold
                SQL)
            ->selectRaw(<<<'SQL'
                SUM((SELECT COALESCE(SUM(tickets.price), 0)
                    FROM tickets JOIN bookings ON bookings.id = tickets.booking_id
                    WHERE tickets.trip_id = trips.id AND tickets.status IN ('active', 'used')
                    AND (EXISTS (SELECT 1 FROM payments WHERE payments.booking_id = bookings.id AND payments.status = 'completed')
                        OR EXISTS (SELECT 1 FROM sepay_payment_intents JOIN payments ON payments.payment_code = sepay_payment_intents.code
                            WHERE sepay_payment_intents.secondary_booking_id = bookings.id AND payments.status = 'completed'))
                )) AS item_amount
                SQL)
            ->selectRaw(<<<'SQL'
                SUM(CASE
                    WHEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1) > 0
                    THEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1)
                    ELSE buses.capacity
                END) AS capacity
                SQL)
            ->groupByRaw('DATE(trips.departure_time)')->orderBy('period')->get()
            ->map(fn (stdClass $row): stdClass => (object) [
                'period'        => $row->period, 'label' => $row->period, 'count' => (int) $row->item_count,
                'amount'        => (float) $row->item_amount, 'tickets' => (int) $row->seats_sold, 'capacity' => (int) $row->capacity,
                'occupancy'     => (int) $row->capacity > 0 ? ((int) $row->seats_sold / (int) $row->capacity) * 100 : 0.0,
                'refund_amount' => 0.0,
            ]);
        $status = (clone $query)->select('trips.status')->selectRaw('COUNT(DISTINCT trips.id) AS item_count')
            ->groupBy('trips.status')->pluck('item_count', 'status')->all();

        return $this->reportResult($rows, (int) $rows->sum('count'), (float) $rows->sum('amount'), 0.0, $status);
    }

    private function reportResult($rows, int $count, float $amount, float $refunded, array $status): array
    {
        return [
            'totals' => ['count' => $count, 'amount' => $amount, 'gross' => $amount + $refunded, 'refunded' => $refunded],
            'rows'   => $rows, 'labels' => $rows->pluck('label')->all(),
            'series' => $rows->pluck('amount')->all(), 'refund_series' => $rows->pluck('refund_amount')->all(),
            'status' => $status,
        ];
    }

    private function revenueLegs(int $companyId, array $filters): Builder
    {
        $primary = DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->leftJoin('sepay_payment_intents as intents', 'intents.code', '=', 'payments.payment_code')
            ->leftJoin('bookings as secondary_bookings', 'secondary_bookings.id', '=', 'intents.secondary_booking_id')
            ->leftJoin('trips as secondary_trips', function ($join) use ($companyId): void {
                $join->on('secondary_trips.id', '=', 'secondary_bookings.trip_id')
                    ->where('secondary_trips.bus_company_id', '=', $companyId);
            })
            ->leftJoin('routes as primary_routes', 'primary_routes.id', '=', 'trips.route_id')
            ->leftJoin('routes as secondary_routes', 'secondary_routes.id', '=', 'secondary_trips.route_id')
            ->where('trips.bus_company_id', $companyId)
            ->whereIn('payments.status', ['completed', 'refunded'])
            ->whereNotNull('payments.paid_at')
            ->whereDate('payments.paid_at', '>=', $filters['from'])->whereDate('payments.paid_at', '<=', $filters['to']);
        $this->applyTripFilters($primary, 'trips', $filters);
        $primary->selectRaw('payments.id AS payment_id, DATE(payments.paid_at) AS period, trips.route_id AS route_id, primary_routes.code AS route_code, primary_routes.origin_city AS origin_city, primary_routes.destination_city AS destination_city')
            ->selectRaw(<<<'SQL'
                CASE WHEN payments.status IN ('completed', 'refunded') THEN
                    CASE WHEN secondary_trips.id IS NULL OR bookings.total_amount + secondary_bookings.total_amount <= 0
                        THEN payments.amount
                        ELSE payments.amount * bookings.total_amount / (bookings.total_amount + secondary_bookings.total_amount)
                    END
                ELSE 0 END AS gross_amount
                SQL)
            ->selectRaw(<<<'SQL'
                CASE WHEN payments.status = 'refunded' THEN
                    CASE WHEN secondary_trips.id IS NULL OR bookings.total_amount + secondary_bookings.total_amount <= 0
                        THEN payments.amount
                        ELSE payments.amount * bookings.total_amount / (bookings.total_amount + secondary_bookings.total_amount)
                    END
                ELSE 0 END AS refund_amount
                SQL);

        $secondary = DB::table('payments')
            ->join('sepay_payment_intents as intents', 'intents.code', '=', 'payments.payment_code')
            ->join('bookings as primary_bookings', 'primary_bookings.id', '=', 'payments.booking_id')
            ->join('trips as primary_trips', 'primary_trips.id', '=', 'primary_bookings.trip_id')
            ->join('bookings as secondary_bookings', 'secondary_bookings.id', '=', 'intents.secondary_booking_id')
            ->join('trips as trips', 'trips.id', '=', 'secondary_bookings.trip_id')
            ->join('routes as primary_routes', 'primary_routes.id', '=', 'primary_trips.route_id')
            ->join('routes as secondary_routes', 'secondary_routes.id', '=', 'trips.route_id')
            ->where('primary_trips.bus_company_id', $companyId)->where('trips.bus_company_id', $companyId)
            ->whereIn('payments.status', ['completed', 'refunded'])
            ->whereNotNull('payments.paid_at')
            ->whereDate('payments.paid_at', '>=', $filters['from'])->whereDate('payments.paid_at', '<=', $filters['to']);
        $this->applyTripFilters($secondary, 'trips', $filters);
        $secondary->selectRaw('payments.id AS payment_id, DATE(payments.paid_at) AS period, trips.route_id AS route_id, secondary_routes.code AS route_code, secondary_routes.origin_city AS origin_city, secondary_routes.destination_city AS destination_city')
            ->selectRaw(<<<'SQL'
                CASE WHEN payments.status IN ('completed', 'refunded')
                    AND primary_bookings.total_amount + secondary_bookings.total_amount > 0
                THEN payments.amount * secondary_bookings.total_amount
                    / (primary_bookings.total_amount + secondary_bookings.total_amount)
                ELSE 0 END AS gross_amount
                SQL)
            ->selectRaw(<<<'SQL'
                CASE WHEN payments.status = 'refunded'
                    AND primary_bookings.total_amount + secondary_bookings.total_amount > 0
                THEN payments.amount * secondary_bookings.total_amount
                    / (primary_bookings.total_amount + secondary_bookings.total_amount)
                ELSE 0 END AS refund_amount
                SQL);

        return $primary->unionAll($secondary);
    }

    private function applyTripFilters(Builder $query, string $alias, array $filters): void
    {
        $query->when($filters['route_id'], fn (Builder $builder) => $builder->where($alias.'.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $builder) => $builder->where($alias.'.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $builder) => $builder->where($alias.'.bus_id', $filters['bus_id']))
            ->when($filters['trip_status'] !== 'all', fn (Builder $builder) => $builder->where($alias.'.status', $filters['trip_status']));
    }

    private function payments(int $companyId, array $filters, bool $applyReportStatus = true): Builder
    {
        return DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->where('trips.bus_company_id', $companyId)
            ->whereDate('payments.'.($filters['type'] === 'payments' ? 'created_at' : 'paid_at'), '>=', $filters['from'])
            ->whereDate('payments.'.($filters['type'] === 'payments' ? 'created_at' : 'paid_at'), '<=', $filters['to'])
            ->when($applyReportStatus && $filters['status'] !== 'all' && $filters['type'] === 'payments', fn (Builder $query) => $query->where('payments.status', $filters['status']))
            ->when($filters['route_id'], fn (Builder $query) => $query->where('trips.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $query) => $query->where('trips.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $query) => $query->where('trips.bus_id', $filters['bus_id']))
            ->when($filters['trip_status'] !== 'all', fn (Builder $query) => $query->where('trips.status', $filters['trip_status']));
    }

    private function tickets(int $companyId, array $filters, bool $applyReportStatus = true, bool $includeRefunded = false): Builder
    {
        $paymentStatuses = $includeRefunded ? ['completed', 'refunded'] : ['completed'];

        return DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')
            ->where('trips.bus_company_id', $companyId)
            ->whereDate('tickets.created_at', '>=', $filters['from'])->whereDate('tickets.created_at', '<=', $filters['to'])
            ->where(function (Builder $query) use ($paymentStatuses): void {
                $query->whereExists(fn (Builder $payment) => $payment->selectRaw('1')->from('payments')
                    ->whereColumn('payments.booking_id', 'bookings.id')->whereIn('payments.status', $paymentStatuses))
                    ->orWhereExists(fn (Builder $payment) => $payment->selectRaw('1')->from('sepay_payment_intents')
                        ->join('payments', 'payments.payment_code', '=', 'sepay_payment_intents.code')
                        ->whereColumn('sepay_payment_intents.secondary_booking_id', 'bookings.id')
                        ->whereIn('payments.status', $paymentStatuses));
            })
            ->when($applyReportStatus && $filters['status'] !== 'all' && $filters['type'] === 'tickets', fn (Builder $query) => $query->where('tickets.status', $filters['status']))
            ->when($filters['route_id'], fn (Builder $query) => $query->where('trips.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $query) => $query->where('trips.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $query) => $query->where('trips.bus_id', $filters['bus_id']))
            ->when($filters['trip_status'] !== 'all', fn (Builder $query) => $query->where('trips.status', $filters['trip_status']));
    }

    private function trips(int $companyId, array $filters, bool $applyReportStatus = true): Builder
    {
        return DB::table('trips')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')->where('trips.bus_company_id', $companyId)
            ->whereDate('trips.departure_time', '>=', $filters['from'])->whereDate('trips.departure_time', '<=', $filters['to'])
            ->when($applyReportStatus && $filters['status'] !== 'all' && $filters['type'] === 'trips', fn (Builder $query) => $query->where('trips.status', $filters['status']))
            ->when($filters['route_id'], fn (Builder $query) => $query->where('trips.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $query) => $query->where('trips.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $query) => $query->where('trips.bus_id', $filters['bus_id']))
            ->when($filters['trip_status'] !== 'all', fn (Builder $query) => $query->where('trips.status', $filters['trip_status']));
    }

    private function comparison(int $companyId, array $filters): array
    {
        $days = Carbon::parse($filters['from'])->diffInDays(Carbon::parse($filters['to'])) + 1;
        $previous = $filters;
        $previous['type'] = 'revenue';
        $previous['to'] = Carbon::parse($filters['from'])->subDay()->toDateString();
        $previous['from'] = Carbon::parse($previous['to'])->subDays($days - 1)->toDateString();
        $previousPayments = DB::query()->fromSub($this->revenueLegs($companyId, $previous), 'previous_revenue_legs');
        $previousTickets = $this->tickets($companyId, $previous)->whereIn('tickets.status', ['active', 'used']);
        $previousTrips = $this->trips($companyId, $previous)->where('trips.status', 'arrived');

        return [
            'revenue' => (float) (clone $previousPayments)->sum('gross_amount')
                - (float) (clone $previousPayments)->sum('refund_amount'),
            'tickets' => (int) $previousTickets->count('tickets.id'),
            'trips'   => (int) $previousTrips->count('trips.id'),
        ];
    }

    private function paymentSuccessRate(int $companyId, array $filters): float
    {
        $payments = DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')->where('trips.bus_company_id', $companyId)
            ->whereBetween('payments.created_at', [$filters['from'].' 00:00:00', $filters['to'].' 23:59:59'])
            ->when($filters['route_id'], fn (Builder $query) => $query->where('trips.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $query) => $query->where('trips.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $query) => $query->where('trips.bus_id', $filters['bus_id']))
            ->whereIn('payments.status', ['completed', 'failed', 'refunded']);
        $total = (int) (clone $payments)->count('payments.id');

        return $total > 0 ? ((int) (clone $payments)->whereIn('payments.status', ['completed', 'refunded'])->count('payments.id') / $total) * 100 : 0.0;
    }

    private function orderTypes(int $companyId, array $filters): array
    {
        $query = DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->leftJoin('sepay_payment_intents as intents', 'intents.code', '=', 'payments.payment_code')
            ->leftJoin('bookings as return_bookings', 'return_bookings.id', '=', 'intents.secondary_booking_id')
            ->leftJoin('trips as return_trips', function ($join) use ($companyId): void {
                $join->on('return_trips.id', '=', 'return_bookings.trip_id')
                    ->where('return_trips.bus_company_id', '=', $companyId);
            })
            ->where('trips.bus_company_id', $companyId)->whereIn('payments.status', ['completed', 'refunded'])
            ->whereDate('bookings.created_at', '>=', $filters['from'])
            ->whereDate('bookings.created_at', '<=', $filters['to']);

        foreach ([
            'route_id' => ['trips.route_id', 'return_trips.route_id'],
            'trip_id'  => ['trips.id', 'return_trips.id'],
            'bus_id'   => ['trips.bus_id', 'return_trips.bus_id'],
        ] as $filter => [$primaryColumn, $returnColumn]) {
            if ($filters[$filter]) {
                $query->where(fn (Builder $builder) => $builder->where($primaryColumn, $filters[$filter])
                    ->orWhere($returnColumn, $filters[$filter]));
            }
        }

        if ($filters['trip_status'] !== 'all') {
            $query->where(fn (Builder $builder) => $builder->where('trips.status', $filters['trip_status'])
                ->orWhere('return_trips.status', $filters['trip_status']));
        }

        $counts = $query->selectRaw("CASE WHEN return_trips.id IS NULL THEN 'one_way' ELSE 'round_trip' END AS order_type")
            ->selectRaw('COUNT(DISTINCT payments.id) AS order_count')->groupBy('order_type')
            ->pluck('order_count', 'order_type')->all();

        return ['one_way' => (int) ($counts['one_way'] ?? 0), 'round_trip' => (int) ($counts['round_trip'] ?? 0)];
    }

    private function revenueByRoute($rows): array
    {
        $top = $rows->take(3)->mapWithKeys(fn (stdClass $route): array => [
            $route->origin_city.' → '.$route->destination_city.' ('.$route->route_code.')' => (float) $route->revenue,
        ]);
        $others = (float) $rows->slice(3)->sum('revenue');
        if ($others > 0) {
            $top[__('Reporting::app.other_routes')] = $others;
        }

        return $top->all();
    }

    private function routeRevenueRows(int $companyId, array $filters)
    {
        $revenue = DB::query()->fromSub($this->revenueLegs($companyId, $filters), 'revenue_legs')
            ->select('route_id', 'route_code', 'origin_city', 'destination_city')
            ->selectRaw('SUM(gross_amount - refund_amount) AS revenue')
            ->groupBy('route_id', 'route_code', 'origin_city', 'destination_city')
            ->orderByDesc('revenue')->get();
        $counts = (clone $this->tickets($companyId, $filters, false))->whereIn('tickets.status', ['active', 'used'])
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->select('routes.id')->selectRaw('COUNT(tickets.id) AS tickets_sold')->groupBy('routes.id')
            ->pluck('tickets_sold', 'id');

        return $revenue->each(fn (stdClass $route) => $route->tickets_sold = (int) ($counts[$route->route_id] ?? 0));
    }

    private function tripTickets(int $companyId, array $filters): Builder
    {
        return DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->where('trips.bus_company_id', $companyId)
            ->whereIn('tickets.status', ['active', 'used'])
            ->whereDate('trips.departure_time', '>=', $filters['from'])->whereDate('trips.departure_time', '<=', $filters['to'])
            ->where(function (Builder $query): void {
                $query->whereExists(fn (Builder $payment) => $payment->selectRaw('1')->from('payments')
                    ->whereColumn('payments.booking_id', 'bookings.id')->where('payments.status', 'completed'))
                    ->orWhereExists(fn (Builder $payment) => $payment->selectRaw('1')->from('sepay_payment_intents')
                        ->join('payments', 'payments.payment_code', '=', 'sepay_payment_intents.code')
                        ->whereColumn('sepay_payment_intents.secondary_booking_id', 'bookings.id')
                        ->where('payments.status', 'completed'));
            })
            ->when($filters['route_id'], fn (Builder $query) => $query->where('trips.route_id', $filters['route_id']))
            ->when($filters['trip_id'], fn (Builder $query) => $query->where('trips.id', $filters['trip_id']))
            ->when($filters['bus_id'], fn (Builder $query) => $query->where('trips.bus_id', $filters['bus_id']))
            ->when($filters['trip_status'] !== 'all', fn (Builder $query) => $query->where('trips.status', $filters['trip_status']));
    }

    private function topTrips(int $companyId, array $filters)
    {
        return (clone $this->trips($companyId, $filters))
            ->select('trips.id', 'trips.departure_time', 'routes.origin_city', 'routes.destination_city', 'buses.license_plate')
            ->selectRaw(<<<'SQL'
                CASE
                    WHEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1) > 0
                    THEN (SELECT COUNT(*) FROM seat_layouts WHERE seat_layouts.bus_id = trips.bus_id AND seat_layouts.is_available = 1)
                    ELSE buses.capacity
                END AS capacity
                SQL)
            ->selectRaw(<<<'SQL'
                (SELECT COUNT(*) FROM tickets JOIN bookings ON bookings.id = tickets.booking_id
                    WHERE tickets.trip_id = trips.id AND tickets.status IN ('active', 'used')
                    AND (EXISTS (SELECT 1 FROM payments WHERE payments.booking_id = bookings.id AND payments.status = 'completed')
                        OR EXISTS (SELECT 1 FROM sepay_payment_intents JOIN payments ON payments.payment_code = sepay_payment_intents.code
                            WHERE sepay_payment_intents.secondary_booking_id = bookings.id AND payments.status = 'completed'))
                ) AS tickets_sold
                SQL)
            ->selectRaw(<<<'SQL'
                (SELECT COALESCE(SUM(tickets.price), 0) FROM tickets JOIN bookings ON bookings.id = tickets.booking_id
                    WHERE tickets.trip_id = trips.id AND tickets.status IN ('active', 'used')
                    AND (EXISTS (SELECT 1 FROM payments WHERE payments.booking_id = bookings.id AND payments.status = 'completed')
                        OR EXISTS (SELECT 1 FROM sepay_payment_intents JOIN payments ON payments.payment_code = sepay_payment_intents.code
                            WHERE sepay_payment_intents.secondary_booking_id = bookings.id AND payments.status = 'completed'))
                ) AS revenue
                SQL)
            ->orderByDesc('revenue')->limit(5)->get();
    }
}

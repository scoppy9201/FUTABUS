<?php

declare(strict_types=1);

namespace FuteBus\Dashboard\Services;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\CustomerManagement\Models\Customer as CustomerRecord;
use FuteBus\Payment\Models\Payment as PaymentRecord;
use FuteBus\RouteManagement\Models\BusRoute as BusRouteRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use stdClass;

class OwnerDashboardService
{
    public function company(?User $user = null): ?stdClass
    {
        if ($user?->hasRole(['bus-company', 'staff'])) {
            if ($user->bus_company_id) {
                return BusCompanyRecord::query()->toBase()->where('id', $user->bus_company_id)->first();
            }

            return BusCompanyRecord::query()->toBase()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])
                ->first();
        }

        return BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->first();
    }

    public function overview(int $companyId): array
    {
        $bookings = $this->bookings($companyId);
        $trips = TripRecord::query()->toBase()->where('bus_company_id', $companyId);
        $buses = BusRecord::query()->toBase()->where('bus_company_id', $companyId);
        $revenueStart = now()->startOfDay()->subDays(6);
        $revenueEnd = now();
        $dailyRevenue = PaymentRecord::query()->toBase()
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->where('trips.bus_company_id', $companyId)
            ->where('payments.status', 'completed')
            ->whereBetween('payments.paid_at', [$revenueStart, $revenueEnd])
            ->selectRaw('DATE(payments.paid_at) AS revenue_date')
            ->selectRaw('SUM(payments.amount) AS revenue_amount')
            ->groupByRaw('DATE(payments.paid_at)')
            ->pluck('revenue_amount', 'revenue_date');
        $revenueChart = collect(range(0, 6))->map(function (int $offset) use ($revenueStart, $dailyRevenue): array {
            $date = $revenueStart->copy()->addDays($offset);

            return [
                'label' => $date->format('d/m'),
                'value' => (int) ($dailyRevenue->get($date->toDateString()) ?? 0),
            ];
        })->all();
        $chartPeak = max(array_column($revenueChart, 'value'));
        $chartDivisor = $chartPeak >= 1_000_000 ? 1_000_000 : ($chartPeak >= 1_000 ? 1_000 : 1);

        return [
            'booking_count'  => (clone $bookings)->count(),
            'pending_count'  => (clone $bookings)->where('bookings.status', 'pending')->count(),
            'upcoming_count' => (clone $trips)->where('departure_time', '>=', now())
                ->where('status', 'scheduled')->count(),
            'active_buses' => (clone $buses)->where('status', 'active')->count(),
            'bus_count'    => (clone $buses)->count(),
            'route_count'  => BusRouteRecord::query()->toBase()->where('bus_company_id', $companyId)->count(),
            'paid_amount'  => PaymentRecord::query()->toBase()
                ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->where('payments.status', 'completed')
                ->sum('payments.amount'),
            'revenue_chart'         => $revenueChart,
            'revenue_chart_divisor' => $chartDivisor,
            'recent_bookings'       => (clone $bookings)
                ->select('bookings.booking_code', 'bookings.status', 'bookings.total_amount',
                    'bookings.created_at', 'customers.full_name', 'routes.origin_city', 'routes.destination_city')
                ->orderByDesc('bookings.created_at')->orderByDesc('bookings.id')->limit(5)->get(),
            'upcoming_trips' => TripRecord::query()->toBase()
                ->join('routes', 'routes.id', '=', 'trips.route_id')
                ->join('buses', 'buses.id', '=', 'trips.bus_id')
                ->where('trips.bus_company_id', $companyId)
                ->where('trips.departure_time', '>=', now())
                ->where('trips.status', 'scheduled')
                ->select('trips.departure_time', 'trips.available_seats', 'routes.origin_city',
                    'routes.destination_city', 'buses.license_plate')
                ->orderBy('trips.departure_time')->limit(5)->get(),
        ];
    }

    public function listing(string $section, int $companyId): LengthAwarePaginator
    {
        $query = match ($section) {
            'trips' => TripRecord::query()->toBase()
                ->join('routes', 'routes.id', '=', 'trips.route_id')
                ->leftJoin('buses', 'buses.id', '=', 'trips.bus_id')
                ->where('trips.bus_company_id', $companyId)
                ->select('trips.id', 'trips.departure_time', 'trips.price', 'trips.status',
                    'trips.available_seats', 'routes.origin_city', 'routes.destination_city', 'buses.license_plate')
                ->orderByDesc('trips.departure_time'),
            'routes' => BusRouteRecord::query()->toBase()->where('bus_company_id', $companyId)
                ->select('id', 'code', 'origin_city', 'destination_city', 'base_price', 'is_active')
                ->orderBy('origin_city'),
            'buses' => BusRecord::query()->toBase()->where('bus_company_id', $companyId)
                ->select('id', 'license_plate', 'name', 'bus_type', 'capacity', 'status')
                ->orderBy('license_plate'),
            'bookings' => $this->bookings($companyId)
                ->select('bookings.id', 'bookings.booking_code', 'bookings.created_at',
                    'bookings.seat_count', 'bookings.total_amount', 'bookings.status',
                    'customers.full_name', 'routes.origin_city', 'routes.destination_city')
                ->orderByDesc('bookings.created_at')->orderByDesc('bookings.id'),
            'customers' => CustomerRecord::query()->toBase()
                ->join('bookings', 'bookings.customer_id', '=', 'customers.id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->select('customers.id', 'customers.full_name', 'customers.email', 'customers.phone')
                ->selectRaw('COUNT(bookings.id) AS booking_count')
                ->groupBy('customers.id', 'customers.full_name', 'customers.email', 'customers.phone')
                ->orderBy('customers.full_name'),
            'reports' => BookingRecord::query()->toBase()
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->selectRaw('DATE(bookings.created_at) AS booking_date')
                ->selectRaw('COUNT(bookings.id) AS booking_count')
                ->selectRaw('SUM(bookings.total_amount) AS booking_value')
                ->groupByRaw('DATE(bookings.created_at)')
                ->orderByDesc('booking_date'),
        };

        return $query->paginate(10)->withQueryString();
    }

    private function bookings(int $companyId): Builder
    {
        return BookingRecord::query()->toBase()
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where('trips.bus_company_id', $companyId);
    }
}

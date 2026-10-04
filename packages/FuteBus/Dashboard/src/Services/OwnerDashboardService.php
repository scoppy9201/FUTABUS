<?php

declare(strict_types=1);

namespace FuteBus\Dashboard\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class OwnerDashboardService
{
    public function company(): ?stdClass
    {
        return DB::table('bus_companies')->where('code', 'FUTA')->first();
    }

    public function overview(int $companyId): array
    {
        $bookings = $this->bookings($companyId);
        $trips = DB::table('trips')->where('bus_company_id', $companyId);
        $buses = DB::table('buses')->where('bus_company_id', $companyId);

        return [
            'booking_count'  => (clone $bookings)->count(),
            'pending_count'  => (clone $bookings)->where('bookings.status', 'pending')->count(),
            'upcoming_count' => (clone $trips)->where('departure_time', '>=', now())
                ->where('status', 'scheduled')->count(),
            'active_buses' => (clone $buses)->where('status', 'active')->count(),
            'bus_count'    => (clone $buses)->count(),
            'route_count'  => DB::table('routes')->where('bus_company_id', $companyId)->count(),
            'paid_amount'  => DB::table('payments')
                ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->where('payments.status', 'completed')
                ->sum('payments.amount'),
            'recent_bookings' => (clone $bookings)
                ->select('bookings.booking_code', 'bookings.status', 'bookings.total_amount',
                    'bookings.created_at', 'customers.full_name', 'routes.origin_city', 'routes.destination_city')
                ->orderByDesc('bookings.created_at')->orderByDesc('bookings.id')->limit(5)->get(),
            'upcoming_trips' => DB::table('trips')
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
            'trips' => DB::table('trips')
                ->join('routes', 'routes.id', '=', 'trips.route_id')
                ->join('buses', 'buses.id', '=', 'trips.bus_id')
                ->where('trips.bus_company_id', $companyId)
                ->select('trips.id', 'trips.departure_time', 'trips.price', 'trips.status',
                    'trips.available_seats', 'routes.origin_city', 'routes.destination_city', 'buses.license_plate')
                ->orderByDesc('trips.departure_time'),
            'routes' => DB::table('routes')->where('bus_company_id', $companyId)
                ->select('id', 'code', 'origin_city', 'destination_city', 'base_price', 'is_active')
                ->orderBy('origin_city'),
            'buses' => DB::table('buses')->where('bus_company_id', $companyId)
                ->select('id', 'license_plate', 'name', 'bus_type', 'capacity', 'status')
                ->orderBy('license_plate'),
            'bookings' => $this->bookings($companyId)
                ->select('bookings.id', 'bookings.booking_code', 'bookings.created_at',
                    'bookings.seat_count', 'bookings.total_amount', 'bookings.status',
                    'customers.full_name', 'routes.origin_city', 'routes.destination_city')
                ->orderByDesc('bookings.created_at')->orderByDesc('bookings.id'),
            'customers' => DB::table('customers')
                ->join('bookings', 'bookings.customer_id', '=', 'customers.id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->where('trips.bus_company_id', $companyId)
                ->select('customers.id', 'customers.full_name', 'customers.email', 'customers.phone')
                ->selectRaw('COUNT(bookings.id) AS booking_count')
                ->groupBy('customers.id', 'customers.full_name', 'customers.email', 'customers.phone')
                ->orderBy('customers.full_name'),
            'reports' => DB::table('bookings')
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
        return DB::table('bookings')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where('trips.bus_company_id', $companyId);
    }
}

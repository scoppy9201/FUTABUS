<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeService
{
    public function getPopularRoutes(int $limit = 3, int $routesPerCity = 4): Collection
    {
        $topOrigins = DB::table('routes')
            ->where('is_active', true)
            ->select('origin_city', DB::raw('COUNT(*) as route_count'))
            ->groupBy('origin_city')
            ->orderByDesc('route_count')
            ->limit($limit)
            ->pluck('origin_city');

        $grouped = collect();

        foreach ($topOrigins as $city) {
            $routes = DB::table('routes')
                ->where('origin_city', $city)
                ->where('is_active', true)
                ->orderBy('base_price')
                ->limit($routesPerCity)
                ->get([
                    'destination_city',
                    'distance_km',
                    'duration_minutes',
                    'base_price',
                ]);

            $grouped->push([
                'city'   => $city,
                'routes' => $routes,
            ]);
        }

        return $grouped;
    }
}

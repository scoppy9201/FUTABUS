<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;

class HomeService
{
    /**
     * Featured routes for the home page. Trip search uses live route and trip data.
     */
    public function getPopularRoutes(int $limit = 3, int $routesPerCity = 3): Collection
    {
        return collect([
            [
                'city' => 'TP Hồ Chí Minh',
                'image' => 'images/popular-routes/ho-chi-minh-city.png',
                'routes' => [
                    ['destination' => 'Đà Lạt', 'distance' => 310, 'hours' => 8, 'price' => 300000],
                    ['destination' => 'Cần Thơ', 'distance' => 172, 'hours' => 4, 'price' => 195000],
                    ['destination' => 'Long Xuyên', 'distance' => 209, 'hours' => 6, 'price' => 200000],
                ],
            ],
            [
                'city' => 'Đà Lạt',
                'image' => 'images/popular-routes/da-lat.png',
                'routes' => [
                    ['destination' => 'TP. Hồ Chí Minh', 'distance' => 305, 'hours' => 8, 'price' => 300000],
                    ['destination' => 'Đà Nẵng', 'distance' => 700, 'hours' => 14, 'price' => 560000],
                    ['destination' => 'Cần Thơ', 'distance' => 464, 'hours' => 11, 'price' => 480000],
                ],
            ],
            [
                'city' => 'Đà Nẵng',
                'image' => 'images/popular-routes/da-nang.png',
                'routes' => [
                    ['destination' => 'Đà Lạt', 'distance' => 700, 'hours' => 14, 'price' => 480000],
                    ['destination' => 'TP. Hồ Chí Minh', 'distance' => 990, 'hours' => 20, 'price' => 560000],
                    ['destination' => 'Nha Trang', 'distance' => 550, 'hours' => 10, 'price' => 430000],
                ],
            ],
        ])->take($limit)->map(function (array $group) use ($routesPerCity): array {
            $group['routes'] = collect($group['routes'])->take($routesPerCity);

            return $group;
        });
    }
}

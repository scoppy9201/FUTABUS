<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Sample departures for checking the public trip results UI. Run manually in local/testing only. */
class TripSearchDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::transaction(function (): void {
            $companyId = DB::table('bus_companies')->where('code', 'FUTA')->value('id');
            if (! $companyId) {
                $companyId = DB::table('bus_companies')->insertGetId([
                    'name'       => 'FUTA Bus Lines',
                    'code'       => 'FUTA',
                    'hotline'    => '1900 6067',
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $examples = [
                // Route code, origin, origin station, destination, destination station,
                // distance, minutes, price, departure, vehicle, rows, columns, available seats.
                ['DEMO-HCM-LD-05', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 'Lâm Đồng', 'Bảo Lộc', 210, 330, 240000, '05:15', 'sleeper', 10, 4, 30],
                ['DEMO-HCM-LD-01', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 'Lâm Đồng', 'Đà Lạt', 320, 510, 300000, '06:30', 'limousine', 5, 4, 16],
                ['DEMO-HCM-LD-02', 'TP. Hồ Chí Minh', 'Bến xe An Sương', 'Lâm Đồng', 'Bảo Lộc', 210, 330, 220000, '09:00', 'standard', 8, 4, 27],
                ['DEMO-HCM-LD-03', 'TP. Hồ Chí Minh', 'Bến xe Miền Đông Mới', 'Lâm Đồng', 'Đà Lạt', 305, 480, 270000, '14:30', 'sleeper', 10, 4, 34],
                ['DEMO-HCM-LD-04', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 'Lâm Đồng', 'Đà Lạt', 320, 510, 340000, '20:30', 'limousine', 5, 4, 12],
                ['DEMO-LD-HCM-01', 'Lâm Đồng', 'Đà Lạt', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 320, 510, 300000, '07:00', 'limousine', 5, 4, 17],
                ['DEMO-LD-HCM-02', 'Lâm Đồng', 'Bảo Lộc', 'TP. Hồ Chí Minh', 'Bến xe An Sương', 210, 330, 230000, '16:00', 'sleeper', 10, 4, 32],
                ['DEMO-HCM-LD-06', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 'Lâm Đồng', 'Đà Lạt', 320, 510, 300000, '22:50', 'limousine', 5, 4, 18],
                ['DEMO-LD-HCM-03', 'Lâm Đồng', 'Đà Lạt', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 320, 510, 300000, '05:30', 'limousine', 5, 4, 18],
                ['DEMO-LD-HCM-04', 'Lâm Đồng', 'Đà Lạt', 'TP. Hồ Chí Minh', 'Bến xe Miền Đông Mới', 320, 510, 280000, '12:30', 'sleeper', 10, 4, 32],
                ['DEMO-LD-HCM-05', 'Lâm Đồng', 'Đà Lạt', 'TP. Hồ Chí Minh', 'Bến xe Miền Tây', 320, 510, 300000, '22:50', 'limousine', 5, 4, 16],
            ];

            foreach ($examples as [$code, $from, $fromStation, $to, $toStation, $distance, $duration, $price, $time, $type, $rows, $columns, $available]) {
                $routeId = DB::table('routes')->where('code', $code)->value('id');
                if (! $routeId) {
                    $routeId = DB::table('routes')->insertGetId([
                        'bus_company_id'      => $companyId,
                        'code'                => $code,
                        'name'                => '[Demo] '.$from.' - '.$to,
                        'origin_city'         => $from,
                        'origin_station'      => $fromStation,
                        'destination_city'    => $to,
                        'destination_station' => $toStation,
                        'distance_km'         => $distance,
                        'duration_minutes'    => $duration,
                        'base_price'          => $price,
                        'is_active'           => true,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }

                $this->seedRouteStops($routeId, $fromStation, $toStation, $duration, $from);

                $busId = DB::table('buses')->where('license_plate', $code)->value('id');
                if (! $busId) {
                    $busId = DB::table('buses')->insertGetId([
                        'bus_company_id'   => $companyId,
                        'license_plate'    => $code,
                        'name'             => '[Demo] '.ucfirst($type),
                        'capacity'         => $rows * $columns,
                        'bus_type'         => $type,
                        'status'           => 'active',
                        'seat_rows'        => $rows,
                        'seat_columns'     => $columns,
                        'color'            => 'Cam FUTABUS',
                        'chassis_number'   => $code,
                        'brand'            => 'FUTABUS Demo',
                        'manufacture_year' => (int) now()->year,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }

                for ($row = 1; $row <= $rows; $row++) {
                    for ($column = 1; $column <= $columns; $column++) {
                        $seatCode = chr(64 + $row).$column;
                        if (DB::table('seat_layouts')->where('bus_id', $busId)->where('seat_code', $seatCode)->exists()) {
                            continue;
                        }
                        DB::table('seat_layouts')->insert([
                            'bus_id'           => $busId,
                            'seat_code'        => $seatCode,
                            'row_number'       => $row,
                            'column_number'    => $column,
                            'seat_type'        => $type === 'standard' ? 'seat' : 'sleeper',
                            'deck'             => $type === 'standard' || $row <= (int) ceil($rows / 2) ? 'lower' : 'upper',
                            'price_multiplier' => 1,
                            'is_available'     => true,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                for ($day = 0; $day <= 30; $day++) {
                    $departure = Carbon::today()->addDays($day)->setTimeFromTimeString($time);
                    if ($departure->lte(now()->addMinutes(15))) {
                        continue;
                    }
                    $departureTime = $departure->toDateTimeString();
                    if (DB::table('trips')->where('route_id', $routeId)->where('bus_id', $busId)->where('departure_time', $departureTime)->exists()) {
                        continue;
                    }
                    DB::table('trips')->insert([
                        'route_id'        => $routeId,
                        'bus_id'          => $busId,
                        'bus_company_id'  => $companyId,
                        'departure_time'  => $departureTime,
                        'arrival_time'    => $departure->copy()->addMinutes($duration)->toDateTimeString(),
                        'price'           => $price,
                        'status'          => 'scheduled',
                        'available_seats' => $available,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        });
    }

    private function seedRouteStops(int $routeId, string $origin, string $destination, int $duration, string $originCity): void
    {
        $addresses = [
            'Bến xe Miền Tây'       => '395 Kinh Dương Vương, Phường An Lạc, TP. Hồ Chí Minh',
            'Bến xe An Sương'       => '16 Lê Quang Đạo, Xã Bà Điểm, TP. Hồ Chí Minh',
            'Bến xe Miền Đông Mới'  => '501 Hoàng Hữu Nam, Phường Long Bình, TP. Hồ Chí Minh',
            'Bến xe Miền Đông Cũ'   => '292 Đinh Bộ Lĩnh, Phường Bình Thạnh, TP. Hồ Chí Minh',
            'Văn phòng Quang Trung' => '190 Quang Trung, Phường Gò Vấp, TP. Hồ Chí Minh',
            'Đà Lạt'                => '01 Tô Hiến Thành, Phường Xuân Hương, Đà Lạt, Lâm Đồng',
            'Bảo Lộc'               => "399 Trần Phú, Phường B'Lao, Lâm Đồng",
        ];

        foreach ($addresses as $name => $address) {
            DB::table('route_stops')->where('route_id', $routeId)
                ->where('name', $name)->whereNull('address')
                ->update(['address' => $address, 'updated_at' => now()]);
        }

        if (DB::table('route_stops')->where('route_id', $routeId)->exists()) {
            return;
        }

        $stops = [
            [$origin, 0, 'pickup'],
            [$destination, $duration, 'dropoff'],
        ];
        if ($originCity === 'TP. Hồ Chí Minh') {
            $stops[] = ['Văn phòng Quang Trung', 15, 'pickup'];
            $stops[] = ['Bến xe Miền Đông Cũ', 30, 'pickup'];
        } else {
            $stops[] = ['Văn phòng Quang Trung', $duration - 30, 'dropoff'];
            $stops[] = ['Bến xe Miền Đông Cũ', $duration - 15, 'dropoff'];
        }
        if ($destination === 'Đà Lạt') {
            $stops[] = ['Bảo Lộc', $duration - 90, 'dropoff'];
        }
        if ($origin === 'Đà Lạt') {
            $stops[] = ['Bảo Lộc', 180, 'pickup'];
        }

        usort($stops, fn (array $a, array $b): int => $a[1] <=> $b[1]);
        foreach ($stops as $index => [$name, $offset, $type]) {
            DB::table('route_stops')->insert([
                'route_id'       => $routeId,
                'name'           => $name,
                'address'        => $addresses[$name],
                'stop_order'     => $index + 1,
                'offset_minutes' => $offset,
                'stop_type'      => $type,
                'status'         => 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}

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
}

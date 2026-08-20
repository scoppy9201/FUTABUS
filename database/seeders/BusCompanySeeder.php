<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusCompanySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
            'Huế', 'Nha Trang', 'Đà Lạt', 'Vũng Tàu', 'Quy Nhơn',
        ];


        $companies = [
            ['FUTA Bus Lines', 'FUTA', '1900 6067', 'futa@futabus.vn', 'Hà Nội'],
            ['Phương Trang', 'PT', '1900 7070', 'pt@futabus.vn', 'Hồ Chí Minh'],
            ['Mai Linh', 'ML', '1900 6868', 'ml@futabus.vn', 'Đà Nẵng'],
            ['Hoàng Long', 'HL', '1900 6262', 'hl@futabus.vn', 'Hà Nội'],
        ];
        $companyIds = [];
        foreach ($companies as $c) {
            $companyIds[] = DB::table('bus_companies')->insertGetId([
                'name'    => $c[0],
                'code'    => $c[1],
                'hotline' => $c[2],
                'email'   => $c[3],
                'address' => $c[4],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        $busIds = [];
        foreach ($companyIds as $cid) {
            foreach (['A', 'B'] as $letter) {
                $plate = '29B-' . $this->randPlate();
                $busId = DB::table('buses')->insertGetId([
                    'bus_company_id' => $cid,
                    'license_plate'  => $plate,
                    'name'           => 'Xe ' . $plate,
                    'capacity'       => 45,
                    'bus_type'       => 'sleeper',
                    'status'         => 'active',
                    'seat_rows'      => 11,
                    'seat_columns'   => 4,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $busIds[] = $busId;

                // Sơ đồ ghế: 11 hàng x 4 cột
                $rowLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                foreach ($rowLabels as $i => $row) {
                    for ($col = 1; $col <= 4; $col++) {
                        $deck = $i < 6 ? 'lower' : 'upper';
                        $seatCode = $row . $col;
                        DB::table('seat_layouts')->insert([
                            'bus_id'           => $busId,
                            'seat_code'        => $seatCode,
                            'row_number'       => $i + 1,
                            'column_number'    => $col,
                            'seat_type'        => 'sleeper',
                            'deck'             => $deck,
                            'price_multiplier' => 1.00,
                            'is_available'     => true,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }
            }
        }


        $routeIds = [];
        $pairs = [
            ['Hà Nội', 'Hồ Chí Minh', 1600, 1800, 850000],
            ['Hà Nội', 'Đà Nẵng', 760, 900, 450000],
            ['Hồ Chí Minh', 'Đà Nẵng', 960, 1100, 500000],
            ['Hồ Chí Minh', 'Nha Trang', 450, 500, 280000],
            ['Hà Nội', 'Hải Phòng', 100, 120, 150000],
            ['Đà Nẵng', 'Huế', 100, 150, 180000],
            ['Hà Nội', 'Quy Nhơn', 1100, 1300, 600000],
        ];
        foreach ($pairs as $idx => $p) {
            $cid = $companyIds[$idx % count($companyIds)];
            $routeIds[] = DB::table('routes')->insertGetId([
                'bus_company_id'      => $cid,
                'code'                => 'RT-' . strtoupper(Str::random(4)),
                'name'                => $p[0] . ' - ' . $p[1],
                'origin_city'         => $p[0],
                'destination_city'    => $p[1],
                'distance_km'         => $p[2],
                'duration_minutes'    => $p[3],
                'base_price'          => $p[4],
                'is_active'           => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }


        foreach ($routeIds as $i => $rid) {
            $busId = $busIds[$i % count($busIds)];
            for ($day = 0; $day < 7; $day++) {
                $departure = now()->addDays($day)->setTime(8 + ($i % 5), 0, 0);
                $route = DB::table('routes')->where('id', $rid)->first();
                $arrival = $departure->copy()->addMinutes($route->duration_minutes);
                DB::table('trips')->insert([
                    'route_id'         => $rid,
                    'bus_id'           => $busId,
                    'bus_company_id'   => $route->bus_company_id,
                    'departure_time'   => $departure,
                    'arrival_time'     => $arrival,
                    'price'            => $route->base_price,
                    'status'           => 'scheduled',
                    'available_seats'  => 45,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    private function randPlate(): string
    {
        return rand(10000, 99999) . '.' . rand(10, 99);
    }
}
<?php

namespace Tests\Feature\Core;

use FuteBus\Core\Services\BookingLocationCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeLocationPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_location_picker_uses_separate_operator_departure_and_destination_data(): void
    {
        $catalog = json_decode(
            file_get_contents(app_path('Data/futa_booking_locations.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertCount(9, $catalog['departure']['provinces']);
        $this->assertCount(13, $catalog['departure']['areas']);
        $this->assertCount(5, $catalog['destination']['provinces']);
        $this->assertCount(5, $catalog['destination']['areas']);
        $this->assertSame('Cà Mau', $catalog['departure']['provinces'][0]);
        $this->assertSame('An Giang', $catalog['destination']['provinces'][0]);
        $this->assertCount(2, $catalog['departure']['areas'][8]['offices']);
        $this->assertSame([], $catalog['destination']['areas'][1]['offices']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('locationCatalog:', false)
            ->assertSee('Tỉnh/Thành phố')
            ->assertSee('Không tìm thấy địa điểm');
    }

    public function test_active_futa_route_and_branch_extend_the_searchable_location_catalog(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA Bus Lines', 'code' => 'FUTA', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId, 'license_plate' => '51B-22222', 'capacity' => 34,
            'created_at'     => now(), 'updated_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id' => $companyId, 'code' => 'TEST-CANTHO', 'name' => 'Cần Thơ - Đà Lạt',
            'origin_city'    => 'Cần Thơ', 'destination_city' => 'Đà Lạt', 'origin_station' => 'Bến xe Cần Thơ',
            'base_price'     => 300000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('trips')->insert([
            'route_id'       => $routeId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
            'departure_time' => now()->addDay(), 'arrival_time' => now()->addDays(2),
            'price'          => 300000, 'available_seats' => 20, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $regionId = DB::table('branch_regions')->insertGetId([
            'name'       => json_encode(['vi' => 'Miền Nam']), 'slug' => 'mien-nam',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('branch_offices')->insert([
            'branch_region_id' => $regionId,
            'name'             => json_encode(['vi' => 'Văn phòng Cần Thơ']),
            'address'          => json_encode(['vi' => 'Cần Thơ']),
            'created_at'       => now(), 'updated_at' => now(),
        ]);

        $catalog = app(BookingLocationCatalog::class)->all();

        $this->assertContains('Cần Thơ', $catalog['departure']['provinces']);
        $this->assertSame('Bến xe Cần Thơ', collect($catalog['departure']['areas'])->last()['name']);
        $this->assertSame('Văn phòng Cần Thơ', $catalog['departure']['directory_offices'][0]['name']);
    }
}

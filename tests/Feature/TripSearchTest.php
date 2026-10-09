<?php

namespace Tests\Feature;

use FuteBus\Core\Services\TripSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TripSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_shows_only_available_futa_trips_on_the_requested_route_and_date(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA Bus Lines', 'code' => 'FUTA', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId, 'license_plate' => '51B-12345', 'capacity' => 34,
            'bus_type'       => 'limousine', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('seat_layouts')->insert([
            ['bus_id' => $busId, 'seat_code' => 'A1', 'row_number' => 1, 'column_number' => 1, 'deck' => 'lower', 'created_at' => now(), 'updated_at' => now()],
            ['bus_id' => $busId, 'seat_code' => 'K1', 'row_number' => 11, 'column_number' => 1, 'deck' => 'upper', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id' => $companyId, 'code' => 'HCM-DALAT', 'name' => 'Hồ Chí Minh - Đà Lạt',
            'origin_city'    => 'Hồ Chí Minh', 'destination_city' => 'Đà Lạt', 'distance_km' => 320,
            'base_price'     => 300000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $departure = now()->addDays(2)->setTime(8, 0);
        DB::table('trips')->insert([
            [
                'route_id'       => $routeId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
                'departure_time' => $departure, 'arrival_time' => $departure->copy()->addHours(8),
                'price'          => 300000, 'status' => 'scheduled', 'available_seats' => 31,
                'created_at'     => now(), 'updated_at' => now(),
            ],
            [
                'route_id'       => $routeId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
                'departure_time' => $departure->copy()->addHour(), 'arrival_time' => $departure->copy()->addHours(9),
                'price'          => 300000, 'status' => 'cancelled', 'available_seats' => 34,
                'created_at'     => now(), 'updated_at' => now(),
            ],
        ]);

        $query = [
            'departure'      => 'TP. Hồ Chí Minh', 'destination' => 'Lâm Đồng',
            'departure_date' => $departure->toDateString(), 'trip_type' => 'one_way', 'quantity' => 1,
        ];

        $trips = app(TripSearchService::class)->search(
            $query['departure'], $query['destination'], $query['departure_date'], 1,
        );
        $this->assertCount(1, $trips);
        $this->assertSame(31, $trips[0]['available_seats']);
        $this->assertSame('Đà Lạt', $trips[0]['destination']);
        $this->assertEqualsCanonicalizing(['front', 'back'], $trips[0]['row_options']);
        $this->assertEqualsCanonicalizing(['lower', 'upper'], $trips[0]['deck_options']);
        $this->assertSame([], app(TripSearchService::class)->search(
            'Bx Miền Tây', $query['destination'], $query['departure_date'], 1,
        ));
        $this->assertSame([], app(TripSearchService::class)->search(
            $query['departure'], 'Di Linh', $query['departure_date'], 1,
        ));

        $this->get(route('trip-search', $query))
            ->assertOk()
            ->assertSee(asset('images/banners/home-banner.jpg'))
            ->assertSee('TP. Hồ Chí Minh - Lâm Đồng')
            ->assertSee('Bộ lọc tìm kiếm');

        $this->get(route('trip-booking.show', ['trip' => $trips[0]['id'], ...$query, 'seats' => $trips[0]['seats'][0]['id']]))
            ->assertOk()
            ->assertSee('Quay lại kết quả tìm kiếm')
            ->assertSee('Điều khoản &amp; lưu ý', false)
            ->assertSee('Thông tin đón trả')
            ->assertSee('Thông tin các chuyến đi (1)')
            ->assertSee('id="booking-trip-detail-modal"', false)
            ->assertSee('Hình ảnh/Video')
            ->assertSee('Chính sách huỷ vé')
            ->assertSee('Thời gian các mốc lịch trình là thời gian dự kiến')
            ->assertSee('A1');

        $twoSeatIds = array_column(array_slice($trips[0]['seats'], 0, 2), 'id');
        $this->assertCount(2, $twoSeatIds);
        $this->get(route('trip-booking.show', [
            'trip' => $trips[0]['id'], ...$query, 'seats' => implode(',', $twoSeatIds),
        ]))
            ->assertOk()
            ->assertViewHas('selectedSeatIds', $twoSeatIds)
            ->assertSee('limit: 5', false);

        $paymentUrl = route('trip-booking.payment.store', ['trip' => $trips[0]['id'], ...$query]);
        $paymentData = [
            'name'         => 'Nguyen Van A',
            'phone'        => '0912345678',
            'email'        => 'customer@example.com',
            'accept_terms' => '1',
            'seats'        => [$twoSeatIds[0]],
            'pickup_mode'  => 'station',
            'dropoff_mode' => 'station',
        ];
        $paymentResponse = $this->post($paymentUrl, $paymentData)->assertRedirect();
        $this->get($paymentResponse->headers->get('Location'))
            ->assertOk()
            ->assertSee('Chọn phương thức thanh toán')
            ->assertSee('Mã SePay sẽ hiển thị khi cấu hình tài khoản nhận tiền và webhook.')
            ->assertSee('Thời gian tới điểm lên xe')
            ->assertSee('Thời gian nhận khách')
            ->assertSee('Chỉ được chuyển đổi vé 1 lần duy nhất')
            ->assertSee('Nguyen Van A')
            ->assertSee('300.000đ');
        $this->travel(11)->minutes();
        $this->get($paymentResponse->headers->get('Location'))
            ->assertRedirect(route('home').'#trip-search');
        $this->travelBack();
        $this->post($paymentUrl, [...$paymentData, 'seats' => [999999]])
            ->assertSessionHasErrors('seats');
        $this->get(route('trip-payment-preview.show', ['draft' => 'unknown']))
            ->assertRedirect(route('home').'#trip-search');
        $this->assertDatabaseCount('bookings', 0);

        $this->get(route('trip-booking.show', ['trip' => 999999, ...$query]))->assertNotFound();

        $this->get(route('trip-search', [...$query, 'departure' => 'Hà Nội']))
            ->assertOk()
            ->assertSee('Không tìm thấy chuyến xe phù hợp');

        $reverseRouteId = DB::table('routes')->insertGetId([
            'bus_company_id' => $companyId, 'code' => 'DALAT-HCM', 'name' => 'Đà Lạt - Hồ Chí Minh',
            'origin_city'    => 'Đà Lạt', 'destination_city' => 'Hồ Chí Minh',
            'base_price'     => 300000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $returnDate = $departure->copy()->addDay();
        DB::table('trips')->insert([
            'route_id'       => $reverseRouteId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
            'departure_time' => $returnDate, 'arrival_time' => $returnDate->copy()->addHours(8),
            'price'          => 300000, 'available_seats' => 20,
            'created_at'     => now(), 'updated_at' => now(),
        ]);

        $this->get(route('trip-search', [
            ...$query, 'trip_type' => 'round_trip', 'return_date' => $returnDate->toDateString(),
        ]))
            ->assertOk()
            ->assertViewHas('returnTrips', fn (array $returnTrips) => count($returnTrips) === 1);

        $this->get(route('trip-search', [...$query, 'quantity' => 32]))
            ->assertSessionHasErrors('quantity');

        DB::table('trips')->where('status', 'scheduled')->update(['available_seats' => 0]);
        $this->assertSame([], app(TripSearchService::class)->search(
            $query['departure'], $query['destination'], $query['departure_date'], 1,
        ));
    }

    public function test_search_rejects_past_dates_and_zero_tickets(): void
    {
        $this->get(route('trip-search', [
            'departure'      => 'Hà Nội', 'destination' => 'Lâm Đồng',
            'departure_date' => now()->subDay()->toDateString(),
            'trip_type'      => 'one_way', 'quantity' => 0,
        ]))->assertSessionHasErrors(['departure_date', 'quantity']);
    }
}

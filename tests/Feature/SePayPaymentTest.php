<?php

namespace Tests\Feature;

use FuteBus\Core\Services\TripSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SePayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private int $tripId;

    private int $seatId;

    private array $criteria;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.sepay', [
            'enabled'        => true,
            'bank'           => 'VCB',
            'account_no'     => '1234567890',
            'webhook_auth'   => 'hmac',
            'webhook_secret' => 'test-secret',
            'webhook_key'    => 'legacy-key',
        ]);

        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA Bus Lines',
            'code' => 'FUTA',
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId,
            'license_plate'  => '51B-12345',
            'capacity'       => 34,
            'bus_type'       => 'limousine',
        ]);
        $this->seatId = DB::table('seat_layouts')->insertGetId([
            'bus_id'        => $busId,
            'seat_code'     => 'A01',
            'row_number'    => 1,
            'column_number' => 1,
            'deck'          => 'lower',
            'is_available'  => true,
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'HCM-DALAT',
            'name'             => 'Hồ Chí Minh - Đà Lạt',
            'origin_city'      => 'Hồ Chí Minh',
            'destination_city' => 'Đà Lạt',
            'distance_km'      => 320,
            'base_price'       => 300000,
            'is_active'        => true,
        ]);
        $departure = now()->addDays(2)->setTime(8, 0);
        $this->tripId = DB::table('trips')->insertGetId([
            'route_id'        => $routeId,
            'bus_id'          => $busId,
            'bus_company_id'  => $companyId,
            'departure_time'  => $departure,
            'arrival_time'    => $departure->copy()->addHours(8),
            'price'           => 300000,
            'status'          => 'scheduled',
            'available_seats' => 34,
        ]);
        $this->criteria = [
            'departure'      => 'TP. Hồ Chí Minh',
            'destination'    => 'Lâm Đồng',
            'departure_date' => $departure->toDateString(),
            'trip_type'      => 'one_way',
            'quantity'       => 1,
        ];
    }

    public function test_valid_webhook_confirms_booking_and_retries_do_not_duplicate_tickets(): void
    {
        $url = $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();

        $this->get($url)->assertOk()
            ->assertSee('Thời gian giữ chỗ còn lại')
            ->assertSee('des='.$intent->code)
            ->assertSee('amount=300000');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertTrue(app(TripSearchService::class)->search(
            $this->criteria['departure'],
            $this->criteria['destination'],
            $this->criteria['departure_date'],
            1
        ) === []);

        $event = $this->event($intent->code);
        $this->signedWebhook($event)->assertOk()->assertJson(['success' => true]);
        $this->signedWebhook($event)->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('booked_seats', 1);
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('sepay_transactions', 1);
        $this->assertDatabaseHas('sepay_payment_intents', ['id' => $intent->id, 'status' => 'paid']);
        $this->get(route('trip-payment-preview.status', ['draft' => $intent->token]))
            ->assertOk()->assertJson(['status' => 'paid']);
        $this->get($url)->assertOk()->assertSee('Thanh toán thành công');
    }

    public function test_payment_does_not_show_a_transfer_qr_without_webhook_authentication(): void
    {
        config()->set('services.sepay.webhook_secret', null);
        $url = $this->createIntent();

        $this->assertDatabaseCount('sepay_payment_intents', 0);
        $this->get($url)->assertOk()
            ->assertSee('Mã SePay sẽ hiển thị khi cấu hình tài khoản nhận tiền và webhook.')
            ->assertDontSee('vietqr.app/img');
    }

    public function test_existing_preview_can_create_a_payment_code_after_sepay_is_enabled(): void
    {
        config()->set('services.sepay.enabled', false);
        $url = $this->createIntent();
        $this->assertDatabaseCount('sepay_payment_intents', 0);

        config()->set('services.sepay.enabled', true);
        $draft = basename(parse_url($url, PHP_URL_PATH));
        $this->get($url)->assertOk()->assertSee('Tạo mã SePay');
        $this->post(route('trip-payment-preview.activate', ['draft' => $draft]))
            ->assertRedirect($url);

        $this->assertDatabaseCount('sepay_payment_intents', 1);
        $this->get($url)->assertOk()
            ->assertSee('Thời gian giữ chỗ còn lại')
            ->assertSee('vietqr.app/img');
    }

    public function test_webhook_rejects_missing_signature_and_does_not_create_a_booking(): void
    {
        $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();

        $this->postJson(route('sepay.webhook'), $this->event($intent->code))
            ->assertUnauthorized();
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('sepay_transactions', 0);
    }

    public function test_wrong_amount_is_recorded_for_manual_review_without_issuing_a_ticket(): void
    {
        $url = $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();
        $event = $this->event($intent->code);
        $event['transferAmount'] = 100000;

        $this->signedWebhook($event)->assertOk();
        $this->assertDatabaseHas('sepay_payment_intents', ['id' => $intent->id, 'status' => 'needs_review']);
        $this->assertDatabaseCount('bookings', 0);
        $this->get($url)->assertOk()->assertSee('Giao dịch cần được kiểm tra');
    }

    public function test_late_payment_needs_review_and_expired_seats_are_available_again(): void
    {
        $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();

        $this->travel(11)->minutes();
        $this->signedWebhook($this->event($intent->code))->assertOk();
        $this->assertDatabaseHas('sepay_payment_intents', ['id' => $intent->id, 'status' => 'needs_review']);
        $this->assertDatabaseCount('bookings', 0);
        $this->assertNotEmpty(app(TripSearchService::class)->search(
            $this->criteria['departure'],
            $this->criteria['destination'],
            $this->criteria['departure_date'],
            1
        ));
    }

    public function test_hmac_rejects_modified_payload_and_old_timestamp(): void
    {
        $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();
        $event = $this->event($intent->code);
        $modified = $event;
        $modified['transferAmount'] = 1;

        $this->signedWebhook($modified, null, json_encode($event, JSON_THROW_ON_ERROR))
            ->assertUnauthorized();
        $this->signedWebhook($event, now()->subMinutes(6)->timestamp)
            ->assertUnauthorized();
        $this->assertDatabaseCount('sepay_transactions', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_hmac_mode_does_not_accept_an_api_key_as_fallback(): void
    {
        $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();

        $this->withHeader('Authorization', 'Apikey legacy-key')
            ->postJson(route('sepay.webhook'), $this->event($intent->code))
            ->assertUnauthorized();
        $this->assertDatabaseCount('sepay_transactions', 0);
    }

    public function test_explicit_api_key_mode_keeps_existing_webhooks_compatible(): void
    {
        config()->set('services.sepay.webhook_auth', 'api_key');
        $this->createIntent();
        $intent = DB::table('sepay_payment_intents')->first();

        $this->withHeader('Authorization', 'Apikey legacy-key')
            ->postJson(route('sepay.webhook'), $this->event($intent->code))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseCount('bookings', 1);
    }

    private function signedWebhook(array $event, ?int $timestamp = null, ?string $signedBody = null)
    {
        $timestamp ??= now()->timestamp;
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $timestamp.'.'.($signedBody ?? $body), 'test-secret');

        return $this->call('POST', route('sepay.webhook'), [], [], [], [
            'CONTENT_TYPE'           => 'application/json',
            'HTTP_ACCEPT'            => 'application/json',
            'HTTP_X_SEPAY_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SEPAY_SIGNATURE' => 'sha256='.$signature,
        ], $body);
    }

    private function createIntent(): string
    {
        $response = $this->post(route('trip-booking.payment.store', [
            'trip' => $this->tripId,
            ...$this->criteria,
        ]), [
            'name'         => 'Nguyen Van A',
            'phone'        => '0912345678',
            'email'        => 'customer@example.com',
            'accept_terms' => '1',
            'seats'        => [$this->seatId],
            'pickup_mode'  => 'station',
            'dropoff_mode' => 'station',
        ])->assertRedirect();

        return $response->headers->get('Location');
    }

    private function event(string $code): array
    {
        return [
            'id'              => 92704,
            'gateway'         => 'Vietcombank',
            'transactionDate' => now()->toDateTimeString(),
            'accountNumber'   => '1234567890',
            'code'            => $code,
            'content'         => $code,
            'transferType'    => 'in',
            'transferAmount'  => 300000,
            'referenceCode'   => 'BANK-123',
        ];
    }
}

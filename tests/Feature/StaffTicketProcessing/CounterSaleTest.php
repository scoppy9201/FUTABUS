<?php

namespace Tests\Feature\StaffTicketProcessing;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CounterSaleTest extends TestCase
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

    public function test_verified_payment_issues_customer_tickets_and_retry_is_idempotent(): void
    {
        $staff = $this->operator();
        $this->actingAs($staff)->get(route('staff.tickets.sale'))->assertOk();
        $this->get(route('staff.tickets.sale', [...$this->criteria, 'quantity' => 2]))->assertOk()->assertDontSee('images/banners/home-banner.jpg');
        $this->withHeader('Origin', config('app.url'))->withSession(['_token' => 'staff-sale-test-token'])->withHeader('X-CSRF-TOKEN', 'staff-sale-test-token');
        $this->withToken($staff->createToken('test', ['api'])->plainTextToken);
        $this->getJson(route('api.v1.staff.trips.index', $this->criteria))->assertOk()->assertJsonPath('data.0.id', $this->tripId);
        $booking = $this->getJson(route('api.v1.staff.trips.booking-context.show', ['trip' => $this->tripId, ...$this->criteria, 'seats' => (string) $this->seatId]))->assertOk();
        $this->assertStringContainsString('booking-captcha-title', $booking->json('data.html'));
        $this->assertStringContainsString('booking-customer-name', $booking->json('data.html'));
        $payload = $this->payload();
        $this->postJson(route('api.v1.staff.previews.store'), [...$payload, 'quantity' => 2])->assertUnprocessable()->assertJsonValidationErrors('seats');
        $this->postJson(route('api.v1.staff.quotes.store'), $payload)->assertOk()->assertJsonPath('data.amount', 300000);
        $this->assertDatabaseCount('sepay_payment_intents', 0);
        config()->set('services.sepay.enabled', false);
        $this->post(route('staff.ticket-sales.store'), [...$payload, '_token' => 'staff-sale-test-token'], ['Accept' => 'text/html', 'X-CSRF-TOKEN' => null])
            ->assertStatus(303)->assertRedirect(route('staff.tickets.payment', $payload['idempotency_key']));
        $this->get(route('staff.tickets.payment', $payload['idempotency_key']))->assertOk()->assertSee('payment-page__layout');
        $this->assertDatabaseCount('sepay_payment_intents', 0);
        $this->postJson(route('api.v1.staff.previews.store'), $payload)->assertOk()
            ->assertJsonPath('data.status', 'draft')->assertJsonPath('data.amount', 300000)
            ->assertJsonPath('data.qr_url', null)->assertJsonPath('data.expires_at', null);
        $this->assertDatabaseCount('sepay_payment_intents', 0);
        $this->assertDatabaseCount('tickets', 0);
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertStatus(503)->assertJsonPath('code', 'payment_unavailable');
        $this->assertDatabaseCount('sepay_payment_intents', 0);
        config()->set('services.sepay.enabled', true);
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertCreated()->assertJsonPath('data.status', 'pending');
        $this->post(route('api.v1.staff.sales.store'), $payload, ['Accept' => 'text/html'])
            ->assertCreated()->assertHeader('Content-Type', 'application/json')->assertJsonPath('data.status', 'pending');
        $this->post(route('staff.ticket-sales.store'), [...$payload, '_token' => 'staff-sale-test-token'], ['Accept' => 'text/html'])
            ->assertStatus(303)->assertRedirect(route('staff.tickets.payment', $payload['idempotency_key']));
        $this->get(route('staff.tickets.payment', $payload['idempotency_key']))->assertOk()->assertSee('payment-page__layout');
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertCreated();
        $this->assertDatabaseCount('sepay_payment_intents', 1);
        $this->assertDatabaseCount('tickets', 0);
        $intent = DB::table('sepay_payment_intents')->first();
        $snapshot = json_decode($intent->snapshot, true);
        $this->assertNull($snapshot['user_id']);
        $this->assertSame($staff->id, $snapshot['operator']['id']);
        $this->getJson(route('api.v1.staff.sales.show', $intent->token))->assertOk()->assertJsonPath('data.booking', null);
        $this->signedWebhook($this->event($intent->code))->assertOk();
        $this->signedWebhook($this->event($intent->code))->assertOk();
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('booked_seats', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('customers', ['phone' => '0912345678', 'full_name' => 'Customer', 'user_id' => null]);
        $this->assertDatabaseHas('bookings', ['user_id' => null, 'status' => 'confirmed']);
        $paidReceipt = $this->getJson(route('api.v1.staff.sales.show', $intent->token))->assertOk()->assertJsonPath('data.status', 'paid')->assertJsonCount(1, 'data.tickets');
        $this->assertStringContainsString('Phương thức thanh toán', $paidReceipt->json('data.html'));
        $this->assertStringContainsString('data-payment-method>Chuyển khoản</dd>', $paidReceipt->json('data.html'));
        $this->deleteJson(route('api.v1.staff.sales.destroy', $intent->token))->assertConflict();
        $this->assertDatabaseHas('sepay_payment_intents', ['token' => $intent->token, 'status' => 'paid']);
        $returnRoute = DB::table('routes')->insertGetId([
            'bus_company_id' => $staff->bus_company_id, 'code' => 'DALAT-HCM', 'name' => 'Đà Lạt - Hồ Chí Minh',
            'origin_city'    => 'Đà Lạt', 'destination_city' => 'Hồ Chí Minh', 'distance_km' => 320, 'base_price' => 370000, 'is_active' => true,
        ]);
        $returnDate = now()->addDays(4)->setTime(8, 0);
        $returnTrip = DB::table('trips')->insertGetId([
            'route_id'       => $returnRoute, 'bus_id' => DB::table('trips')->where('id', $this->tripId)->value('bus_id'),
            'bus_company_id' => $staff->bus_company_id, 'departure_time' => $returnDate, 'arrival_time' => $returnDate->copy()->addHours(8),
            'price'          => 370000, 'status' => 'scheduled', 'available_seats' => 34,
        ]);
        $outboundSeat = DB::table('seat_layouts')->insertGetId([
            'bus_id'    => DB::table('trips')->where('id', $this->tripId)->value('bus_id'),
            'seat_code' => 'A02', 'row_number' => 1, 'column_number' => 2, 'deck' => 'lower', 'is_available' => true,
        ]);
        $returnPayload = [...$payload, 'trip_id' => $returnTrip, 'idempotency_key' => (string) Str::uuid(),
            'outbound_trip' => $this->tripId, 'outbound_seats' => [$outboundSeat], 'outbound_pickup_mode' => 'station', 'outbound_dropoff_mode' => 'station',
            'trip_type'     => 'round_trip', 'return_date' => $returnDate->toDateString(), 'direction' => 'return',
            'pickup_mode'   => 'transfer', 'pickup_address' => 'Customer pickup address', 'dropoff_mode' => 'transfer', 'dropoff_address' => 'Customer drop-off address'];
        $this->getJson(route('api.v1.staff.trips.index', $returnPayload))->assertOk()->assertJsonPath('data.0.id', $returnTrip);
        $roundBooking = $this->getJson(route('api.v1.staff.trips.booking-context.show', ['trip' => $returnTrip, ...$returnPayload, 'seats' => implode(',', $returnPayload['seats']), 'outbound_seats' => (string) $outboundSeat]))->assertOk();
        $this->assertStringContainsString('booking-outbound-seats-title', $roundBooking->json('data.html'));
        $this->postJson(route('api.v1.staff.quotes.store'), $returnPayload)->assertOk()->assertJsonPath('data.amount', 370000);
        $this->postJson(route('api.v1.staff.sales.store'), $returnPayload)->assertCreated()->assertJsonPath('data.amount', 670000)
            ->assertJsonPath('data.preview.direction', 'return')->assertJsonPath('data.preview.pickup.address', 'Customer pickup address');
        $this->assertDatabaseCount('tickets', 1);
        $this->actingAs($this->operator())
            ->getJson(route('api.v1.staff.sales.show', $intent->token))->assertNotFound();
    }

    public function test_competing_seat_request_is_rejected_and_permissions_and_company_are_enforced(): void
    {
        $staff = $this->operator();
        $this->withToken($staff->createToken('test', ['api'])->plainTextToken);
        $payload = $this->payload();
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertCreated();
        $this->postJson(route('api.v1.staff.sales.store'), [...$payload, 'idempotency_key' => (string) Str::uuid()])
            ->assertUnprocessable()->assertJsonValidationErrors('seats');
        $this->postJson(route('api.v1.staff.sales.store'), [...$payload, 'name' => 'Changed customer'])->assertConflict();
        $this->postJson(route('api.v1.staff.sales.store'), [...$payload, 'idempotency_key' => (string) Str::uuid(), 'quoted_fare' => 1])->assertConflict();
        $this->assertDatabaseCount('sepay_payment_intents', 1);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('tickets', 0);
        $this->deleteJson(route('api.v1.staff.sales.destroy', ['sale' => $payload['idempotency_key']]))->assertOk()->assertJsonPath('data.status', 'expired');
        $this->assertDatabaseHas('sepay_payment_intents', ['token' => $payload['idempotency_key'], 'status' => 'expired']);
        $otherCompany = DB::table('bus_companies')->insertGetId(['name' => 'Other company', 'code' => 'OTHER']);
        $staff->update(['bus_company_id' => $otherCompany]);
        DB::table('roles')->where('slug', 'staff')->update(['bus_company_id' => $otherCompany]);
        auth()->forgetGuards();
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertNotFound();
        $this->postJson(route('api.v1.staff.previews.store'), $payload)->assertNotFound();
        $this->getJson(route('api.v1.staff.trips.booking-context.show', ['trip' => $this->tripId, ...$this->criteria]))->assertNotFound();
        DB::table('permission_role')->delete();
        $this->getJson(route('api.v1.staff.trips.index', $this->criteria))->assertForbidden();
        $this->postJson(route('api.v1.staff.sales.store'), $payload)->assertForbidden();
        $this->postJson(route('api.v1.staff.previews.store'), $payload)->assertForbidden();
        $customer = $this->createTestUser(['is_active' => true, 'email_verified_at' => now()]);
        auth()->forgetGuards();
        $this->withToken($customer->createToken('test', ['api'])->plainTextToken)->getJson(route('api.v1.staff.sale-context.show'))->assertForbidden();
        $this->actingAs($customer)->get(route('staff.tickets.sale'))->assertForbidden();
    }

    private function operator(): User
    {
        $roleId = DB::table('roles')->where('slug', 'staff')->value('id');
        if ($roleId === null) {
            $roleId = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff', 'bus_company_id' => DB::table('bus_companies')->where('code', 'FUTA')->value('id')]);
            $permission = DB::table('permissions')->insertGetId(['name' => 'Create booking', 'slug' => 'booking.create']);
            DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permission]);
        }
        $staff = $this->createTestUser(['bus_company_id' => DB::table('bus_companies')->where('code', 'FUTA')->value('id'), 'is_active' => true, 'email_verified_at' => now()]);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $roleId]);

        return $staff;
    }

    private function payload(): array
    {
        return [...$this->criteria, 'trip_id' => $this->tripId, 'idempotency_key' => (string) Str::uuid(),
            'name'  => 'Customer', 'phone' => '0912345678', 'email' => 'customer@example.com', 'accept_terms' => true,
            'seats' => [$this->seatId], 'pickup_mode' => 'station', 'dropoff_mode' => 'station'];
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

<?php

namespace Tests\Feature\StaffTicketProcessing;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashSaleTest extends TestCase
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

    public function test_cash_receipt_issues_real_tickets_once_without_sepay_configuration(): void
    {
        config()->set('services.sepay.enabled', false);
        $staff = $this->operator();
        $token = (string) Str::uuid();
        $payload = [...$this->payload(), 'idempotency_key' => $token, '_token' => 'cash-test'];
        $this->actingAs($staff)->withHeader('Origin', config('app.url'))->withSession(['_token' => 'cash-test']);
        $this->post(route('staff.ticket-sales.store'), $payload)->assertRedirect(route('staff.tickets.payment', $token));
        $page = $this->get(route('staff.tickets.payment', $token))->assertOk();
        $page->assertSee('staffPayment(')->assertSee('data-confirm', false)->assertSee('data-cash-payment', false);
        $page->assertSee('startPaymentClock()', false)->assertSee('Bạn có chắc chắn muốn hủy vé này?');
        $this->assertStringContainsString('Thanh toán tiền mặt', $page->original->getData()['initialSale']['html']);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('sepay_payment_intents', 0);
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $token), ['received' => false, '_token' => 'cash-test'])->assertUnprocessable();
        $receipt = $this->postJson(route('api.v1.staff.sales.cash-collections.store', $token), ['received' => true, '_token' => 'cash-test'])->assertOk()->assertJsonPath('data.status', 'paid')->assertJsonPath('data.amount', 300000);
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $token), ['received' => true, '_token' => 'cash-test'])->assertOk()->assertJsonPath('data.reference', $receipt->json('data.reference'));
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('payments', ['method' => 'cash', 'amount' => 300000, 'status' => 'completed', 'transaction_id' => null]);
        $this->assertDatabaseHas('bookings', ['user_id' => null, 'status' => 'confirmed']);
        $audit = json_decode(DB::table('payments')->value('payload'), true);
        $this->assertSame($staff->id, $audit['operator_id']);
        $paidPage = $this->get(route('staff.tickets.payment', $token))->assertOk();
        $this->assertStringContainsString('Đã ghi nhận thu tiền mặt', $paidPage->original->getData()['initialSale']['html']);
        $receiptHtml = $paidPage->original->getData()['initialSale']['html'];
        $this->assertStringContainsString('Phương thức thanh toán', $receiptHtml);
        $this->assertStringContainsString('data-payment-method>Tiền mặt</dd>', $receiptHtml);
        $this->assertStringNotContainsString('>SePay</dd>', $receiptHtml);
        $this->assertStringContainsString('Vé xe khách điện tử', $receiptHtml);
        $this->assertStringContainsString('51B-12345', $receiptHtml);
        $this->assertStringContainsString('Thông tin đón/trả khách', $receiptHtml);
        $this->assertStringContainsString('In vé', $receiptHtml);
        $this->assertSame(1, substr_count($receiptHtml, 'data-print-tickets'));
        $this->deleteJson(route('api.v1.staff.sales.destroy', $token), ['_token' => 'cash-test'])->assertConflict();
        $other = $this->operator();
        $this->actingAs($other)->get(route('staff.tickets.payment', $token))->assertNotFound();
    }

    public function test_cash_rechecks_holds_expiry_permissions_and_back_abandons_only_unpaid_sales(): void
    {
        $staff = $this->operator();
        $this->actingAs($staff)->withHeader('Origin', config('app.url'))->withSession(['_token' => 'cash-test'])->withHeader('X-CSRF-TOKEN', 'cash-test');
        config()->set('services.sepay.enabled', false);
        $payload = $this->payload();
        $token = $payload['idempotency_key'];
        $this->post(route('staff.ticket-sales.store'), $payload)->assertStatus(303);
        config()->set('services.sepay.enabled', true);
        $hold = [...$payload, 'idempotency_key' => (string) Str::uuid()];
        $this->postJson(route('api.v1.staff.sales.store'), $hold)->assertCreated();
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $token), ['received' => true])->assertUnprocessable()->assertJsonValidationErrors('seats');
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('tickets', 0);
        $this->delete(route('staff.ticket-sales.destroy', $token))->assertRedirect(route('staff.tickets.sale', $this->criteria))->assertStatus(303);
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $token), ['received' => true])->assertNotFound();
        $this->delete(route('staff.ticket-sales.destroy', $hold['idempotency_key']))->assertStatus(303);
        $this->assertDatabaseHas('sepay_payment_intents', ['token' => $hold['idempotency_key'], 'status' => 'expired']);
        config()->set('services.sepay.enabled', false);
        $expired = [...$payload, 'idempotency_key' => (string) Str::uuid()];
        $this->post(route('staff.ticket-sales.store'), $expired)->assertStatus(303);
        $this->travel(11)->minutes();
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $expired['idempotency_key']), ['received' => true])->assertStatus(410);
        $this->travelBack();
        config()->set('services.sepay.enabled', true);
        $pending = [...$payload, 'idempotency_key' => (string) Str::uuid()];
        $this->post(route('staff.ticket-sales.store'), $pending)->assertStatus(303);
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $pending['idempotency_key']), ['received' => true])->assertOk()->assertJsonPath('data.status', 'paid');
        $this->assertDatabaseHas('sepay_payment_intents', ['token' => $pending['idempotency_key'], 'status' => 'expired']);
        $this->assertDatabaseCount('sepay_transactions', 0);
        DB::table('permission_role')->delete();
        $this->postJson(route('api.v1.staff.sales.cash-collections.store', $pending['idempotency_key']), ['received' => true])->assertForbidden();
        $this->assertDatabaseCount('payments', 1);
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
}

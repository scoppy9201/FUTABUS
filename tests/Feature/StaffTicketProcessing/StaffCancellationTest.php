<?php

namespace Tests\Feature\StaffTicketProcessing;

use App\Models\User;
use FuteBus\Payment\Services\ManualRefundService;
use FuteBus\Payment\Services\StaffCashRefundService;
use FuteBus\Payment\Services\TicketEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class StaffCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Notification::fake();
        $this->instance(TicketEmailService::class, Mockery::mock(TicketEmailService::class, function ($mock): void {
            $mock->shouldReceive('cancellationRequested', 'refundCompleted')->zeroOrMoreTimes();
        }));
    }

    public function test_cash_guest_refund_is_explicit_audited_atomic_and_idempotent(): void
    {
        $f = $this->fixture(true, true);
        $this->operator($f['staff']);
        $this->get(route('staff.tickets.cancellation'))->assertOk()->assertSeeText('Thanh toán');
        $this->getJson($f['url'])->assertOk()->assertJsonPath('cash', true)->assertJsonPath('quote.refund', 315000);
        $this->assertDatabaseCount('ticket_cancellations', 0);
        $this->postJson($f['url'], [...$this->payload(), 'refund_method' => 'cash'])->assertUnprocessable()->assertJsonValidationErrors('cash_paid');
        $this->assertDatabaseCount('ticket_refunds', 0);
        $payment = (array) DB::table('payments')->first();
        $response = $this->postJson($f['url'], $this->payload(true))->assertOk()->assertJsonPath('status', 'refunded');
        $this->postJson($f['url'], $this->payload(true))->assertOk()->assertJsonPath('reference', $response->json('reference'));
        $this->assertDatabaseCount('ticket_cancellations', 1);
        $this->assertDatabaseCount('ticket_refunds', 1);
        $this->assertDatabaseCount('ticket_refund_events', 1);
        $this->assertDatabaseHas('ticket_cancellations', ['ticket_id' => $f['ticket'], 'user_id' => null, 'actor_id' => $f['staff']->id]);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'refunded']);
        $this->assertDatabaseMissing('booked_seats', ['booking_id' => $f['booking']]);
        $this->assertDatabaseHas('bookings', ['id' => $f['booking'], 'user_id' => null, 'seat_count' => 0, 'total_amount' => 0]);
        $this->assertSame($payment, (array) DB::table('payments')->first());
        Http::assertNothingSent();
    }

    public function test_registered_cash_customer_keeps_ownership_and_online_refund_enters_existing_queue(): void
    {
        foreach ([true, false] as $cash) {
            $f = $this->fixture($cash, false);
            $this->operator($f['staff']);
            $this->getJson($f['url'])->assertOk()->assertJsonPath('cash', $cash);
            $this->postJson($f['url'], $this->payload($cash))->assertOk()->assertJsonPath('status', $cash ? 'refunded' : 'refund_pending');
            $this->assertDatabaseHas('ticket_cancellations', ['ticket_id' => $f['ticket'], 'user_id' => $f['customer']->id, 'actor_id' => $f['staff']->id]);
            $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => $cash ? 'refunded' : 'cancelled']);
            if (! $cash) {
                $this->assertTrue(app(ManualRefundService::class)->visible($f['staff'])->where('ticket_cancellations.ticket_id', $f['ticket'])->exists());
                $this->assertDatabaseMissing('ticket_refund_events', ['actor_id' => $f['staff']->id]);
            }
        }
        Http::assertNothingSent();
    }

    public function test_permissions_company_and_csrf_are_checked_before_mutation(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['customer'])->getJson($f['url'])->assertForbidden();
        $this->actingAs($f['staff'])->postJson($f['url'], $this->payload())->assertStatus(419);
        $this->operator($f['staff']);
        $company = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        DB::table('trips')->where('id', $f['trip'])->update(['bus_company_id' => $company]);
        $this->getJson($f['url'])->assertNotFound();
        DB::table('trips')->where('id', $f['trip'])->update(['bus_company_id' => $f['staff']->bus_company_id]);
        $permission = DB::table('permissions')->where('slug', 'ticket.cancel')->value('id');
        DB::table('permission_role')->where('permission_id', $permission)->delete();
        $this->postJson($f['url'], $this->payload())->assertForbidden();
        $this->assertDatabaseCount('ticket_cancellations', 0);
    }

    public function test_cash_requires_refund_authority_and_stale_quotes_do_not_cancel(): void
    {
        $f = $this->fixture(true, true);
        $this->operator($f['staff']);
        $this->postJson($f['url'], [...$this->payload(true), 'expected_refund' => 350000])->assertUnprocessable();
        $permission = DB::table('permissions')->where('slug', 'payment.refund')->value('id');
        DB::table('permission_role')->where('permission_id', $permission)->delete();
        $this->postJson($f['url'], $this->payload(true))->assertForbidden();
        $this->assertDatabaseCount('ticket_cancellations', 0);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
    }

    public function test_cash_failure_rolls_back_ticket_refund_and_seat_changes(): void
    {
        $f = $this->fixture(true, true);
        $this->operator($f['staff']);
        $this->instance(StaffCashRefundService::class, Mockery::mock(StaffCashRefundService::class, function ($mock): void {
            $mock->shouldReceive('complete')->once()->andThrow(new RuntimeException('Settlement failed'));
        }));
        $this->postJson($f['url'], $this->payload(true))->assertStatus(500);
        $this->assertDatabaseCount('ticket_cancellations', 0);
        $this->assertDatabaseCount('ticket_refunds', 0);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseHas('booked_seats', ['booking_id' => $f['booking']]);
    }

    public function test_online_guest_and_tickets_under_four_hours_are_rejected(): void
    {
        $f = $this->fixture(false, true);
        $this->operator($f['staff']);
        $this->getJson($f['url'])->assertOk()->assertJsonPath('can_request_refund', false);
        $this->postJson($f['url'], $this->payload())->assertUnprocessable()->assertJsonValidationErrors('refund_method');
        $f = $this->fixture(true, false);
        $this->operator($f['staff']);
        DB::table('trips')->where('id', $f['trip'])->update(['departure_time' => now()->addHours(3)]);
        $this->postJson($f['url'], $this->payload(true))->assertUnprocessable();
        $this->assertDatabaseCount('ticket_cancellations', 0);
    }

    public function test_online_payment_can_be_refunded_in_cash_for_registered_and_guest_customers(): void
    {
        foreach ([false, true] as $guest) {
            $f = $this->fixture(false, $guest);
            $this->operator($f['staff']);
            $original = (array) DB::table('payments')->where('booking_id', $f['booking'])->first();
            $this->postJson($f['url'], [...$this->payload(), 'refund_method' => 'cash'])->assertUnprocessable();
            $this->postJson($f['url'], $this->payload(true))->assertOk()->assertJsonPath('status', 'refunded');
            $this->postJson($f['url'], $this->payload())->assertOk()->assertJsonPath('status', 'refunded');
            $this->assertSame($original, (array) DB::table('payments')->where('booking_id', $f['booking'])->first());
        }
        $this->assertDatabaseCount('ticket_refunds', 2);
        $this->assertDatabaseCount('ticket_refund_events', 2);
        Http::assertNothingSent();
    }

    private function operator(User $staff): void
    {
        $this->actingAs($staff)->withSession(['_token' => 'staff-cancel'])->withHeader('X-CSRF-TOKEN', 'staff-cancel');
    }

    private function payload(bool $paid = false): array
    {
        return ['refund_method' => $paid ? 'cash' : 'request', 'reason' => 'plans', 'note' => 'Test cancellation', 'expected_refund' => 315000, 'cash_paid' => $paid];
    }

    private function fixture(bool $cash = false, bool $guest = false): array
    {
        $code = strtoupper(Str::random(12));
        $company = DB::table('bus_companies')->insertGetId(['code' => $code, 'name' => 'FUTA']);
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true, 'email_verified_at' => now()]);
        $role = DB::table('roles')->insertGetId(['slug' => 'staff', 'name' => 'Staff', 'bus_company_id' => $company]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $staff->id]);
        foreach (['ticket.view', 'ticket.cancel', 'payment.view', 'payment.refund'] as $slug) {
            DB::table('permissions')->insertOrIgnore(['slug' => $slug, 'name' => $slug, 'group' => explode('.', $slug)[0], 'is_active' => true]);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => DB::table('permissions')->where('slug', $slug)->value('id')]);
        }
        $customer = $this->createTestUser(['is_active' => true]);
        $profile = DB::table('customers')->insertGetId(['user_id' => $guest ? null : $customer->id, 'full_name' => 'Test Customer', 'phone' => '090'.random_int(1000000, 9999999)]);
        $bus = DB::table('buses')->insertGetId(['bus_company_id' => $company, 'license_plate' => $code, 'capacity' => 2]);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => $code, 'name' => 'Test route', 'origin_city' => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000]);
        $trip = DB::table('trips')->insertGetId(['bus_company_id' => $company, 'bus_id' => $bus, 'route_id' => $route, 'departure_time' => now()->addDays(3), 'arrival_time' => now()->addDays(3)->addHours(8), 'price' => 350000]);
        $seat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1]);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => $code, 'trip_id' => $trip, 'user_id' => $guest ? null : $customer->id, 'customer_id' => $profile, 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => $code.'T', 'booking_id' => $booking, 'trip_id' => $trip, 'seat_layout_id' => $seat, 'seat_code' => 'A01', 'passenger_name' => 'Test Customer', 'price' => 350000, 'status' => 'active']);
        DB::table('booked_seats')->insert(['booking_id' => $booking, 'trip_id' => $trip, 'seat_layout_id' => $seat, 'seat_code' => 'A01', 'price' => 350000]);
        $transaction = random_int(1000000, 9999999);
        DB::table('payments')->insert(['payment_code' => $cash ? 'CASH'.$code : $code, 'booking_id' => $booking, 'amount' => 350000,
            'method'                                  => $cash ? 'cash' : 'bank_transfer', 'status' => 'completed', 'paid_at' => now(), 'transaction_id' => (string) $transaction,
            'payload'                                 => json_encode(['operator_id' => $staff->id, 'booking_ids' => [$booking]])]);
        if (! $cash) {
            DB::table('sepay_transactions')->insert(['sepay_id' => $transaction, 'code' => $code, 'amount' => 350000, 'status' => 'matched', 'environment' => 'sandbox', 'payload' => '{}']);
        }
        $url = route('staff.tickets.cancellation.context', $ticket);

        return compact('staff', 'customer', 'ticket', 'booking', 'trip', 'url');
    }
}

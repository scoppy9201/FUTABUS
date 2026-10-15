<?php

namespace Tests\Feature\Cancellation;

use FuteBus\Cancellation\Notifications\TicketCancelled;
use FuteBus\Payment\Services\SePayRefundService;
use FuteBus\Payment\Services\TicketEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class TicketCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->travelTo(now()->startOfSecond());
        config(['services.sepay.refund_environment' => 'sandbox']);
        Http::preventStrayRequests();
        Notification::fake();
    }

    public function test_fee_boundaries_are_calculated_on_server(): void
    {
        [$user, $booking, $ticket, $trip] = $this->fixture();
        foreach ([[86401, 10, 315000], [86400, 30, 245000], [14400, 30, 245000]] as [$seconds, $fee, $refund]) {
            DB::table('trips')->where('id', $trip)->update(['departure_time' => now()->addSeconds($seconds)]);
            $this->actingAs($user)->postJson(route('cancellation.quote', $booking), ['ticket_id' => $ticket])
                ->assertOk()->assertJsonPath('quote.fee_percent', $fee)->assertJsonPath('quote.refund', $refund);
        }
        DB::table('trips')->where('id', $trip)->update(['departure_time' => now()->addSeconds(14399)]);
        $this->postJson(route('cancellation.quote', $booking), ['ticket_id' => $ticket])->assertUnprocessable();
    }

    public function test_cancellation_releases_only_selected_seat_and_duplicate_is_idempotent(): void
    {
        [$user, $booking, $ticket, $trip, $seat] = $this->fixture();
        $this->mock(TicketEmailService::class, function ($mock): void {
            $mock->shouldReceive('cancellationRequested')->once()->with(Mockery::type('int'));
        });
        $this->actingAs($user)->get(route('cancellation.show', $booking))->assertOk()->assertSee('data-cancellation-form', false);
        $input = ['ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1];
        $url = route('cancellation.store', $booking);
        $response = $this->postJson($url, $input)->assertOk()->assertJsonPath('refund_status', 'refund_pending');
        $this->postJson($url, $input)->assertOk()->assertJsonPath('reference', $response->json('reference'));
        $this->assertDatabaseCount('ticket_cancellations', 1);
        $this->assertDatabaseCount('ticket_refunds', 1);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->assertDatabaseMissing('booked_seats', ['trip_id' => $trip, 'seat_layout_id' => $seat]);
        $this->assertDatabaseHas('payments', ['booking_id' => $booking, 'status' => 'completed']);
        Http::assertNothingSent();
    }

    public function test_foreign_ticket_missing_consent_and_stale_quote_cannot_cancel(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        $url = route('cancellation.store', $booking);
        $input = ['ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1];
        $this->actingAs($this->createTestUser())->postJson($url, $input)->assertNotFound();
        $this->actingAs($user)->postJson($url, [...$input, 'consent' => 0])->assertUnprocessable();
        $this->postJson($url, [...$input, 'expected_refund' => 350000])->assertUnprocessable();
        $this->postJson($url, [...$input, 'reason' => 'forged'])->assertUnprocessable();
        $this->assertDatabaseCount('ticket_cancellations', 0);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'active']);
    }

    public function test_manual_cancellation_accepts_live_receipt_without_refund_api_credentials(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        DB::table('sepay_transactions')->update(['environment' => 'live']);
        $this->actingAs($user)->postJson(route('cancellation.quote', $booking), ['ticket_id' => $ticket])->assertOk();
        $this->postJson(route('cancellation.store', $booking), ['ticket_id' => $ticket, 'expected_refund' => 315000,
            'reason'                                                        => 'plans', 'consent' => 1])->assertOk();
        $this->assertDatabaseHas('ticket_refunds', ['processing_mode' => 'manual', 'environment' => 'live', 'status' => 'refund_pending']);
        Http::assertNothingSent();
    }

    public function test_partial_cancellation_preserves_other_ticket_and_its_reserved_seat(): void
    {
        [$user, $booking, $ticket, $trip, $seat] = $this->fixture();
        $bus = DB::table('trips')->where('id', $trip)->value('bus_id');
        $secondSeat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A02', 'row_number' => 1, 'column_number' => 2]);
        $secondTicket = DB::table('tickets')->insertGetId(['ticket_code' => 'TICKET2', 'booking_id' => $booking, 'trip_id' => $trip,
            'seat_layout_id'                                             => $secondSeat, 'seat_code' => 'A02', 'passenger_name' => 'Other passenger', 'price' => 350000]);
        DB::table('booked_seats')->insert(['booking_id' => $booking, 'trip_id' => $trip, 'seat_layout_id' => $secondSeat, 'seat_code' => 'A02', 'price' => 350000]);
        DB::table('payments')->update(['amount' => 700000]);
        DB::table('sepay_transactions')->update(['amount' => 700000]);
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $booking, 'status' => 'confirmed']);
        $this->assertDatabaseHas('tickets', ['id' => $secondTicket, 'status' => 'active']);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $trip, 'seat_layout_id' => $secondSeat]);
        $this->assertDatabaseMissing('booked_seats', ['trip_id' => $trip, 'seat_layout_id' => $seat]);
        Notification::assertSentToTimes($user, TicketCancelled::class, 1);
    }

    public function test_return_ticket_uses_verified_shared_payment_and_preserves_outbound_ticket(): void
    {
        [$user, $booking, $ticket, $trip] = $this->fixture();
        DB::table('payments')->update(['amount' => 700000]);
        DB::table('sepay_transactions')->update(['amount' => 700000]);
        $returnBooking = DB::table('bookings')->insertGetId(['booking_code' => 'RETURN', 'trip_id' => $trip,
            'user_id'                                                       => $user->id, 'customer_id' => DB::table('bookings')->value('customer_id'),
            'seat_count'                                                    => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        $returnTicket = DB::table('tickets')->insertGetId(['ticket_code' => 'RETURN-TICKET', 'booking_id' => $returnBooking,
            'trip_id'                                                    => $trip, 'passenger_name' => 'Test', 'price' => 350000]);
        DB::table('sepay_payment_intents')->insert(['token' => (string) Str::uuid(), 'code' => 'PAY',
            'trip_id'                                       => $trip, 'secondary_trip_id' => $trip, 'booking_id' => $booking, 'secondary_booking_id' => $returnBooking,
            'status'                                        => 'paid', 'seat_ids' => '[]', 'snapshot' => '{}', 'amount' => 700000, 'expires_at' => now()->addDay()]);
        $this->actingAs($user)->postJson(route('cancellation.store', $returnBooking), [
            'ticket_id' => $returnTicket, 'expected_refund' => 315000, 'reason' => 'urgent', 'consent' => 1,
        ])->assertOk();
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'active']);
        $this->assertDatabaseHas('ticket_refunds', ['payment_id' => DB::table('payments')->value('id'), 'amount' => 315000]);
    }

    public function test_unauthorized_status_and_changed_departure_cannot_leak_or_cancel(): void
    {
        [$user, $booking, $ticket, $trip] = $this->fixture();
        $this->actingAs($this->createTestUser())->getJson(route('cancellation.status', $booking))->assertNotFound();
        $this->actingAs($user)->postJson(route('cancellation.quote', $booking), ['ticket_id' => $ticket])->assertOk();
        DB::table('trips')->where('id', $trip)->update(['departure_time' => now()->addHours(6)]);
        $this->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('ticket');
        $this->assertDatabaseCount('ticket_cancellations', 0);
    }

    public function test_network_timeout_reuses_the_original_idempotency_key(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertOk();
        $this->enableLegacyAutomation();
        config(['services.sepay.refund_enabled' => true, 'services.sepay.refund_token' => 'test-token', 'services.sepay.refund_bank_id' => 'test-bank']);
        $keys = [];
        Http::fake(function ($request) use (&$keys) {
            $keys[] = $request->header('X-Idempotency-Key')[0];
            if (count($keys) === 1) {
                throw new ConnectionException('Timed out');
            }

            return Http::response(['status' => 'success', 'data' => ['refund_id' => 'RF-RETRY', 'transaction_id' => '12345',
                'amount'                                                         => 315000, 'currency' => 'VND', 'status' => 'pending']]);
        });
        $refund = DB::table('ticket_refunds')->first();
        app(SePayRefundService::class)->process($refund->id);
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund->id, 'status' => 'queued', 'error_code' => 'connection_unknown']);
        app(SePayRefundService::class)->process($refund->id);
        $this->assertSame([$refund->idempotency_key, $refund->idempotency_key], $keys);
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund->id, 'status' => 'pending']);
    }

    public function test_provider_pending_then_verified_success_updates_ticket_only_after_confirmation(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertOk();
        $this->enableLegacyAutomation();
        config(['services.sepay.refund_enabled' => true, 'services.sepay.refund_token' => 'test-token', 'services.sepay.refund_bank_id' => 'test-bank']);
        $data = ['refund_id' => 'RF-TEST', 'transaction_id' => '12345', 'amount' => 315000, 'currency' => 'VND', 'status' => 'pending'];
        Http::fake(['userapi-sandbox.sepay.vn/*' => Http::sequence()
            ->push(['status' => 'success', 'data' => $data])
            ->push(['status' => 'success', 'data' => [...$data, 'status' => 'succeeded']])]);
        $refund = DB::table('ticket_refunds')->first();
        app(SePayRefundService::class)->process($refund->id);
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund->id, 'status' => 'pending']);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->hasHeader('X-Idempotency-Key', $refund->idempotency_key) && $request['refund_amount'] === 315000);
        $this->mock(TicketEmailService::class, function ($mock) use ($refund): void {
            $mock->shouldReceive('refundCompleted')->once()->with($refund->id);
        });
        app(SePayRefundService::class)->process($refund->id);
        app(SePayRefundService::class)->process($refund->id);
        Http::assertSentCount(2);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'refunded']);
    }

    public function test_mismatched_provider_amount_never_marks_refund_as_complete(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertOk();
        $this->enableLegacyAutomation();
        config(['services.sepay.refund_enabled' => true, 'services.sepay.refund_token' => 'test-token', 'services.sepay.refund_bank_id' => 'test-bank']);
        Http::fake(['*' => Http::response(['status' => 'success', 'data' => [
            'refund_id' => 'RF-WRONG', 'transaction_id' => '12345', 'amount' => 1, 'currency' => 'VND', 'status' => 'succeeded',
        ]])]);
        app(SePayRefundService::class)->process(DB::table('ticket_refunds')->value('id'));
        $this->assertDatabaseHas('ticket_refunds', ['status' => 'queued', 'error_code' => 'provider_unverified']);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
    }

    public function test_provider_rejection_is_visible_for_support_and_not_retried_by_scheduler(): void
    {
        [$user, $booking, $ticket] = $this->fixture();
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), [
            'ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
        ])->assertOk();
        $this->enableLegacyAutomation();
        config(['services.sepay.refund_enabled' => true, 'services.sepay.refund_token' => 'test-token', 'services.sepay.refund_bank_id' => 'test-bank']);
        Http::fake(['*' => Http::response(['error_code' => 'unsupported_bank'], 422)]);
        $this->artisan('tickets:process-refunds')->assertSuccessful();
        $this->artisan('tickets:process-refunds')->assertSuccessful();
        Http::assertSentCount(1);
        $this->assertDatabaseHas('ticket_refunds', ['status' => 'needs_review']);
        $this->getJson(route('cancellation.status', $booking))->assertOk()->assertJsonPath('receipts.0.status', 'needs_review');
        $this->get(route('cancellation.show', $booking))->assertOk()->assertSee(__('Cancellation::cancellation.statuses.needs_review'));
    }

    public function test_used_ticket_and_departed_trip_are_rejected_even_with_forged_request_fields(): void
    {
        [$user, $booking, $ticket, $trip] = $this->fixture();
        $input = ['ticket_id' => $ticket, 'expected_refund' => 315000, 'reason' => 'plans', 'consent' => 1,
            'status'          => 'active', 'fee_percent' => 0, 'refund_status' => 'succeeded'];
        DB::table('tickets')->where('id', $ticket)->update(['status' => 'used']);
        $this->actingAs($user)->postJson(route('cancellation.store', $booking), $input)->assertUnprocessable();
        DB::table('tickets')->where('id', $ticket)->update(['status' => 'active']);
        DB::table('trips')->where('id', $trip)->update(['status' => 'departed']);
        $this->postJson(route('cancellation.store', $booking), $input)->assertUnprocessable();
        $this->assertDatabaseCount('ticket_cancellations', 0);
        $this->assertDatabaseCount('ticket_refunds', 0);
    }

    private function fixture(): array
    {
        $user = $this->createTestUser();
        $company = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'TEST']);
        $bus = DB::table('buses')->insertGetId(['bus_company_id' => $company, 'license_plate' => 'TEST']);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => 'TEST', 'name' => 'Route',
            'origin_city'                                           => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000]);
        $trip = DB::table('trips')->insertGetId(['route_id' => $route, 'bus_id' => $bus, 'bus_company_id' => $company,
            'departure_time'                                => now()->addDays(3), 'arrival_time' => now()->addDays(3)->addHours(6), 'price' => 350000]);
        $customer = DB::table('customers')->insertGetId(['user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0900000000']);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => 'TEST', 'trip_id' => $trip, 'user_id' => $user->id, 'customer_id' => $customer,
            'seat_count'                                              => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        $seat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1]);
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => 'TICKET', 'booking_id' => $booking, 'trip_id' => $trip,
            'seat_layout_id'                                       => $seat, 'seat_code' => 'A01', 'passenger_name' => 'Test', 'price' => 350000]);
        DB::table('booked_seats')->insert(['booking_id' => $booking, 'trip_id' => $trip, 'seat_layout_id' => $seat, 'seat_code' => 'A01', 'price' => 350000]);
        DB::table('payments')->insert(['payment_code' => 'PAY', 'booking_id' => $booking, 'amount' => 350000,
            'method'                                  => 'bank_transfer', 'status' => 'completed', 'transaction_id' => '12345']);
        DB::table('sepay_transactions')->insert(['sepay_id' => 12345, 'code' => 'PAY', 'amount' => 350000, 'status' => 'matched', 'environment' => 'sandbox', 'payload' => '{}']);

        return [$user, $booking, $ticket, $trip, $seat];
    }

    private function enableLegacyAutomation(): void
    {
        config(['services.sepay.refund_mode' => 'automatic']);
        DB::table('ticket_refunds')->update(['processing_mode' => 'automatic', 'status' => 'queued']);
    }
}

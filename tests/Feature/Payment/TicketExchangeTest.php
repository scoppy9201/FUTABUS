<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use FuteBus\Payment\Models\TicketExchange;
use FuteBus\Payment\Services\BookingPaymentResolver;
use FuteBus\Payment\Services\ExchangeSeatHolds;
use FuteBus\Payment\Services\SePayIntentService;
use FuteBus\Payment\Services\TicketEmailService;
use FuteBus\Payment\Services\TicketExchangeService;
use FuteBus\Profile\Services\TicketHistoryService;
use FuteBus\StaffTicketProcessing\Services\CashSaleService;
use FuteBus\TicketManagement\Services\TicketLookupService;
use FuteBus\TripSearch\Services\TripSearchService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class TicketExchangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->travelTo(now()->startOfSecond());
        Notification::fake();
        Http::preventStrayRequests();
    }

    public function test_verified_exchange_is_atomic_idempotent_and_keeps_the_original_payment(): void
    {
        $f = $this->fixture();
        $payment = (array) DB::table('payments')->first();
        $this->actingAs($f['user'])->get(route('exchange.show', $f['booking']))->assertOk()
            ->assertSee('data-exchange-wizard', false)
            ->assertSee("togglePanel(trip.id, 'schedule')", false)
            ->assertSee('chooseSeat(trip, seat)', false)
            ->assertSee('trip.timeline', false)
            ->assertSee("stopOption(trip, 'pickup'", false);
        $token = $this->hold($f);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $f['trip'], 'seat_layout_id' => $f['oldSeat']]);
        $url = route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]);
        $this->postJson($url, [])->assertUnprocessable();
        $this->mock(TicketEmailService::class, function ($mock): void {
            $mock->shouldReceive('exchanged')->once()->with(Mockery::type('int'));
        });
        $response = $this->postJson($url, ['consent' => true])->assertOk()->assertJsonStructure(['code', 'qr', 'url']);
        $this->postJson($url, ['consent' => true])->assertOk()->assertJsonPath('code', $response->json('code'));
        $exchange = TicketExchange::first();
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame($payment, (array) DB::table('payments')->first());
        $this->assertDatabaseCount('ticket_exchanges', 1);
        $this->assertDatabaseCount('tickets', 2);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'cancelled']);
        $this->assertDatabaseHas('tickets', ['id' => $exchange->new_ticket_id, 'status' => 'active', 'trip_id' => $f['target'], 'seat_layout_id' => $f['seat']]);
        $this->assertDatabaseMissing('booked_seats', ['trip_id' => $f['trip'], 'seat_layout_id' => $f['oldSeat']]);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $f['target'], 'seat_layout_id' => $f['seat']]);
        $history = app(TicketHistoryService::class)->detailForUser($f['user'], $exchange->new_booking_id);
        $this->assertSame('completed', $history['payment']->status);
        $this->assertSame('paid', $history['stage']);
        $this->assertSame('exchanged', app(TicketHistoryService::class)->detailForUser($f['user'], $f['booking'])['stage']);
        $lookup = app(TicketLookupService::class)->find('0900000000', $response->json('code'));
        $this->assertSame('completed', $lookup['paymentStatus']);
        $this->get(route('profile.tickets.show', $exchange->new_booking_id))->assertOk()->assertSee($response->json('code'));
        $this->get(route('profile.tickets.show', $f['booking']))->assertOk()->assertSee('Đã đổi');
        $this->assertDatabaseCount('ticket_refunds', 0);
        Http::assertNothingSent();
    }

    public function test_live_hold_blocks_normal_checkout_and_search_and_release_restores_availability(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        $trips = app(TripSearchService::class)->search('HCM', 'Da Lat', now()->addDays(4)->toDateString(), 1);
        $selected = collect($trips)->firstWhere('id', $f['target']);
        $this->assertTrue(collect($selected['seats'])->firstWhere('id', $f['seat'])['sold']);
        try {
            app(SePayIntentService::class)->createIntent(['token' => (string) Str::uuid(), 'trip' => ['id' => $f['target'], 'fare' => 350000], 'seat_ids' => [$f['seat']]]);
            $this->fail('An exchange-held seat must not be sold.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('seats', $error->errors());
        }
        $url = route('exchange.release', ['booking' => $f['booking'], 'token' => $token]);
        $this->postJson($url)->assertNoContent();
        $this->postJson($url)->assertNoContent();
        $selected = collect(app(TripSearchService::class)->search('HCM', 'Da Lat', now()->addDays(4)->toDateString(), 1))->firstWhere('id', $f['target']);
        $this->assertFalse(collect($selected['seats'])->firstWhere('id', $f['seat'])['sold']);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseHas('ticket_exchanges', ['token' => $token, 'status' => 'released']);
    }

    public function test_expired_hold_cannot_exchange_and_original_ticket_stays_valid(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        $this->travel(5)->minutes();
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertUnprocessable();
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseCount('tickets', 1);
        $this->assertFalse(app(ExchangeSeatHolds::class)->conflicts($f['target'], [$f['seat']]));
    }

    public function test_wrong_owner_inactive_account_and_forged_token_are_rejected(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        $other = $this->createTestUser(['is_active' => true]);
        $this->actingAs($other)->get(route('exchange.show', $f['booking']))->assertNotFound();
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertNotFound();
        $this->postJson(route('exchange.hold', $f['booking']), $this->input($f))->assertNotFound();
        $this->actingAs($f['user'])->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => (string) Str::uuid()]), ['consent' => true])->assertNotFound();
        $f['user']->update(['is_active' => false]);
        $this->get(route('exchange.show', $f['booking']))->assertForbidden();
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_wrong_route_class_fare_seat_and_unverified_payment_cannot_be_exchanged(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['user']);
        $url = route('exchange.hold', $f['booking']);
        $this->postJson($url, [...$this->input($f), 'seat_id' => 999999])->assertUnprocessable();
        $this->postJson($url, [...$this->input($f), 'pickup' => '999999'])->assertUnprocessable();
        DB::table('trips')->where('id', $f['target'])->update(['price' => 350001]);
        $this->postJson($url, [...$this->input($f), 'fare' => 350000, 'fee' => 0])->assertUnprocessable();
        $otherRoute = DB::table('routes')->insertGetId(['bus_company_id' => $f['company'], 'code' => 'REVERSE', 'name' => 'Reverse',
            'origin_city'                                                => 'Da Lat', 'destination_city' => 'HCM', 'base_price' => 350000]);
        DB::table('trips')->where('id', $f['target'])->update(['price' => 350000, 'route_id' => $otherRoute]);
        $this->postJson($url, $this->input($f))->assertUnprocessable();
        $otherBus = DB::table('buses')->insertGetId(['bus_company_id' => $f['company'], 'license_plate' => 'OTHER', 'capacity' => 3]);
        DB::table('trips')->where('id', $f['target'])->update(['route_id' => $f['route'], 'bus_id' => $otherBus]);
        $this->postJson($url, $this->input($f))->assertUnprocessable();
        DB::table('trips')->where('id', $f['target'])->update(['bus_id' => $f['bus']]);
        DB::table('sepay_transactions')->update(['status' => 'unmatched']);
        $this->postJson($url, $this->input($f))->assertUnprocessable();
        $this->assertDatabaseCount('ticket_exchanges', 0);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_revalidation_after_hold_never_changes_the_old_ticket_on_failure(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        DB::table('trips')->where('id', $f['target'])->update(['price' => 360000]);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertUnprocessable();
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_new_ticket_cannot_exchange_again_and_refund_uses_the_original_departure_deadline(): void
    {
        $f = $this->fixture();
        DB::table('trips')->where('id', $f['trip'])->update(['departure_time' => now()->addHours(12), 'arrival_time' => now()->addHours(20)]);
        $token = $this->hold($f);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $exchange = TicketExchange::first();
        $this->postJson(route('exchange.hold', $exchange->new_booking_id), [...$this->input($f), 'ticket_id' => $exchange->new_ticket_id])->assertUnprocessable();
        $this->postJson(route('cancellation.quote', $exchange->new_booking_id), ['ticket_id' => $exchange->new_ticket_id])
            ->assertOk()->assertJsonPath('quote.fee_percent', 30)->assertJsonPath('quote.refund', 245000);
        $this->postJson(route('cancellation.store', $exchange->new_booking_id), ['ticket_id' => $exchange->new_ticket_id,
            'expected_refund'                                                                => 245000, 'reason' => 'plans', 'consent' => true])->assertOk();
        $this->assertDatabaseHas('ticket_refunds', ['payment_id' => $exchange->payment_id, 'amount' => 245000, 'status' => 'refund_pending']);
        $this->assertDatabaseHas('payments', ['id' => $exchange->payment_id, 'status' => 'completed', 'amount' => 350000]);
    }

    public function test_exchanging_one_ticket_preserves_other_tickets_and_both_refunds_use_one_original_receipt(): void
    {
        $f = $this->fixture();
        $otherTicket = DB::table('tickets')->insertGetId(['ticket_code' => 'OTHER-TICKET', 'booking_id' => $f['booking'], 'trip_id' => $f['trip'],
            'seat_layout_id'                                            => $f['seat'], 'seat_code' => 'A02', 'passenger_name' => 'Other', 'price' => 350000]);
        DB::table('booked_seats')->insert(['booking_id' => $f['booking'], 'trip_id' => $f['trip'], 'seat_layout_id' => $f['seat'], 'seat_code' => 'A02', 'price' => 350000]);
        DB::table('bookings')->where('id', $f['booking'])->update(['seat_count' => 2, 'total_amount' => 700000]);
        DB::table('payments')->update(['amount' => 700000]);
        DB::table('sepay_transactions')->update(['amount' => 700000]);
        $token = $this->hold($f);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $exchange = TicketExchange::first();
        $this->assertDatabaseHas('bookings', ['id' => $f['booking'], 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        $this->assertDatabaseHas('tickets', ['id' => $otherTicket, 'status' => 'active']);
        $this->assertDatabaseHas('booked_seats', ['booking_id' => $f['booking'], 'seat_layout_id' => $f['seat']]);
        foreach ([[$f['booking'], $otherTicket], [$exchange->new_booking_id, $exchange->new_ticket_id]] as [$booking, $ticket]) {
            $this->postJson(route('cancellation.store', $booking), ['ticket_id' => $ticket, 'expected_refund' => 315000,
                'reason'                                                        => 'plans', 'consent' => true])->assertOk();
        }
        $this->assertSame(630000, (int) DB::table('ticket_refunds')->sum('amount'));
        $this->assertSame(1, DB::table('ticket_refunds')->distinct()->count('payment_id'));
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_a_normal_checkout_hold_blocks_exchange_and_duplicate_exchange_holds_do_not_extend_the_deadline(): void
    {
        $f = $this->fixture();
        $preview = ['token' => (string) Str::uuid(), 'trip' => ['id' => $f['target'], 'fare' => 350000], 'seat_ids' => [$f['seat']]];
        app(SePayIntentService::class)->createIntent($preview);
        $this->actingAs($f['user'])->postJson(route('exchange.hold', $f['booking']), $this->input($f))->assertUnprocessable();
        DB::table('sepay_payment_intents')->update(['status' => 'expired']);
        $token = $this->hold($f);
        $deadline = TicketExchange::first()->expires_at;
        $this->travel(1)->minutes();
        $this->assertSame($token, $this->hold($f));
        $this->assertTrue(TicketExchange::first()->expires_at->eq($deadline));
        $this->assertDatabaseCount('ticket_exchanges', 1);
        $this->postJson(route('exchange.hold', $f['booking']), [...$this->input($f), 'seat_id' => $f['oldSeat']])->assertUnprocessable();
    }

    public function test_cancelling_the_old_ticket_releases_exchange_hold_and_prevents_completion(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        $this->postJson(route('cancellation.store', $f['booking']), ['ticket_id' => $f['ticket'], 'expected_refund' => 315000,
            'reason'                                                             => 'plans', 'consent' => true])->assertOk();
        $this->assertDatabaseHas('ticket_exchanges', ['token' => $token, 'status' => 'released']);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertUnprocessable();
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_issue_failure_rolls_back_booking_ticket_and_seat_changes(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        DB::listen(static function (QueryExecuted $query): void {
            $table = $query->connection->getQueryGrammar()->wrapTable('tickets');
            if (str_starts_with($query->sql, 'insert into '.$table.' ')) {
                throw new RuntimeException('Simulated issue failure');
            }
        });
        try {
            app(TicketExchangeService::class)->complete($f['user'], $f['booking'], $token);
            $this->fail('The issue failure must roll back the exchange.');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulated issue failure', $error->getMessage());
            $this->assertDatabaseCount('bookings', 1);
            $this->assertDatabaseCount('tickets', 1);
            $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
            $this->assertDatabaseHas('ticket_exchanges', ['token' => $token, 'status' => 'held', 'new_ticket_id' => null]);
            $this->assertDatabaseCount('booked_seats', 1);
            $this->assertDatabaseCount('payments', 1);
        }
    }

    public function test_authorized_staff_uses_the_same_flow_and_cross_company_staff_cannot_access_it(): void
    {
        $f = $this->fixture();
        $staff = $this->createTestUser(['bus_company_id' => $f['company'], 'is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff', 'bus_company_id' => $f['company']]);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $role]);
        $permission = DB::table('permissions')->insertGetId(['slug' => 'ticket.view', 'name' => 'View tickets', 'group' => 'ticket', 'is_active' => true]);
        DB::table('permission_role')->insert(['permission_id' => $permission, 'role_id' => $role]);
        $this->actingAs($staff)->get(route('staff.exchange.manage'))->assertForbidden();
        $this->getJson(route('staff.tickets.exchange.context', $f['ticket']))->assertForbidden();
        $this->get(route('staff.tickets.exchange'))->assertForbidden();
        $this->get(route('exchange.show', $f['booking']))->assertNotFound();
        DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => DB::table('permissions')->where('slug', 'ticket.exchange')->value('id')]);
        $this->get(route('staff.tickets.exchange'))->assertOk()->assertSee('staffTicketExchange', false);
        $this->getJson(route('staff.tickets.exchange.context', $f['ticket']))->assertOk()
            ->assertJsonPath('ticket.booking_id', $f['booking'])->assertJsonPath('ticket.fare', 350000);
        $tripUrl = route('staff.tickets.exchange.trips', ['ticket' => $f['ticket'], 'date' => now()->addDays(4)->toDateString()]);
        $this->getJson($tripUrl)->assertOk()->assertJsonCount(1, 'trips')->assertJsonPath('trips.0.id', $f['target'])
            ->assertJsonPath('trips.0.fare', 350000)->assertJsonStructure(['html', 'trips' => [['seats', 'stops']]]);
        DB::table('trips')->where('id', $f['target'])->update(['price' => 400000]);
        $this->getJson($tripUrl)->assertOk()->assertJsonCount(0, 'trips');
        DB::table('trips')->where('id', $f['target'])->update(['price' => 350000]);
        $this->getJson(route('staff.tickets.exchange.trips', ['ticket' => $f['ticket'], 'date' => 'invalid']))->assertUnprocessable();
        $this->get(route('staff.exchange.manage', ['code' => 'TICKET']))->assertOk()->assertSee('data-ticket-exchange', false);
        $token = $this->postJson(route('exchange.hold', $f['booking']), $this->input($f))->assertOk()->json('token');
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $this->assertDatabaseHas('ticket_exchanges', ['actor_id' => $staff->id, 'user_id' => $f['user']->id, 'status' => 'completed']);
        $foreignCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $staff->update(['bus_company_id' => $foreignCompany]);
        $this->get(route('exchange.show', $f['booking']))->assertNotFound();
        $this->getJson(route('staff.tickets.exchange.context', $f['ticket']))->assertForbidden();
    }

    public function test_round_trip_exchange_cannot_overlap_the_other_leg(): void
    {
        $f = $this->fixture();
        $otherTrip = DB::table('trips')->insertGetId(['route_id' => $f['route'], 'bus_id' => $f['bus'], 'bus_company_id' => $f['company'],
            'departure_time'                                     => now()->addDays(4)->addHours(2), 'arrival_time' => now()->addDays(4)->addHours(10), 'price' => 350000]);
        $otherBooking = DB::table('bookings')->insertGetId(['booking_code' => 'RETURN', 'trip_id' => $otherTrip, 'user_id' => $f['user']->id,
            'customer_id'                                                  => DB::table('bookings')->where('id', $f['booking'])->value('customer_id'), 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        DB::table('tickets')->insert(['ticket_code' => 'RETURN01', 'booking_id' => $otherBooking, 'trip_id' => $otherTrip,
            'seat_layout_id'                        => $f['oldSeat'], 'seat_code' => 'A01', 'passenger_name' => 'Test', 'price' => 350000]);
        DB::table('sepay_payment_intents')->insert(['token' => (string) Str::uuid(), 'code' => 'PAY', 'trip_id' => $f['trip'],
            'secondary_trip_id'                             => $otherTrip, 'seat_ids' => '[]', 'secondary_seat_ids' => '[]', 'snapshot' => '{}', 'amount' => 700000,
            'status'                                        => 'paid', 'booking_id' => $f['booking'], 'secondary_booking_id' => $otherBooking, 'expires_at' => now()->addDays(1)]);
        DB::table('payments')->update(['amount' => 700000]);
        DB::table('sepay_transactions')->update(['amount' => 700000]);
        $this->actingAs($f['user'])->postJson(route('exchange.hold', $f['booking']), $this->input($f))
            ->assertUnprocessable()->assertJsonValidationErrors('trip_id');
        $this->assertDatabaseCount('ticket_exchanges', 0);
        DB::table('trips')->where('id', $f['target'])->update(['departure_time' => now()->addDays(3)->addHours(10),
            'arrival_time'                                                      => now()->addDays(3)->addHours(18)]);
        $token = $this->hold($f);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $otherBooking, 'status' => 'confirmed', 'seat_count' => 1]);
        $this->assertDatabaseHas('tickets', ['ticket_code' => 'RETURN01', 'status' => 'active', 'trip_id' => $otherTrip]);
    }

    public function test_cash_counter_checkout_cannot_sell_an_exchange_held_seat(): void
    {
        $f = $this->fixture();
        $this->hold($f);
        $operator = $this->createTestUser(['bus_company_id' => $f['company'], 'is_active' => true]);
        $preview = ['created_at' => now()->timestamp, 'trip' => ['id' => $f['target'], 'fare' => 350000], 'seat_ids' => [$f['seat']]];
        try {
            app(CashSaleService::class)->collect($operator, (string) Str::uuid(), $preview);
            $this->fail('Counter checkout must respect the exchange hold.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('seats', $error->errors());
        }
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_verified_counter_cash_receipt_can_be_transferred_and_refunded_without_a_new_payment(): void
    {
        $f = $this->fixture();
        DB::table('payments')->update(['payment_code' => 'CASH-TEST', 'method' => 'cash', 'transaction_id' => null, 'paid_at' => now(),
            'payload'                                 => json_encode(['operator_id' => 123, 'booking_ids' => [$f['booking']]])]);
        $token = $this->hold($f);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $exchange = TicketExchange::first();
        $this->postJson(route('cancellation.store', $exchange->new_booking_id), ['ticket_id' => $exchange->new_ticket_id,
            'expected_refund'                                                                => 315000, 'reason' => 'plans', 'consent' => true])->assertOk();
        $this->assertDatabaseHas('ticket_refunds', ['payment_id' => $exchange->payment_id, 'transaction_id' => 'CASH-TEST', 'amount' => 315000]);
        $this->assertDatabaseCount('payments', 1);
        Http::assertNothingSent();
    }

    public function test_a_receipt_smaller_than_the_issued_ticket_value_cannot_fund_an_exchange(): void
    {
        $f = $this->fixture();
        DB::table('payments')->update(['amount' => 1000]);
        DB::table('sepay_transactions')->update(['amount' => 1000]);
        $this->actingAs($f['user'])->postJson(route('exchange.hold', $f['booking']), $this->input($f))->assertUnprocessable();
        $this->assertDatabaseCount('ticket_exchanges', 0);
    }

    public function test_disabled_stops_are_not_offered_and_are_revalidated_at_confirmation(): void
    {
        $f = $this->fixture();
        $pickup = DB::table('route_stops')->insertGetId(['route_id' => $f['route'], 'name' => 'Origin', 'stop_order' => 1,
            'offset_minutes'                                        => 0, 'stop_type' => 'both', 'status' => 'active']);
        $dropoff = DB::table('route_stops')->insertGetId(['route_id' => $f['route'], 'name' => 'Destination', 'stop_order' => 3,
            'offset_minutes'                                         => 480, 'stop_type' => 'both', 'status' => 'active']);
        DB::table('route_stops')->insert(['route_id' => $f['route'], 'name' => 'Temporarily closed stop', 'stop_order' => 2,
            'offset_minutes'                         => 120, 'stop_type' => 'both', 'status' => 'inactive']);
        $tripResponse = $this->actingAs($f['user'])->getJson(route('exchange.trips', [
            'booking' => $f['booking'], 'ticket_id' => $f['ticket'], 'date' => now()->addDays(4)->toDateString(),
        ]))->assertOk();
        $this->assertFalse($tripResponse->json('trips.0.timeline.1.active'));
        $this->assertSame('Temporarily closed stop', $tripResponse->json('trips.0.timeline.1.name'));
        $this->assertStringContainsString('google.com/maps', $tripResponse->json('trips.0.timeline.1.map_url'));
        $input = [...$this->input($f), 'pickup' => (string) $pickup, 'dropoff' => (string) $dropoff];
        $this->actingAs($f['user']);
        $this->postJson(route('exchange.hold', $f['booking']), [...$input, 'pickup' => (string) $dropoff, 'dropoff' => (string) $pickup])
            ->assertUnprocessable()->assertJsonValidationErrors('dropoff');
        $token = $this->postJson(route('exchange.hold', $f['booking']), $input)->assertOk()->json('token');
        DB::table('route_stops')->where('id', $pickup)->update(['status' => 'inactive']);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('pickup');
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->postJson(route('exchange.release', ['booking' => $f['booking'], 'token' => $token]))->assertNoContent();
        DB::table('route_stops')->where('id', $dropoff)->update(['status' => 'inactive']);
        $this->getJson(route('exchange.trips', ['booking' => $f['booking'], 'ticket_id' => $f['ticket'], 'date' => now()->addDays(4)->toDateString()]))
            ->assertOk()->assertJsonPath('trips', []);
        $this->postJson(route('exchange.hold', $f['booking']), $this->input($f))->assertUnprocessable();
    }

    public function test_cash_round_trip_secondary_booking_keeps_its_verified_original_payment(): void
    {
        $f = $this->fixture();
        $returnTrip = DB::table('trips')->insertGetId(['route_id' => $f['route'], 'bus_id' => $f['bus'], 'bus_company_id' => $f['company'],
            'departure_time'                                      => now()->addDays(5), 'arrival_time' => now()->addDays(5)->addHours(8), 'price' => 350000]);
        $returnBooking = DB::table('bookings')->insertGetId(['booking_code' => 'CASHRETURN', 'trip_id' => $returnTrip, 'user_id' => $f['user']->id,
            'customer_id'                                                   => DB::table('bookings')->where('id', $f['booking'])->value('customer_id'), 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed']);
        $returnTicket = DB::table('tickets')->insertGetId(['ticket_code' => 'CASHRETURN01', 'booking_id' => $returnBooking, 'trip_id' => $returnTrip,
            'seat_layout_id'                                             => $f['oldSeat'], 'seat_code' => 'A01', 'passenger_name' => 'Test', 'price' => 350000]);
        DB::table('payments')->update(['payment_code' => 'CASH-ROUND', 'method' => 'cash', 'transaction_id' => null, 'paid_at' => now(), 'amount' => 700000,
            'payload'                                 => json_encode(['operator_id' => 123, 'booking_ids' => [$f['booking'], $returnBooking]])]);
        $this->assertSame('completed', app(TicketHistoryService::class)->findForUser($f['user'], $returnBooking)->payment_status);
        $token = $this->hold([...$f, 'booking' => $returnBooking, 'ticket' => $returnTicket, 'trip' => $returnTrip]);
        $this->postJson(route('exchange.complete', ['booking' => $returnBooking, 'token' => $token]), ['consent' => true])->assertOk();
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
    }

    public function test_cash_history_matches_only_numeric_booking_ids_in_the_receipt(): void
    {
        $f = $this->fixture();
        $bookings = [];
        foreach (['RELATED', 'UNRELATED', 'STRING-ID'] as $code) {
            $bookings[$code] = DB::table('bookings')->insertGetId([
                'booking_code' => $code, 'trip_id' => $f['trip'], 'user_id' => $f['user']->id,
                'customer_id'  => DB::table('bookings')->where('id', $f['booking'])->value('customer_id'),
                'seat_count'   => 1, 'total_amount' => 350000, 'status' => 'confirmed',
            ]);
        }
        DB::table('payments')->update([
            'payment_code' => 'CASH-HISTORY', 'method' => 'cash', 'status' => 'completed',
            'payload'      => json_encode(['booking_ids' => [$f['booking'], $bookings['RELATED'], (string) $bookings['STRING-ID']]]),
        ]);

        $history = app(TicketHistoryService::class);
        $this->assertSame('completed', $history->findForUser($f['user'], $bookings['RELATED'])->payment_status);
        $this->assertNull($history->findForUser($f['user'], $bookings['UNRELATED'])->payment_status);
        $this->assertNull($history->findForUser($f['user'], $bookings['STRING-ID'])->payment_status);
    }

    public function test_management_opens_with_upcoming_tickets_without_a_search(): void
    {
        $f = $this->fixture();
        $admin = $this->exchangeOperator($f, 'admin');
        $this->actingAs($admin)->get(route('exchange.manage'))->assertOk()
            ->assertSee('data-exchange-ticket="'.$f['ticket'].'"', false)
            ->assertViewHas('records', fn ($records) => $records->total() === 1 && $records->first()->eligible);
        $this->get(route('exchange.manage', ['code' => 'MISSING']))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->isEmpty())
            ->assertSee('data-empty-state', false);
        $this->assertDatabaseCount('ticket_exchanges', 0);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_management_exact_ticket_search_returns_the_requested_ticket_in_a_multi_ticket_booking(): void
    {
        $f = $this->fixture();
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => 'SECOND-TICKET', 'booking_id' => $f['booking'],
            'trip_id'                                              => $f['trip'], 'seat_layout_id' => $f['seat'], 'seat_code' => 'A02', 'passenger_name' => 'Second', 'price' => 350000]);
        DB::table('payments')->update(['amount' => 700000]);
        DB::table('sepay_transactions')->update(['amount' => 700000]);
        $this->actingAs($this->exchangeOperator($f, 'admin'))
            ->get(route('exchange.manage', ['code' => 'SECOND-TICKET']))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 1 && (int) $records->first()->id === $ticket);
        $this->get(route('exchange.manage', ['code' => 'TEST']))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 2);
    }

    public function test_management_lists_and_exchange_history_respect_company_scope(): void
    {
        $f = $this->fixture();
        $token = $this->hold($f);
        $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $exchange = TicketExchange::first();
        $newCode = DB::table('tickets')->where('id', $exchange->new_ticket_id)->value('ticket_code');
        $staff = $this->exchangeOperator($f, 'staff');
        $this->actingAs($staff)->get(route('staff.exchange.manage', ['tab' => 'history']))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 1 && $records->first()->new_code === $newCode);
        $this->get(route('staff.exchange.manage', ['tab' => 'history', 'code' => $newCode]))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 1);
        $this->get(route('staff.exchange.manage'))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 1 && ! $records->first()->eligible);
        $other = DB::table('bus_companies')->insertGetId(['name' => 'Other', 'code' => 'FOREIGN']);
        $staff->update(['bus_company_id' => $other]);
        DB::table('roles')->where('slug', 'staff')->update(['bus_company_id' => $other]);
        foreach (['tickets', 'history'] as $tab) {
            $this->get(route('staff.exchange.manage', ['tab' => $tab]))->assertOk()
                ->assertViewHas('records', fn ($records) => $records->isEmpty());
            $this->get(route('staff.exchange.manage', ['tab' => $tab, 'code' => $newCode]))->assertOk()
                ->assertViewHas('records', fn ($records) => $records->isEmpty());
        }
        $this->actingAs($f['user'])->get(route('exchange.manage'))->assertForbidden();
    }

    public function test_staff_exchange_changes_only_replacement_passenger_and_preserves_payment_and_audit(): void
    {
        $f = $this->fixture();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $staff = $this->exchangeOperator($f, 'staff');
        $payload = [...$this->input($f), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '+84912345678'];
        $url = route('staff.tickets.exchange.hold', $f['ticket']);
        $payment = (array) DB::table('payments')->first();
        $profile = (array) DB::table('customers')->first();
        $this->actingAs($staff)->get(route('staff.tickets.exchange'))->assertOk()->assertSee('name="passenger_name"', false);
        $hold = $this->postJson($url, $payload)->assertOk()->assertJsonPath('passenger.passenger_name', 'Updated Passenger');
        $token = $hold->json('token');
        $originalHold = TicketExchange::where('token', $token)->firstOrFail();
        $originalExpiry = $originalHold->expires_at->toIso8601String();
        // Reproduce MySQL's JSON object key reordering even when this test runs on SQLite.
        $originalHold->update(['snapshot' => Arr::sortRecursive($originalHold->snapshot)]);
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('token', $token);
        $this->assertSame($originalExpiry, $originalHold->fresh()->expires_at->toIso8601String());
        $this->assertDatabaseCount('ticket_exchanges', 1);
        $this->postJson($url, [...$payload, 'passenger_name' => 'Different Passenger'])->assertUnprocessable()->assertJsonValidationErrors('ticket');
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active', 'passenger_name' => 'Test']);
        $exchange = TicketExchange::where('token', $token)->firstOrFail();
        $this->assertSame(['passenger_name' => 'Test', 'passenger_phone' => null], $exchange->snapshot['passenger']['before']);
        $this->assertSame($staff->id, $exchange->snapshot['passenger']['actor_id']);
        $complete = route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]);
        $response = $this->postJson($complete, ['consent' => true])->assertOk();
        $this->postJson($complete, ['consent' => true])->assertOk()->assertJsonPath('code', $response->json('code'));
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 2);
        $this->assertSame($payment, (array) DB::table('payments')->first());
        $this->assertSame($profile, (array) DB::table('customers')->first());
        $this->assertDatabaseHas('tickets', ['ticket_code' => $response->json('code'), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '+84912345678', 'status' => 'active']);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'passenger_name' => 'Test', 'passenger_phone' => null, 'status' => 'cancelled']);
        $this->assertDatabaseMissing('booked_seats', ['trip_id' => $f['trip'], 'seat_layout_id' => $f['oldSeat']]);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $f['target'], 'seat_layout_id' => $f['seat']]);
        $this->get(route('staff.exchange.manage', ['tab' => 'history']))->assertOk()->assertSeeText($response->json('code'));
        Http::assertNothingSent();
    }

    public function test_staff_passenger_validation_does_not_hold_a_seat_or_modify_the_original(): void
    {
        $f = $this->fixture();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $staff = $this->exchangeOperator($f, 'staff');
        $this->actingAs($staff);
        $url = route('staff.tickets.exchange.hold', $f['ticket']);
        foreach ([['passenger_name' => '', 'passenger_phone' => '0912345678'],
            ['passenger_name' => str_repeat('x', 256), 'passenger_phone' => '0912345678'],
            ['passenger_name' => 'Passenger', 'passenger_phone' => '123'],
            ['passenger_name' => 'Passenger', 'passenger_phone' => ''],
        ] as $invalid) {
            $this->postJson($url, [...$this->input($f), ...$invalid])->assertUnprocessable();
        }
        $this->postJson($url, [...$this->input($f), 'ticket_id' => $f['ticket'] + 999,
            'passenger_name' => 'Passenger', 'passenger_phone' => '0912345678'])->assertUnprocessable()->assertJsonValidationErrors('ticket_id');
        $this->assertDatabaseCount('ticket_exchanges', 0);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active', 'passenger_name' => 'Test']);
    }

    public function test_customer_exchange_keeps_original_passenger_and_cannot_use_staff_endpoints(): void
    {
        $f = $this->fixture();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $payload = [...$this->input($f), 'passenger_name' => 'Injected', 'passenger_phone' => '0912345678'];
        $this->actingAs($f['user'])->postJson(route('staff.tickets.exchange.hold', $f['ticket']), $payload)->assertForbidden();
        $token = $this->postJson(route('exchange.hold', $f['booking']), $payload)->assertOk()->json('token');
        $this->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertForbidden();
        $this->postJson(route('staff.tickets.exchange.release', ['booking' => $f['booking'], 'token' => $token]))->assertForbidden();
        $result = $this->postJson(route('exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $this->assertDatabaseHas('tickets', ['ticket_code' => $result->json('code'), 'passenger_name' => 'Test', 'passenger_phone' => null]);
        $this->assertArrayNotHasKey('passenger', TicketExchange::where('token', $token)->firstOrFail()->snapshot);
    }

    public function test_staff_from_another_company_cannot_hold_an_edited_passenger_exchange(): void
    {
        $f = $this->fixture();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $other = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $staff = $this->exchangeOperator([...$f, 'company' => $other], 'staff');
        $this->actingAs($staff)->postJson(route('staff.tickets.exchange.hold', $f['ticket']),
            [...$this->input($f), 'passenger_name' => 'Passenger', 'passenger_phone' => '0912345678'])->assertNotFound();
        $this->assertDatabaseCount('ticket_exchanges', 0);
    }

    public function test_changing_original_passenger_after_hold_prevents_completion(): void
    {
        $f = $this->fixture();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $staff = $this->exchangeOperator($f, 'staff');
        $token = $this->actingAs($staff)->postJson(route('staff.tickets.exchange.hold', $f['ticket']),
            [...$this->input($f), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '0912345678'])->assertOk()->json('token');
        DB::table('tickets')->where('id', $f['ticket'])->update(['passenger_name' => 'Changed elsewhere']);
        $this->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('ticket');
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $f['trip'], 'seat_layout_id' => $f['oldSeat']]);
    }

    public function test_revoking_exchange_permission_after_hold_blocks_passenger_edit_even_for_the_owner(): void
    {
        $f = $this->fixture();
        $staff = $this->exchangeOperator($f, 'staff');
        DB::table('bookings')->where('id', $f['booking'])->update(['user_id' => $staff->id]);
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $token = $this->actingAs($staff)->postJson(route('staff.tickets.exchange.hold', $f['ticket']),
            [...$this->input($f), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '0912345678'])->assertOk()->json('token');
        $permission = DB::table('permissions')->where('slug', 'ticket.exchange')->value('id');
        DB::table('permission_role')->where('permission_id', $permission)->delete();
        $this->actingAs($staff->fresh())->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])
            ->assertForbidden();
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active', 'passenger_name' => 'Test']);
    }

    public function test_staff_hold_requires_csrf_token_before_creating_an_exchange(): void
    {
        $f = $this->fixture();
        $staff = $this->exchangeOperator($f, 'staff');
        $this->actingAs($staff)->postJson(route('staff.tickets.exchange.hold', $f['ticket']),
            [...$this->input($f), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '0912345678'])->assertStatus(419);
        $this->assertDatabaseCount('ticket_exchanges', 0);
    }

    public function test_staff_context_uses_separate_urls_and_release_preserves_the_original_ticket(): void
    {
        $f = $this->fixture();
        $staff = $this->exchangeOperator($f, 'staff');
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $context = $this->actingAs($staff)->getJson(route('staff.tickets.exchange.context', $f['ticket']))->assertOk();
        $this->assertSame(url('/nhan-vien/doi-ve/dat-cho/'.$f['booking']), $context->json('urls.base'));
        $token = $this->postJson(route('staff.tickets.exchange.hold', $f['ticket']),
            [...$this->input($f), 'passenger_name' => 'Updated Passenger', 'passenger_phone' => '0912345678'])->assertOk()->json('token');
        $this->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('consent');
        $this->postJson(route('staff.tickets.exchange.release', ['booking' => $f['booking'], 'token' => $token]))->assertNoContent();
        $this->assertDatabaseHas('ticket_exchanges', ['token' => $token, 'status' => 'released']);
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active', 'passenger_name' => 'Test']);
        $this->assertDatabaseHas('booked_seats', ['trip_id' => $f['trip'], 'seat_layout_id' => $f['oldSeat']]);
    }

    public function test_staff_exchange_validates_and_persists_transfer_addresses_on_the_replacement(): void
    {
        $f = $this->fixture();
        $staff = $this->exchangeOperator($f, 'staff');
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $this->actingAs($staff);
        $url = route('staff.tickets.exchange.hold', $f['ticket']);
        $payload = [...$this->input($f), 'passenger_name' => 'Passenger', 'passenger_phone' => '0912345678',
            'pickup_mode'    => 'transfer', 'dropoff_mode' => 'transfer',
            'pickup_address' => '', 'dropoff_address' => '20 Arrival Street'];
        $this->postJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('pickup_address');
        $this->assertDatabaseCount('ticket_exchanges', 0);
        $payload['pickup_address'] = '10 Departure Street';
        $token = $this->postJson($url, $payload)->assertOk()->json('token');
        $this->postJson($url, [...$payload, 'pickup_address' => 'Changed address'])->assertUnprocessable();
        $this->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertOk();
        $exchange = TicketExchange::where('token', $token)->firstOrFail();
        $notes = json_decode(DB::table('bookings')->where('id', $exchange->new_booking_id)->value('notes'), true);
        $this->assertSame('10 Departure Street', $notes['pickup']['address']);
        $this->assertSame('20 Arrival Street', $notes['dropoff']['address']);
        $this->assertSame('transfer', $notes['pickup']['mode']);
        $this->assertSame('10 Departure Street', $exchange->snapshot['pickup']['address']);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'passenger_name' => 'Test']);
    }

    public function test_staff_can_exchange_a_walk_in_ticket_without_assigning_it_to_an_employee_account(): void
    {
        $f = $this->fixture();
        DB::table('bookings')->where('id', $f['booking'])->update(['user_id' => null]);
        $customer = DB::table('bookings')->where('id', $f['booking'])->value('customer_id');
        DB::table('customers')->where('id', $customer)->update(['user_id' => null]);
        $staff = $this->exchangeOperator($f, 'staff');
        $payment = (array) DB::table('payments')->first();
        $profile = (array) DB::table('customers')->where('id', $customer)->first();
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $payload = [...$this->input($f), 'passenger_name' => 'Walk-in Passenger', 'passenger_phone' => '0912345678'];
        $this->actingAs($f['user'])->postJson(route('exchange.hold', $f['booking']), $payload)->assertNotFound();
        $url = route('staff.tickets.exchange.hold', $f['ticket']);
        $token = $this->actingAs($staff)->postJson($url, $payload)->assertOk()->json('token');
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('token', $token);
        $this->assertDatabaseHas('ticket_exchanges', ['token' => $token, 'user_id' => null, 'actor_id' => $staff->id, 'status' => 'held']);
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $complete = route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]);
        $response = $this->postJson($complete, ['consent' => true])->assertOk();
        $this->postJson($complete, ['consent' => true])->assertOk()->assertJsonPath('code', $response->json('code'));
        $exchange = TicketExchange::where('token', $token)->firstOrFail();
        $this->assertDatabaseHas('bookings', ['id' => $exchange->new_booking_id, 'user_id' => null, 'customer_id' => $customer]);
        $this->assertDatabaseHas('tickets', ['id' => $exchange->new_ticket_id, 'passenger_name' => 'Walk-in Passenger', 'status' => 'active']);
        $this->assertSame($payment, (array) DB::table('payments')->first());
        $this->assertSame($profile, (array) DB::table('customers')->where('id', $customer)->first());
        $this->assertDatabaseCount('tickets', 2);
        $this->get(route('staff.exchange.manage', ['tab' => 'history']))->assertOk()->assertSeeText($response->json('code'));
        $this->assertSame($payment['id'], app(BookingPaymentResolver::class)->verified((int) $exchange->new_booking_id)->id);
        Http::assertNothingSent();
    }

    public function test_guest_staff_exchange_still_requires_permission_and_matching_company(): void
    {
        $f = $this->fixture();
        DB::table('bookings')->where('id', $f['booking'])->update(['user_id' => null]);
        DB::table('customers')->update(['user_id' => null]);
        $other = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $outsider = $this->exchangeOperator([...$f, 'company' => $other], 'staff');
        $this->withSession(['_token' => 'staff-exchange-token'])->withHeader('X-CSRF-TOKEN', 'staff-exchange-token');
        $payload = [...$this->input($f), 'passenger_name' => 'Walk-in Passenger', 'passenger_phone' => '0912345678'];
        $this->actingAs($outsider)->postJson(route('staff.tickets.exchange.hold', $f['ticket']), $payload)->assertNotFound();
        $this->assertDatabaseCount('ticket_exchanges', 0);
        $staff = $this->exchangeOperator($f, 'staff');
        $token = $this->actingAs($staff)->postJson(route('staff.tickets.exchange.hold', $f['ticket']), $payload)->assertOk()->json('token');
        $permission = DB::table('permissions')->where('slug', 'ticket.exchange')->value('id');
        DB::table('permission_role')->where('permission_id', $permission)->delete();
        $this->postJson(route('staff.tickets.exchange.complete', ['booking' => $f['booking'], 'token' => $token]), ['consent' => true])->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $f['ticket'], 'status' => 'active']);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_staff_lookup_reports_the_specific_reason_a_ticket_cannot_be_exchanged(): void
    {
        $f = $this->fixture();
        $staff = $this->exchangeOperator($f, 'staff');
        $this->actingAs($staff);
        $url = route('staff.tickets.exchange.context', $f['ticket']);
        DB::table('tickets')->where('id', $f['ticket'])->update(['status' => 'cancelled']);
        $this->getJson($url)->assertUnprocessable()
            ->assertJsonPath('errors.ticket.0', __('StaffTicketProcessing::exchange.rejection.cancelled'));
        DB::table('tickets')->where('id', $f['ticket'])->update(['status' => 'used']);
        $this->getJson($url)->assertUnprocessable()
            ->assertJsonPath('errors.ticket.0', __('StaffTicketProcessing::exchange.rejection.used'));
        DB::table('tickets')->where('id', $f['ticket'])->update(['status' => 'active']);
        DB::table('trips')->where('id', $f['trip'])->update(['departure_time' => now()->addHours(3)]);
        $this->getJson($url)->assertUnprocessable()
            ->assertJsonPath('errors.ticket.0', __('StaffTicketProcessing::exchange.rejection.too_late'));
    }

    private function exchangeOperator(array $f, string $slug): User
    {
        $operator = $this->createTestUser(['bus_company_id' => $f['company'], 'is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => $slug, 'slug' => $slug, 'bus_company_id' => $f['company']]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $operator->id]);
        foreach (['ticket.view', 'ticket.exchange'] as $slug) {
            $permission = DB::table('permissions')->where('slug', $slug)->value('id')
                ?: DB::table('permissions')->insertGetId(['name' => $slug, 'slug' => $slug, 'group' => 'ticket', 'is_active' => true]);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        }

        return $operator;
    }

    private function hold(array $fixture): string
    {
        return $this->actingAs($fixture['user'])->postJson(route('exchange.hold', $fixture['booking']), $this->input($fixture))
            ->assertOk()->assertJsonPath('fee', 0)->json('token');
    }

    private function input(array $f): array
    {
        return ['ticket_id' => $f['ticket'], 'trip_id' => $f['target'], 'seat_id' => $f['seat'], 'pickup' => 'pickup', 'dropoff' => 'dropoff'];
    }

    private function fixture(): array
    {
        $user = $this->createTestUser(['is_active' => true]);
        $company = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FUTA']);
        $bus = DB::table('buses')->insertGetId(['bus_company_id' => $company, 'license_plate' => 'TEST', 'capacity' => 2, 'seat_rows' => 1]);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => 'TEST', 'name' => 'Route',
            'origin_city'                                           => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000]);
        $trip = DB::table('trips')->insertGetId(['route_id' => $route, 'bus_id' => $bus, 'bus_company_id' => $company,
            'departure_time'                                => now()->addDays(3), 'arrival_time' => now()->addDays(3)->addHours(8), 'price' => 350000]);
        $target = DB::table('trips')->insertGetId(['route_id' => $route, 'bus_id' => $bus, 'bus_company_id' => $company,
            'departure_time'                                  => now()->addDays(4), 'arrival_time' => now()->addDays(4)->addHours(8), 'price' => 350000]);
        $customer = DB::table('customers')->insertGetId(['user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0900000000']);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => 'TEST', 'trip_id' => $trip, 'user_id' => $user->id,
            'customer_id'                                             => $customer, 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed', 'created_at' => now(), 'updated_at' => now()]);
        $oldSeat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1]);
        $seat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A02', 'row_number' => 1, 'column_number' => 2]);
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => 'TICKET', 'booking_id' => $booking, 'trip_id' => $trip,
            'seat_layout_id'                                       => $oldSeat, 'seat_code' => 'A01', 'passenger_name' => 'Test', 'price' => 350000]);
        DB::table('booked_seats')->insert(['booking_id' => $booking, 'trip_id' => $trip, 'seat_layout_id' => $oldSeat, 'seat_code' => 'A01', 'price' => 350000]);
        DB::table('payments')->insert(['payment_code' => 'PAY', 'booking_id' => $booking, 'amount' => 350000,
            'method'                                  => 'bank_transfer', 'status' => 'completed', 'transaction_id' => '12345']);
        DB::table('sepay_transactions')->insert(['sepay_id' => 12345, 'code' => 'PAY', 'amount' => 350000, 'status' => 'matched', 'environment' => 'sandbox', 'payload' => '{}']);

        return compact('user', 'company', 'bus', 'route', 'trip', 'target', 'booking', 'oldSeat', 'seat', 'ticket');
    }
}

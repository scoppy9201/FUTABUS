<?php

namespace Tests\Feature\BookingTracking;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_staff_can_read_own_bookings_but_not_another_company(): void
    {
        [$company, $trip, $booking] = $this->fixture(1);
        [, , $foreign] = $this->fixture(2);
        foreach (['bus-company' => 'bookings', 'staff' => 'staff.bookings'] as $role => $routes) {
            $actor = $this->operator($company, $role);
            $this->actingAs($actor)->get(route($routes.'.index'))->assertOk()
                ->assertSee('BOOK-1')->assertDontSee('BOOK-2')->assertHeader('Cache-Control', 'no-store, private');
            $url = route($routes.'.show', ['kind' => 'booking', 'record' => $booking]);
            $this->get($url)->assertOk()->assertSee('TICKET-1')->assertSee('PAY-1')
                ->assertSee('HCM')->assertSee('Da Lat')->assertDontSee('private-provider-payload');
            $this->get(route($routes.'.show', ['kind' => 'booking', 'record' => $foreign]))->assertNotFound();
            $this->post($url)->assertMethodNotAllowed();
            $this->patch($url)->assertMethodNotAllowed();
            $this->delete($url)->assertMethodNotAllowed();
            if ($role === 'staff') {
                $this->get(route('bookings.index', ['search' => 'BOOK-1']))
                    ->assertRedirect(route('staff.bookings.index', ['search' => 'BOOK-1']));
            }
        }
        $this->assertDatabaseHas('bookings', ['id' => $booking, 'status' => 'confirmed']);
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('trips', ['id' => $trip, 'available_seats' => 20]);
    }

    public function test_customer_missing_permission_inactive_staff_and_unscoped_staff_are_blocked(): void
    {
        [$company, , $booking] = $this->fixture(1);
        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        foreach ([$this->createTestUser(), $this->operator($company, 'staff', false),
            $this->operator($company, 'staff', true, false), $this->operator(null, 'staff')] as $actor) {
            $this->actingAs($actor)->get(route('bookings.index'))->assertForbidden();
            $this->get(route('staff.bookings.show', ['kind' => 'booking', 'record' => $booking]))->assertForbidden();
        }
    }

    public function test_filters_pagination_and_invalid_input(): void
    {
        [$company] = $this->fixture(1);
        [, , $second] = $this->fixture(2, $company);
        DB::table('bookings')->where('id', $second)->update(['status' => 'cancelled']);
        $this->actingAs($this->operator($company, 'bus-company'));
        $this->get(route('bookings.index', ['search' => '0900000001']))->assertOk()->assertSee('BOOK-1')->assertDontSee('BOOK-2');
        $this->get(route('bookings.index', ['status' => 'cancelled']))->assertOk()->assertSee('BOOK-2')->assertDontSee('BOOK-1');
        $this->get(route('bookings.index', ['from' => now()->addDays(10)->toDateString()]))->assertOk()->assertDontSee('BOOK-1');
        $this->get(route('bookings.index', ['to' => now()->toDateString()]))->assertOk()->assertDontSee('BOOK-1');
        foreach ([['status' => 'bad'], ['page' => 0], ['search' => str_repeat('x', 101)],
            ['from' => 'bad'], ['from' => '2026-12-10', 'to' => '2026-12-01']] as $filters) {
            $this->getJson(route('bookings.index', $filters))->assertUnprocessable();
        }
        for ($i = 3; $i <= 17; $i++) {
            $this->fixture($i, $company);
        }
        $this->get(route('bookings.index'))->assertOk()->assertSee('data-operations-pagination', false)
            ->assertViewHas('records', fn ($records) => $records->count() === 15 && $records->total() === 17);
        $this->get(route('bookings.index', ['page' => 2]))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->count() === 2);
    }

    public function test_pending_expired_and_cross_company_round_trip_sessions_are_scoped_without_exposing_snapshot(): void
    {
        [$company, $trip] = $this->fixture(1);
        [$other, $returnTrip] = $this->fixture(2);
        $intent = $this->intent($trip, $returnTrip);
        $this->actingAs($this->operator($company, 'staff'));
        $this->get(route('staff.bookings.index'))->assertOk()->assertSee('FUTA1234567890');
        $url = route('staff.bookings.show', ['kind' => 'intent', 'record' => $intent]);
        $this->get($url)->assertOk()->assertSee('A1')->assertSee('350.000')
            ->assertDontSee('FOREIGN-SEAT')->assertDontSee('private-token')->assertDontSee('foreign-address');
        $this->get(route('staff.bookings.show', ['kind' => 'outbound', 'record' => $intent]))->assertNotFound();
        $this->get(route('staff.bookings.index', ['search' => 'foreign-address']))->assertOk()->assertDontSee('FUTA1234567890');
        $this->get(route('staff.bookings.index', ['search' => 'Pending Customer']))->assertOk()->assertSee('FUTA1234567890');
        DB::table('sepay_payment_intents')->where('id', $intent)->update(['expires_at' => now()->subMinute()]);
        $this->get(route('staff.bookings.index', ['status' => 'expired']))->assertOk()->assertSee('FUTA1234567890');
        $this->assertDatabaseHas('sepay_payment_intents', ['id' => $intent, 'status' => 'pending']);
        $this->actingAs($this->operator($other, 'bus-company'));
        $this->get($url)->assertForbidden();
        $this->get(route('bookings.show', ['kind' => 'outbound', 'record' => $intent]))->assertOk()
            ->assertSee('FOREIGN-SEAT')->assertDontSeeText('Ghế: A1');
    }

    public function test_paid_round_trip_deduplicates_sessions_and_shows_shared_payment_and_partial_refund(): void
    {
        [$company, $trip, $booking, $ticket] = $this->fixture(1);
        [, $returnTrip, $returnBooking] = $this->fixture(2, $company);
        DB::table('payments')->where('booking_id', $returnBooking)->delete();
        $intent = $this->intent($trip, $returnTrip);
        DB::table('sepay_payment_intents')->where('id', $intent)->update([
            'status' => 'paid', 'booking_id' => $booking, 'secondary_booking_id' => $returnBooking,
        ]);
        $user = $this->createTestUser();
        $cancellation = DB::table('ticket_cancellations')->insertGetId([
            'reference'    => 'CANCEL-1', 'ticket_id' => $ticket, 'user_id' => $user->id,
            'reason'       => 'plans', 'ticket_amount' => 350000, 'fee_percent' => 10,
            'fee_amount'   => 35000, 'refund_amount' => 315000, 'policy_version' => 'test',
            'departure_at' => now()->addDays(3), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $payment = DB::table('payments')->where('booking_id', $booking)->value('id');
        DB::table('ticket_refunds')->insert([
            'cancellation_id' => $cancellation, 'payment_id' => $payment, 'transaction_id' => '1',
            'idempotency_key' => (string) Str::uuid(), 'amount' => 315000, 'environment' => 'live',
            'processing_mode' => 'manual', 'status' => 'refund_pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($this->operator($company, 'bus-company'));
        $this->get(route('bookings.index'))->assertOk()->assertDontSee('FUTA1234567890')
            ->assertViewHas('records', fn ($records) => $records->total() === 2
                && $records->firstWhere('id', $returnBooking)->payment_status === 'completed');
        $this->get(route('bookings.show', ['kind' => 'booking', 'record' => $returnBooking]))->assertOk()->assertSee('PAY-1');
        $this->get(route('bookings.show', ['kind' => 'booking', 'record' => $booking]))->assertOk()
            ->assertSee('CANCEL-1')->assertSee('315.000')->assertSee(__('Payment::refunds.statuses.refund_pending'));
        $this->assertDatabaseHas('payments', ['id' => $payment, 'status' => 'completed', 'amount' => 350000]);
    }

    private function operator(?int $company, string $role, bool $permission = true, bool $active = true): User
    {
        $user = $this->createTestUser(['bus_company_id' => $company, 'is_active' => $active, 'email_verified_at' => now()]);
        $base = DB::table('roles')->where('slug', $role)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $role, 'slug' => $role]);
        DB::table('role_user')->insert(['role_id' => $base, 'user_id' => $user->id]);
        if ($permission) {
            $custom = DB::table('roles')->insertGetId(['name' => 'Reader '.$user->id, 'slug' => 'reader-'.$user->id, 'bus_company_id' => $company]);
            $perm = DB::table('permissions')->where('slug', 'booking.view')->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => 'View bookings', 'slug' => 'booking.view', 'group' => 'booking', 'is_active' => true]);
            DB::table('permission_role')->insert(['role_id' => $custom, 'permission_id' => $perm]);
            DB::table('role_user')->insert(['role_id' => $custom, 'user_id' => $user->id]);
        }

        return $user;
    }

    private function fixture(int $number, ?int $company = null): array
    {
        $company ??= DB::table('bus_companies')->insertGetId(['code' => 'CO-'.$number, 'name' => 'Company '.$number]);
        $bus = DB::table('buses')->insertGetId(['license_plate' => 'BUS-'.$number, 'bus_company_id' => $company]);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => 'R-'.$number, 'name' => 'Route',
            'origin_city'                                           => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000]);
        $trip = DB::table('trips')->insertGetId(['bus_company_id' => $company, 'bus_id' => $bus, 'route_id' => $route,
            'departure_time'                                      => now()->addDays(3), 'arrival_time' => now()->addDays(4), 'price' => 350000, 'available_seats' => 20]);
        $customer = DB::table('customers')->insertGetId(['full_name' => 'Customer '.$number, 'phone' => '09000000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT)]);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => 'BOOK-'.$number, 'trip_id' => $trip,
            'customer_id'                                             => $customer, 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'confirmed', 'created_at' => now()]);
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => 'TICKET-'.$number, 'trip_id' => $trip,
            'booking_id'                                           => $booking, 'seat_code' => 'B'.$number, 'passenger_name' => 'Customer '.$number, 'price' => 350000]);
        DB::table('payments')->insert(['payment_code' => 'PAY-'.$number, 'booking_id' => $booking,
            'amount'                                  => 350000, 'status' => 'completed', 'method' => 'bank_transfer', 'payload' => '{"secret":"private-provider-payload"}']);

        return [$company, $trip, $booking, $ticket];
    }

    private function intent(int $trip, int $secondary): int
    {
        return DB::table('sepay_payment_intents')->insertGetId([
            'token'    => (string) Str::uuid(), 'code' => 'FUTA1234567890', 'trip_id' => $trip, 'secondary_trip_id' => $secondary,
            'seat_ids' => '[1]', 'secondary_seat_ids' => '[2]', 'amount' => 700000,
            'snapshot' => json_encode(['token' => 'private-token', 'customer' => ['name' => 'Pending Customer', 'phone' => '0901234567'],
                'trip'                         => ['fare' => 350000], 'outbound_trip' => ['fare' => 350000], 'seats' => ['A1'],
                'outbound_seats'               => ['FOREIGN-SEAT'], 'outbound_pickup' => ['address' => 'foreign-address']]),
            'status' => 'pending', 'expires_at' => now()->addMinutes(10), 'created_at' => now(),
        ]);
    }
}

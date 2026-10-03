<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketHistoryTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_guest_is_redirected_and_customer_sees_empty_history(): void
    {
        $this->get(route('profile.tickets.index'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profile.tickets.index'))
            ->assertOk()
            ->assertSee('Bạn chưa có vé nào')
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-profile-date-picker', false)
            ->assertSee('data-ticket-status-picker', false)
            ->assertSee('colspan="8"', false)
            ->assertSee('href="'.route('home').'"', false);
    }

    public function test_history_only_shows_bookings_owned_by_the_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createBooking($user, 'OWN-001');
        $this->createBooking($other, 'OTHER-001');
        $this->createBooking($user, 'LINKED-001', ['booking_user_id' => null]);
        $this->createBooking($user, 'MISMATCH-001', ['booking_user_id' => $other->id]);

        $this->actingAs($user)->get(route('profile.tickets.index'))
            ->assertOk()
            ->assertSee('OWN-001')
            ->assertSee('LINKED-001')
            ->assertDontSee('OTHER-001')
            ->assertDontSee('MISMATCH-001')
            ->assertSee('Sài Gòn')
            ->assertSee('Đà Lạt');
    }

    public function test_filters_ticket_code_departure_route_and_status(): void
    {
        $user = User::factory()->create();
        $match = $this->createBooking($user, 'BOOK-001', [
            'departure_time' => '2026-10-10 08:00:00',
            'status'         => 'confirmed',
        ]);
        $this->createBooking($user, 'BOOK-002', [
            'departure_time'   => '2026-10-11 08:00:00',
            'status'           => 'cancelled',
            'destination_city' => 'Nha Trang',
        ]);
        DB::table('tickets')->insert([
            'ticket_code'    => 'TICKET-001',
            'booking_id'     => $match['booking_id'],
            'trip_id'        => $match['trip_id'],
            'passenger_name' => 'Customer',
            'price'          => 250000,
        ]);
        DB::table('payments')->insert([
            'payment_code' => 'PAY-001',
            'booking_id'   => $match['booking_id'],
            'amount'       => 250000,
            'method'       => 'mock',
            'status'       => 'completed',
        ]);

        $this->actingAs($user)->get(route('profile.tickets.index', [
            'code'   => 'TICKET-001',
            'date'   => '2026-10-10',
            'route'  => 'Đà Lạt',
            'status' => 'confirmed',
        ]))->assertOk()
            ->assertSee('BOOK-001')
            ->assertSee('Đã thanh toán')
            ->assertSee('250.000 đ')
            ->assertDontSee('BOOK-002');

        $this->get(route('profile.tickets.index', ['code' => 'MISSING']))
            ->assertOk()
            ->assertSee('Không tìm thấy đơn đặt vé');
    }

    public function test_invalid_filter_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.tickets.index', [
            'date'   => 'not-a-date',
            'status' => 'admin',
        ]))->assertRedirect()->assertSessionHasErrors(['date', 'status']);
    }

    public function test_payment_status_filter_uses_the_latest_payment(): void
    {
        $user = User::factory()->create();
        $paid = $this->createBooking($user, 'PAID-001');
        $this->createBooking($user, 'OPEN-001');

        foreach (['pending', 'completed'] as $index => $status) {
            DB::table('payments')->insert([
                'payment_code' => 'PAY-'.$index,
                'booking_id'   => $paid['booking_id'],
                'amount'       => 250000,
                'method'       => 'mock',
                'status'       => $status,
            ]);
        }

        $this->actingAs($user)
            ->get(route('profile.tickets.index', ['status' => 'payment:completed']))
            ->assertOk()
            ->assertSee('PAID-001')
            ->assertDontSee('OPEN-001')
            ->assertSee('data-ticket-status-option="payment:completed"', false);

        $this->get(route('profile.tickets.index', ['status' => 'payment:unpaid']))
            ->assertOk()
            ->assertSee('OPEN-001')
            ->assertDontSee('PAID-001');
    }

    public function test_booking_details_only_open_for_the_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownBooking = $this->createBooking($owner, 'OWN-DETAIL');
        $otherBooking = $this->createBooking($other, 'OTHER-DETAIL');

        $this->get(route('profile.tickets.show', $ownBooking['booking_id']))
            ->assertRedirect(route('login'));

        $this->actingAs($owner)
            ->get(route('profile.tickets.show', $ownBooking['booking_id']))
            ->assertOk()
            ->assertSee('OWN-DETAIL')
            ->assertDontSee('OTHER-DETAIL');

        $this->get(route('profile.tickets.show', $otherBooking['booking_id']))
            ->assertNotFound();
    }

    private function createBooking(User $owner, string $code, array $options = []): array
    {
        $number = ++$this->sequence;
        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA',
            'code' => 'COMP-'.$number,
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId,
            'license_plate'  => 'BUS-'.$number,
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'ROUTE-'.$number,
            'name'             => 'Sài Gòn - '.($options['destination_city'] ?? 'Đà Lạt'),
            'origin_city'      => 'Sài Gòn',
            'destination_city' => $options['destination_city'] ?? 'Đà Lạt',
            'base_price'       => 250000,
        ]);
        $tripId = DB::table('trips')->insertGetId([
            'route_id'       => $routeId,
            'bus_id'         => $busId,
            'bus_company_id' => $companyId,
            'departure_time' => $options['departure_time'] ?? '2026-10-10 08:00:00',
            'arrival_time'   => '2026-10-10 14:00:00',
            'price'          => 250000,
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'user_id'   => $owner->id,
            'full_name' => $owner->name,
            'phone'     => '09000000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        ]);
        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => $code,
            'trip_id'      => $tripId,
            'customer_id'  => $customerId,
            'user_id'      => array_key_exists('booking_user_id', $options) ? $options['booking_user_id'] : $owner->id,
            'seat_count'   => 1,
            'total_amount' => 250000,
            'status'       => $options['status'] ?? 'confirmed',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return ['booking_id' => $bookingId, 'trip_id' => $tripId];
    }
}

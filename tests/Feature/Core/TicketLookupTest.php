<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_look_up_a_booking_with_the_original_phone_and_booking_code(): void
    {
        app()->setLocale('vi');
        $this->createBooking();

        $this->get(route('ticket-lookup'))->assertOk()->assertSee('Tra cứu thông tin đặt vé');

        $this->post(route('ticket-lookup.search'), [
            'phone'       => '+84 568 503 606',
            'ticket_code' => 'booking-001',
        ])->assertOk()
            ->assertSee('BOOKING-001')
            ->assertSee('TICKET-001')
            ->assertSee('TICKET-002')
            ->assertSee('Đà Lạt')
            ->assertSee('Đã thanh toán')
            ->assertDontSee('data-notice-on-load', false);
    }

    public function test_ticket_code_lookup_shows_only_the_requested_ticket(): void
    {
        $this->createBooking();

        $this->post(route('ticket-lookup.search'), [
            'phone'       => '0568503606',
            'ticket_code' => 'TICKET-001',
        ])->assertOk()
            ->assertSee('TICKET-001')
            ->assertDontSee('TICKET-002');
    }

    public function test_lookup_matches_phone_numbers_saved_in_international_format(): void
    {
        $this->createBooking();
        DB::table('customers')->update(['phone' => '+84568503606']);

        $this->post(route('ticket-lookup.search'), [
            'phone'       => '0568503606',
            'ticket_code' => 'BOOKING-001',
        ])->assertOk()->assertSee('BOOKING-001');
    }

    public function test_lookup_shows_current_booking_ticket_and_latest_payment_status(): void
    {
        app()->setLocale('vi');
        $this->createBooking();
        DB::table('bookings')->update(['status' => 'cancelled']);
        DB::table('tickets')->where('ticket_code', 'TICKET-001')->update(['status' => 'refunded']);
        DB::table('payments')->insert([
            'payment_code' => 'PAY-002',
            'booking_id'   => DB::table('bookings')->value('id'),
            'amount'       => 500000,
            'status'       => 'refunded',
        ]);

        $this->post(route('ticket-lookup.search'), [
            'phone'       => '0568503606',
            'ticket_code' => 'TICKET-001',
        ])->assertOk()
            ->assertSee('Đã hủy')
            ->assertSee('Đã hoàn tiền');
    }

    public function test_wrong_phone_or_code_returns_the_same_generic_message(): void
    {
        app()->setLocale('vi');
        $this->createBooking();

        foreach ([
            ['phone' => '0568503607', 'ticket_code' => 'BOOKING-001'],
            ['phone' => '0568503606', 'ticket_code' => 'WRONG-001'],
        ] as $input) {
            $this->post(route('ticket-lookup.search'), $input)
                ->assertOk()
                ->assertSee('id="ticket-lookup-not-found"', false)
                ->assertSee('Tổng đài 1900 6067')
                ->assertSee('data-lookup-dialog-ok', false)
                ->assertDontSee('data-notice-on-load', false)
                ->assertSee('value="'.$input['phone'].'"', false)
                ->assertSee('value="'.$input['ticket_code'].'"', false)
                ->assertDontSee('Đà Lạt');
        }
    }

    public function test_invalid_input_is_rejected_without_querying(): void
    {
        $this->from(route('ticket-lookup'))
            ->post(route('ticket-lookup.search'), [
                'phone'       => 'abc',
                'ticket_code' => '',
            ])
            ->assertRedirect(route('ticket-lookup'))
            ->assertSessionHasErrors(['phone', 'ticket_code']);

        $this->from(route('ticket-lookup'))
            ->post(route('ticket-lookup.search'), [
                'phone'       => ['0568503606'],
                'ticket_code' => ['BOOKING-001'],
            ])
            ->assertRedirect(route('ticket-lookup'))
            ->assertSessionHasErrors(['phone', 'ticket_code']);
    }

    public function test_public_lookup_is_rate_limited(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.28']);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('ticket-lookup.search'), [
                'phone'       => '0568503606',
                'ticket_code' => 'NO-TICKET',
            ])->assertOk();
        }

        $this->post(route('ticket-lookup.search'), [
            'phone'       => '0568503606',
            'ticket_code' => 'NO-TICKET',
        ])->assertStatus(429);
    }

    private function createBooking(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId([
            'name' => 'FUTA',
            'code' => 'FUTA-TEST',
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId,
            'license_plate'  => '51B-12345',
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'SG-DL',
            'name'             => 'Sài Gòn - Đà Lạt',
            'origin_city'      => 'Sài Gòn',
            'destination_city' => 'Đà Lạt',
            'base_price'       => 250000,
        ]);
        $tripId = DB::table('trips')->insertGetId([
            'route_id'       => $routeId,
            'bus_id'         => $busId,
            'bus_company_id' => $companyId,
            'departure_time' => '2026-10-10 08:00:00',
            'arrival_time'   => '2026-10-10 14:00:00',
            'price'          => 250000,
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'full_name' => 'Khách thử nghiệm',
            'phone'     => '0568503606',
        ]);
        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => 'BOOKING-001',
            'trip_id'      => $tripId,
            'customer_id'  => $customerId,
            'seat_count'   => 2,
            'total_amount' => 500000,
            'status'       => 'confirmed',
        ]);

        foreach (['TICKET-001' => 'A01', 'TICKET-002' => 'A02'] as $code => $seat) {
            DB::table('tickets')->insert([
                'ticket_code'    => $code,
                'booking_id'     => $bookingId,
                'trip_id'        => $tripId,
                'passenger_name' => 'Hành khách',
                'seat_code'      => $seat,
                'price'          => 250000,
            ]);
        }

        DB::table('payments')->insert([
            'payment_code' => 'PAY-001',
            'booking_id'   => $bookingId,
            'amount'       => 500000,
            'status'       => 'completed',
        ]);
    }
}

<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use FuteBus\Payment\Services\TicketQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketHistoryTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_guest_is_redirected_and_customer_sees_empty_history(): void
    {
        $this->get(route('profile.tickets.index'))->assertRedirect(route('login'));

        $user = $this->createTestUser();
        $this->actingAs($user)->get(route('profile.tickets.index'))
            ->assertOk()
            ->assertSee('Bạn chưa có vé nào')
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-profile-date-picker', false)
            ->assertSee('<select id="ticket-status" name="status"', false)
            ->assertSee('colspan="8"', false)
            ->assertSee('href="'.route('home').'"', false);
    }

    public function test_history_only_shows_bookings_owned_by_the_account(): void
    {
        $user = $this->createTestUser();
        $other = $this->createTestUser();
        $this->createBooking($user, 'OWN-001');
        $this->createBooking($other, 'OTHER-001');
        $this->createBooking($user, 'LINKED-001', ['booking_user_id' => null]);
        $this->createBooking($user, 'MISMATCH-001', ['booking_user_id' => $other->id]);

        $this->getJson(route('api.v1.me.bookings.index'))->assertUnauthorized();
        $this->withToken($user->createToken('history test', ['api'])->plainTextToken)
            ->getJson(route('api.v1.me.bookings.index'))
            ->assertOk()
            ->assertJsonFragment(['booking_code' => 'OWN-001'])
            ->assertJsonFragment(['booking_code' => 'LINKED-001'])
            ->assertJsonMissing(['booking_code' => 'OTHER-001'])
            ->assertJsonMissing(['booking_code' => 'MISMATCH-001']);

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
        $user = $this->createTestUser();
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
        $user = $this->createTestUser();

        $this->actingAs($user)->get(route('profile.tickets.index', [
            'date'   => 'not-a-date',
            'status' => 'admin',
        ]))->assertRedirect()->assertSessionHasErrors(['date', 'status']);
    }

    public function test_payment_status_filter_uses_the_latest_payment(): void
    {
        $user = $this->createTestUser();
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
            ->assertSee('value="payment:completed" selected', false);

        $this->get(route('profile.tickets.index', ['status' => 'payment:unpaid']))
            ->assertOk()
            ->assertSee('OPEN-001')
            ->assertDontSee('PAID-001');
    }

    public function test_booking_details_only_open_for_the_owner(): void
    {
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $ownBooking = $this->createBooking($owner, 'OWN-DETAIL');
        $otherBooking = $this->createBooking($other, 'OTHER-DETAIL');

        $this->get(route('profile.tickets.show', $ownBooking['booking_id']))
            ->assertRedirect(route('login'));

        $this->withToken($owner->createToken('detail test', ['api'])->plainTextToken)
            ->getJson(route('api.v1.me.bookings.show', $otherBooking['booking_id']))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('profile.tickets.show', $ownBooking['booking_id']))
            ->assertOk()
            ->assertSee('OWN-DETAIL')
            ->assertDontSee('OTHER-DETAIL');

        $this->get(route('profile.tickets.show', $otherBooking['booking_id']))
            ->assertNotFound();

        $this->get(route('profile.tickets.show', ['booking' => $otherBooking['booking_id'], 'modal' => 1]))
            ->assertNotFound();

    }

    public function test_modal_uses_real_payment_and_issued_ticket_data(): void
    {
        $user = $this->createTestUser();
        $booking = $this->createBooking($user, 'JOURNEY-PAID', [
            'departure_time' => now()->addDays(2)->toDateTimeString(),
        ]);
        $url = route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]);

        $this->actingAs($user)->get(route('profile.tickets.index'))
            ->assertOk()->assertSee('data-history-dialog', false)
            ->assertSee('data-history-detail data-no-loader', false)
            ->assertDontSee('data-loading=', false);
        $this->get($url)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Đơn đã được ghi nhận, đang chờ thanh toán')
            ->assertDontSee('data:image/svg+xml;base64,');

        $this->issueTicket($booking, 'JOURNEY-TICKET');
        $this->recordPayment($booking, 'completed');

        $this->get($url)->assertOk()
            ->assertSee('Đã thanh toán, chờ giờ khởi hành')
            ->assertSee('JOURNEY-TICKET')
            ->assertSee('Mã vé · Ghế A01')
            ->assertSee('Đưa mã này cho nhân viên khi lên xe.')
            ->assertSee(app(TicketQrService::class)->dataUri('JOURNEY-TICKET'), false)
            ->assertDontSee('data-history-review', false);

        DB::table('tickets')->where('ticket_code', 'JOURNEY-TICKET')->update(['status' => 'used']);
        $this->get($url)->assertOk()
            ->assertSee(app(TicketQrService::class)->dataUri('JOURNEY-TICKET'), false)
            ->assertDontSee('Đưa mã này cho nhân viên khi lên xe.');

        DB::table('tickets')->where('ticket_code', 'JOURNEY-TICKET')->update(['status' => 'refunded']);
        $this->get($url)->assertOk()
            ->assertSee('JOURNEY-TICKET')
            ->assertDontSee('data:image/svg+xml;base64,', false)
            ->assertDontSee('Đưa mã này cho nhân viên khi lên xe.');
    }

    public function test_timeline_distinguishes_scheduled_times_from_operator_confirmation(): void
    {
        $this->travelTo(Carbon::parse('2026-10-11 09:00:00'));
        $user = $this->createTestUser();
        $booking = $this->createBooking($user, 'JOURNEY-STAGES', [
            'departure_time' => '2026-10-11 10:00:00',
            'arrival_time'   => '2026-10-11 16:00:00',
        ]);
        $this->issueTicket($booking, 'STAGES-TICKET');
        $this->recordPayment($booking, 'completed');
        $url = route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]);

        $this->actingAs($user)->get($url)->assertOk()
            ->assertSee('Đã thanh toán, chờ giờ khởi hành')
            ->assertDontSee('Đã qua giờ khởi hành dự kiến');

        $this->travelTo(Carbon::parse('2026-10-11 12:00:00'));
        $this->get($url)->assertOk()
            ->assertSee('Đã qua giờ khởi hành dự kiến')
            ->assertSee('nhà xe chưa xác nhận xe đã đi')
            ->assertDontSee('data-history-review', false);

        $this->travelTo(Carbon::parse('2026-10-11 17:00:00'));
        $this->get($url)->assertOk()
            ->assertSee('Đã qua giờ đến dự kiến')
            ->assertSee('Chỉ có thể đánh giá sau khi nhà xe xác nhận')
            ->assertDontSee('data-history-review', false);

        DB::table('trips')->where('id', $booking['trip_id'])->update(['status' => 'arrived']);
        $this->get($url)->assertOk()
            ->assertSee('Nhà xe đã cập nhật: xe đến nơi')
            ->assertSee('data-history-review', false);
    }

    public function test_cancelled_and_refunded_bookings_do_not_show_a_valid_qr_or_review_form(): void
    {
        $user = $this->createTestUser();
        $cancelled = $this->createBooking($user, 'JOURNEY-CANCELLED', ['status' => 'cancelled']);
        $refunded = $this->createBooking($user, 'JOURNEY-REFUNDED');
        foreach ([$cancelled, $refunded] as $booking) {
            $this->issueTicket($booking, 'TICKET-'.$booking['booking_id']);
        }
        $this->recordPayment($cancelled, 'completed');
        $this->recordPayment($refunded, 'refunded');
        $this->actingAs($user);

        foreach ([$cancelled, $refunded] as $booking) {
            $this->get(route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]))
                ->assertOk()
                ->assertDontSee('data:image/svg+xml;base64,', false)
                ->assertDontSee('data-history-review', false);
        }
    }

    public function test_review_requires_owned_completed_paid_trip_and_updates_one_record(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->travelTo(Carbon::parse('2026-10-11 18:00:00'));
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $booking = $this->createBooking($owner, 'JOURNEY-REVIEW', [
            'departure_time' => '2026-10-11 08:00:00',
            'arrival_time'   => '2026-10-11 14:00:00',
        ]);
        $this->issueTicket($booking, 'REVIEW-TICKET');
        $this->recordPayment($booking, 'completed');
        $url = route('profile.tickets.review', $booking['booking_id']);

        $this->post($url, ['rating' => 5])->assertRedirect(route('login'));
        $this->actingAs($other)->post($url, ['rating' => 5])->assertNotFound();
        $this->actingAs($owner)->post($url, ['rating' => 5])->assertForbidden();
        DB::table('trips')->where('id', $booking['trip_id'])->update(['status' => 'arrived']);

        $detailUrl = route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]);
        $form = $this->get($detailUrl)->assertOk()
            ->assertSee('name="rating" value=""', false)
            ->assertSee('data-review-tags="positive"', false)
            ->assertSee('data-review-tags="negative"', false)
            ->assertDontSee('id="journey-review-summary-title"', false);
        foreach ([1, 2, 3, 4, 5] as $rating) {
            $form->assertSee('data-review-rating="'.$rating.'"', false);
        }

        $this->post($url, ['rating' => 0])->assertSessionHasErrors('rating');
        $this->post($url, ['rating' => 6])->assertSessionHasErrors('rating');
        $this->post($url, ['rating' => 5, 'comment' => str_repeat('x', 301)])->assertSessionHasErrors('comment');
        $this->post($url, ['rating' => 5, 'comment' => 'Chuyến đi tốt'])->assertRedirect();
        $this->assertDatabaseHas('booking_reviews', ['booking_id' => $booking['booking_id'], 'user_id' => $owner->id, 'rating' => 5]);
        $this->get($detailUrl)->assertOk()
            ->assertSee('Cảm ơn bạn đã đánh giá!')
            ->assertSee('Đánh giá của bạn')
            ->assertSee('Tuyệt vời')
            ->assertSee('Chuyến đi tốt')
            ->assertDontSee('data-history-review', false);
        $this->get(route('profile.tickets.index'))->assertOk()->assertSee('Đã đánh giá');

        $this->postJson($url, ['rating' => 4, 'comment' => 'Xe sạch sẽ · Tốt <script>alert(1)</script>'])
            ->assertOk()
            ->assertJsonPath('message', 'Đã lưu đánh giá của bạn.')
            ->assertJsonPath('stage', 'rated')
            ->assertJsonPath('stageLabel', 'Đã đánh giá');
        $this->assertSame(1, DB::table('booking_reviews')->where('booking_id', $booking['booking_id'])->count());
        $this->assertDatabaseHas('booking_reviews', ['booking_id' => $booking['booking_id'], 'rating' => 4]);
        $this->get($detailUrl)
            ->assertOk()
            ->assertSee('Cảm ơn bạn đã đánh giá!')
            ->assertSee('Xe sạch sẽ')
            ->assertSee('Tốt')
            ->assertSee('4/5')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        foreach ([1 => 'Rất tệ', 2 => 'Chưa tốt', 3 => 'Bình thường'] as $rating => $label) {
            $this->postJson($url, ['rating' => $rating, 'comment' => 'Xe trễ giờ'])
                ->assertOk()->assertJsonPath('stage', 'rated');
            $this->get($detailUrl)->assertOk()
                ->assertSee($label)
                ->assertSee('Xe trễ giờ')
                ->assertDontSee('data-history-review', false);
            $this->assertDatabaseHas('booking_reviews', [
                'booking_id' => $booking['booking_id'],
                'rating'     => $rating,
                'comment'    => 'Xe trễ giờ',
            ]);
        }
        $this->assertSame(1, DB::table('booking_reviews')->where('booking_id', $booking['booking_id'])->count());

        app()->setLocale('en');
        $this->get($detailUrl)->assertOk()->assertSee('>Xe trễ giờ</span>', false);
        app()->setLocale('vi');

        DB::table('bookings')->where('id', $booking['booking_id'])->update(['status' => 'cancelled']);
        $this->post($url, ['rating' => 3])->assertForbidden();
        $this->get(route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]))
            ->assertOk()->assertSee('Đơn đặt vé đã hủy')->assertDontSee('data-history-review', false);
    }

    public function test_review_endpoint_limits_repeated_submissions(): void
    {
        $user = $this->createTestUser();
        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '127.0.0.44']);
        $url = route('profile.tickets.review', 999999);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post($url, ['rating' => 0])->assertSessionHasErrors('rating');
        }
        $this->post($url, ['rating' => 0])->assertTooManyRequests();
    }

    public function test_invalid_emotion_scores_do_not_change_a_saved_review(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $owner = $this->createTestUser();
        $booking = $this->createBooking($owner, 'INVALID-SCORE-REVIEW', ['trip_status' => 'arrived']);
        $this->issueTicket($booking, 'INVALID-SCORE-TICKET');
        $this->recordPayment($booking, 'completed');
        $url = route('profile.tickets.review', $booking['booking_id']);
        $this->actingAs($owner)->postJson($url, ['rating' => 3, 'comment' => 'Original review'])
            ->assertOk();

        foreach ([0, 6, 3.5, 'happy', null, [5]] as $rating) {
            $this->postJson($url, ['rating' => $rating, 'comment' => 'Changed review'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('rating');
            $this->assertDatabaseHas('booking_reviews', [
                'booking_id' => $booking['booking_id'],
                'user_id'    => $owner->id,
                'rating'     => 3,
                'comment'    => 'Original review',
            ]);
        }

        $this->assertDatabaseCount('booking_reviews', 1);
    }

    public function test_review_uses_the_route_booking_and_authenticated_account_despite_forged_identifiers(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $ownBooking = $this->createBooking($owner, 'OWN-FORGED-REVIEW', ['trip_status' => 'arrived']);
        $otherBooking = $this->createBooking($other, 'OTHER-FORGED-REVIEW', ['trip_status' => 'arrived']);
        foreach ([$ownBooking, $otherBooking] as $booking) {
            $this->issueTicket($booking, 'FORGED-TICKET-'.$booking['booking_id']);
            $this->recordPayment($booking, 'completed');
        }

        $this->actingAs($owner)
            ->postJson(route('profile.tickets.review', $ownBooking['booking_id']), [
                'rating'     => 1,
                'booking_id' => $otherBooking['booking_id'],
                'user_id'    => $other->id,
            ])
            ->assertOk()
            ->assertJsonPath('stage', 'rated');
        $this->assertDatabaseHas('booking_reviews', [
            'booking_id' => $ownBooking['booking_id'],
            'user_id'    => $owner->id,
            'rating'     => 1,
        ]);

        $this->postJson(route('profile.tickets.review', $otherBooking['booking_id']), [
            'rating'     => 5,
            'booking_id' => $ownBooking['booking_id'],
            'user_id'    => $owner->id,
        ])->assertNotFound();
        $this->assertDatabaseMissing('booking_reviews', ['booking_id' => $otherBooking['booking_id']]);
        $this->assertDatabaseCount('booking_reviews', 1);
    }

    public function test_review_requires_the_latest_payment_to_be_completed(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $owner = $this->createTestUser();
        $this->actingAs($owner);

        foreach ([null, 'pending', 'failed', 'refunded'] as $paymentStatus) {
            $booking = $this->createBooking($owner, 'PAYMENT-REVIEW-'.($paymentStatus ?? 'unpaid'), [
                'trip_status' => 'arrived',
            ]);
            $this->issueTicket($booking, 'PAYMENT-TICKET-'.$booking['booking_id']);
            if ($paymentStatus !== null) {
                $this->recordPayment($booking, 'completed');
                $this->recordPayment($booking, $paymentStatus);
            }

            $this->postJson(route('profile.tickets.review', $booking['booking_id']), [
                'rating'           => 5,
                'payment_status'   => 'completed',
                'arrivalConfirmed' => true,
                'canReview'        => true,
            ])->assertForbidden();
            $this->get(route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]))
                ->assertOk()
                ->assertDontSee('data-history-review', false);
        }

        $this->assertDatabaseCount('booking_reviews', 0);
    }

    public function test_demo_and_booking_without_issued_ticket_cannot_be_reviewed(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $user = $this->createTestUser();
        $demo = $this->createBooking($user, 'DEMOHIST-REVIEW', ['trip_status' => 'arrived']);
        $withoutTicket = $this->createBooking($user, 'NO-TICKET-REVIEW', ['trip_status' => 'arrived']);
        $this->issueTicket($demo, 'DEMO-TICKET');
        $this->recordPayment($demo, 'completed');
        $this->recordPayment($withoutTicket, 'completed');
        $this->actingAs($user);

        foreach ([$demo, $withoutTicket] as $booking) {
            $this->post(route('profile.tickets.review', $booking['booking_id']), ['rating' => 5])
                ->assertForbidden();
            $this->get(route('profile.tickets.show', ['booking' => $booking['booking_id'], 'modal' => 1]))
                ->assertOk()->assertDontSee('data-history-review', false);
        }
        $this->assertDatabaseCount('booking_reviews', 0);
    }

    public function test_change_contacts_support_and_cancellation_opens_modal_for_paid_bookings(): void
    {
        $user = $this->createTestUser();
        $eligible = $this->createBooking($user, 'ELIGIBLE-001', [
            'departure_time' => now()->addDays(3)->toDateTimeString(),
        ]);
        $tooLate = $this->createBooking($user, 'TOO-LATE-001', [
            'departure_time' => now()->addHours(6)->toDateTimeString(),
        ]);

        DB::table('payments')->insert([
            'payment_code' => 'PAID-MENU-001',
            'booking_id'   => $eligible['booking_id'],
            'amount'       => 250000,
            'method'       => 'mock',
            'status'       => 'completed',
        ]);
        DB::table('payments')->insert([
            'payment_code' => 'PAID-MENU-002',
            'booking_id'   => $tooLate['booking_id'],
            'amount'       => 250000,
            'method'       => 'mock',
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('profile.tickets.index'));
        $response
            ->assertOk()
            ->assertSee('aria-controls="ticket-actions-'.$eligible['booking_id'].'"', false)
            ->assertSee('href="tel:19006067"', false)
            ->assertSee('TOO-LATE-001')
            ->assertSee('disabled title=', false);

        $response->assertSee('href="'.route('cancellation.show', $eligible['booking_id']).'"', false)
            ->assertSee('href="'.route('cancellation.show', $tooLate['booking_id']).'"', false);
        $this->assertSame(1, substr_count(
            $response->getContent(),
            'data-history-sensitive'
        ));
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
            'arrival_time'   => $options['arrival_time'] ?? Carbon::parse($options['departure_time'] ?? '2026-10-10 08:00:00')->addHours(6)->toDateTimeString(),
            'status'         => $options['trip_status'] ?? 'scheduled',
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

    private function issueTicket(array $booking, string $code): void
    {
        DB::table('tickets')->insert([
            'ticket_code'    => $code,
            'booking_id'     => $booking['booking_id'],
            'trip_id'        => $booking['trip_id'],
            'passenger_name' => 'Customer',
            'seat_code'      => 'A01',
            'price'          => 250000,
        ]);
    }

    private function recordPayment(array $booking, string $status): void
    {
        DB::table('payments')->insert([
            'payment_code' => 'PAY-'.$booking['booking_id'].'-'.$status,
            'booking_id'   => $booking['booking_id'],
            'amount'       => 250000,
            'method'       => 'mock',
            'status'       => $status,
            'paid_at'      => $status === 'completed' ? now() : null,
        ]);
    }
}

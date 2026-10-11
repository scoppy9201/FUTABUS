<?php

namespace Tests\Feature\Profile;

use Database\Seeders\ProfileTicketHistoryDemoSeeder;
use FuteBus\Profile\Services\TicketHistoryService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketHistoryDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_refuses_to_write_outside_local_environment(): void
    {
        $this->expectException(\RuntimeException::class);
        (new ProfileTicketHistoryDemoSeeder)->run();
    }

    public function test_local_demo_seeder_creates_eight_real_history_states_without_valid_tickets(): void
    {
        $this->travelTo(Carbon::parse('2026-10-11 10:00:00'));
        $user = $this->createTestUser();
        $previousEmail = getenv('TICKET_HISTORY_DEMO_EMAIL');
        putenv('TICKET_HISTORY_DEMO_EMAIL='.$user->email);
        $this->app->detectEnvironment(fn (): string => 'local');

        try {
            $seeder = new ProfileTicketHistoryDemoSeeder;
            $seeder->run();
            $seeder->run();

            $history = app(TicketHistoryService::class);
            $expected = [
                1 => 'paid', 2 => 'cancelled', 3 => 'departed', 4 => 'arrived',
                5 => 'rated', 6 => 'booked', 7 => 'refunded', 8 => 'payment_failed',
            ];
            foreach ($expected as $number => $stage) {
                $booking = DB::table('bookings')
                    ->where('booking_code', 'DEMOHIST'.$user->id.'-'.$number)->first();
                $this->assertNotNull($booking);
                $this->assertSame($stage, $history->detailForUser($user, $booking->id)['stage']);
            }
            $this->assertSame(8, DB::table('bookings')->where('booking_code', 'like', 'DEMOHIST'.$user->id.'-%')->count());
            $this->assertSame(8, DB::table('trips')->join('routes', 'routes.id', '=', 'trips.route_id')
                ->where('routes.code', 'DEMOHIST-'.$user->id)->count());
            $this->assertSame(6, DB::table('payments')->where('method', 'mock')->count());
            $this->assertDatabaseCount('tickets', 0);
            $this->assertDatabaseCount('booking_reviews', 1);

            $this->actingAs($user)->get(route('profile.tickets.index'))
                ->assertOk()->assertSee('DEMOHIST'.$user->id.'-4')
                ->assertSee('Chờ khởi hành')
                ->assertSee('Đang di chuyển')
                ->assertSee('Đã đánh giá')
                ->assertSee('Thanh toán thất bại')
                ->assertDontSee('history-preview-title');
            $arrivedId = DB::table('bookings')->where('booking_code', 'DEMOHIST'.$user->id.'-4')->value('id');
            $this->get(route('profile.tickets.show', ['booking' => $arrivedId, 'modal' => 1]))
                ->assertOk()->assertSee('data-history-review', false)
                ->assertSee('Tài xế thân thiện')
                ->assertSee('QR mẫu', false)
                ->assertSee('data:image/svg+xml;base64,', false);
            $cancelledId = DB::table('bookings')->where('booking_code', 'DEMOHIST'.$user->id.'-2')->value('id');
            $this->get(route('profile.tickets.show', ['booking' => $cancelledId, 'modal' => 1]))
                ->assertOk()->assertDontSee('data:image/svg+xml;base64,', false);
            $this->assertDatabaseCount('tickets', 0);
            $this->app->detectEnvironment(fn (): string => 'testing');
            $this->get(route('profile.tickets.show', ['booking' => $arrivedId, 'modal' => 1]))
                ->assertOk()->assertDontSee('data:image/svg+xml;base64,', false);
            $this->app->detectEnvironment(fn (): string => 'local');
            $departedId = DB::table('bookings')->where('booking_code', 'DEMOHIST'.$user->id.'-3')->value('id');
            $this->get(route('profile.tickets.show', ['booking' => $departedId, 'modal' => 1]))
                ->assertOk()->assertSee('role="progressbar"', false)
                ->assertSee('xe khởi hành');
            $this->withoutMiddleware(ValidateCsrfToken::class);
            $this->post(route('profile.tickets.review', $arrivedId), ['rating' => 5])
                ->assertRedirect();
            $this->assertSame('rated', $history->detailForUser($user, $arrivedId)['stage']);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            $previousEmail === false
                ? putenv('TICKET_HISTORY_DEMO_EMAIL')
                : putenv('TICKET_HISTORY_DEMO_EMAIL='.$previousEmail);
        }
    }
}

<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use App\Models\User;
use FuteBus\Notification\Services\CompanyNotificationService;
use FuteBus\Payment\Models\TicketExchange;
use FuteBus\Payment\Notifications\TicketExchanged;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TicketExchangeIssuer
{
    public function issue(TicketExchange $exchange, object $ticket, object $trip, object $seat, array $stops): TicketExchange
    {
        $code = 'EX'.strtoupper(Str::random(16));
        $booking = DB::table('bookings')->insertGetId([
            'booking_code' => $code, 'trip_id' => $trip->id, 'customer_id' => $ticket->customer_id, 'user_id' => $exchange->user_id,
            'seat_count'   => 1, 'total_amount' => $exchange->fare, 'status' => 'confirmed',
            'notes'        => json_encode($stops, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $newTicket = DB::table('tickets')->insertGetId([
            'ticket_code' => $code.'01', 'booking_id' => $booking, 'trip_id' => $trip->id, 'seat_layout_id' => $seat->id,
            'seat_code'   => $seat->seat_code, 'passenger_name' => $ticket->passenger_name, 'passenger_phone' => $ticket->passenger_phone,
            'price'       => $exchange->fare, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('booked_seats')->insert(['booking_id' => $booking, 'trip_id' => $trip->id, 'seat_layout_id' => $seat->id,
            'seat_code'                                 => $seat->seat_code, 'price' => $exchange->fare, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('tickets')->where('id', $ticket->id)->update(['status' => 'cancelled', 'updated_at' => now()]);
        DB::table('booked_seats')->where('booking_id', $ticket->booking_id)->where('trip_id', $ticket->trip_id)
            ->where('seat_layout_id', $ticket->seat_layout_id)->delete();
        $remaining = DB::table('tickets')->where('booking_id', $ticket->booking_id)->whereIn('status', ['active', 'used']);
        $count = (clone $remaining)->count();
        DB::table('bookings')->where('id', $ticket->booking_id)->update([
            'seat_count' => $count, 'total_amount' => (clone $remaining)->sum('price'),
            'status'     => $count ? 'confirmed' : 'cancelled', 'updated_at' => now(),
        ]);
        $exchange->update(['status' => 'completed', 'new_ticket_id' => $newTicket, 'new_booking_id' => $booking,
            'original_departure_at' => $exchange->original_departure_at->min(Carbon::parse($ticket->departure_time)),
            'completed_at'          => now(), 'snapshot' => [...$exchange->snapshot, ...$stops]]);
        $this->notify($exchange, $ticket, $trip, $code.'01');

        return $exchange->fresh();
    }

    private function notify(TicketExchange $exchange, object $ticket, object $trip, string $code): void
    {
        $data = ['old' => $ticket->ticket_code, 'new' => $code, 'booking' => $exchange->new_booking_id];
        DB::afterCommit(function () use ($exchange, $trip, $data): void {
            try {
                User::find($exchange->user_id)?->notify(new TicketExchanged($data));
                app(CompanyNotificationService::class)->notifyManagers((int) $trip->bus_company_id, [
                    'title'   => __('Payment::exchange.completed'),
                    'message' => __('Payment::exchange.notification', $data),
                    'url'     => route('bookings.show', ['kind' => 'booking', 'record' => $exchange->new_booking_id], false),
                ]);
            } catch (Throwable $error) {
                Log::warning('Ticket exchange notification failed.', ['exchange' => $exchange->id, 'exception' => $error::class]);
            }
        });
    }
}

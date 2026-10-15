<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Payment\Mail\TicketLifecycleMail;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TicketEmailService
{
    public function __construct(private readonly TicketQrService $qr) {}

    public function bookingConfirmed(array $bookingIds, ?string $recipientEmail = null): void
    {
        DB::afterCommit(function () use ($bookingIds, $recipientEmail): void {
            $records = $this->ticketsForBookings($bookingIds);
            if ($records->isEmpty()) {
                return;
            }

            $this->deliver($recipientEmail ?: $records->first()->email, 'booking_confirmed', $records->all());
        });
    }

    public function exchanged(int $exchangeId): void
    {
        DB::afterCommit(function () use ($exchangeId): void {
            $exchange = DB::table('ticket_exchanges')->where('id', $exchangeId)->first();
            if (! $exchange || $exchange->status !== 'completed' || ! $exchange->new_ticket_id) {
                return;
            }

            $records = $this->ticketDetails()->where('tickets.id', $exchange->new_ticket_id)->get();
            if ($records->isEmpty()) {
                return;
            }

            $this->deliver($records->first()->email, 'exchange_completed', $records->all(), [
                'old_ticket_code' => DB::table('tickets')->where('id', $exchange->old_ticket_id)->value('ticket_code'),
            ]);
        });
    }

    public function cancellationRequested(int $cancellationId): void
    {
        DB::afterCommit(function () use ($cancellationId): void {
            $cancellation = DB::table('ticket_cancellations')->where('id', $cancellationId)->first();
            if (! $cancellation) {
                return;
            }

            $records = $this->ticketDetails()->where('tickets.id', $cancellation->ticket_id)->get();
            if ($records->isEmpty()) {
                return;
            }

            $this->deliver($records->first()->email, 'cancellation_requested', $records->all(), [
                'reference' => $cancellation->reference,
                'refund'    => (int) $cancellation->refund_amount,
                'fee'       => (int) $cancellation->fee_amount,
            ]);
        });
    }

    public function refundCompleted(int $refundId): void
    {
        DB::afterCommit(function () use ($refundId): void {
            $refund = DB::table('ticket_refunds')->join('ticket_cancellations', 'ticket_cancellations.id', '=', 'ticket_refunds.cancellation_id')
                ->where('ticket_refunds.id', $refundId)->whereIn('ticket_refunds.status', ['refunded', 'succeeded'])
                ->selectRaw('ticket_refunds.amount, COALESCE(ticket_refunds.bank_reference, ticket_refunds.provider_refund_id) as bank_reference, COALESCE(ticket_refunds.bank_paid_at, ticket_refunds.succeeded_at) as bank_paid_at')
                ->addSelect('ticket_cancellations.reference', 'ticket_cancellations.ticket_id')->first();
            if (! $refund) {
                return;
            }

            $records = $this->ticketDetails()->where('tickets.id', $refund->ticket_id)->get();
            if ($records->isEmpty()) {
                return;
            }

            $this->deliver($records->first()->email, 'refund_completed', $records->all(), [
                'reference'     => $refund->reference,
                'refund'        => (int) $refund->amount,
                'bank_reference'=> $refund->bank_reference,
                'bank_paid_at'  => $refund->bank_paid_at,
            ]);
        });
    }

    private function ticketsForBookings(array $bookingIds): Collection
    {
        return $this->ticketDetails()->whereIn('bookings.id', $bookingIds)->orderBy('tickets.id')->get();
    }

    private function ticketDetails(): Builder
    {
        return DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->select('tickets.id', 'tickets.ticket_code', 'tickets.passenger_name', 'tickets.passenger_phone', 'tickets.seat_code',
                'tickets.price', 'bookings.booking_code', 'bookings.notes', 'trips.departure_time', 'trips.arrival_time',
                'routes.origin_city', 'routes.destination_city', 'routes.origin_station', 'routes.destination_station', 'buses.bus_type')
            ->selectRaw("COALESCE(NULLIF(customers.email, ''), users.email) as email")
            ->selectRaw("COALESCE(NULLIF(tickets.passenger_name, ''), customers.full_name, users.name) as customer_name");
    }

    private function deliver(?string $email, string $event, array $records, array $details = []): void
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Ticket lifecycle email skipped because the customer email is missing or invalid.', ['event' => $event]);

            return;
        }

        try {
            $includeQr = in_array($event, ['booking_confirmed', 'exchange_completed'], true);
            $tickets = array_map(function (object $record) use ($includeQr): array {
                $notes = is_array($record->notes) ? $record->notes : (json_decode((string) $record->notes, true) ?: []);
                $pickup = $notes['pickup'] ?? [];
                $dropoff = $notes['dropoff'] ?? [];

                return [
                    'code'             => $record->ticket_code,
                    'booking_code'     => $record->booking_code,
                    'passenger_name'   => $record->customer_name ?: $record->passenger_name,
                    'passenger_phone'  => $record->passenger_phone,
                    'seat'             => $record->seat_code,
                    'price'            => (int) $record->price,
                    'departure_at'     => $record->departure_time,
                    'arrival_at'       => $record->arrival_time,
                    'origin'           => $record->origin_station ?: $record->origin_city,
                    'destination'      => $record->destination_station ?: $record->destination_city,
                    'bus_type'         => $record->bus_type,
                    'pickup_name'      => $this->stopValue($pickup, 'name', $record->origin_station ?: $record->origin_city),
                    'pickup_address'   => $this->stopValue($pickup, 'address'),
                    'dropoff_name'     => $this->stopValue($dropoff, 'name', $record->destination_station ?: $record->destination_city),
                    'dropoff_address'  => $this->stopValue($dropoff, 'address'),
                    'qr_png'           => $includeQr ? $this->qr->png($record->ticket_code) : null,
                ];
            }, $records);

            $name = $tickets[0]['passenger_name'] ?? '';
            $mail = (new TicketLifecycleMail($event, $name, $tickets, $details))->locale(app()->getLocale());
            Mail::to($email)->queue($mail);
        } catch (Throwable $exception) {
            Log::warning('Could not queue a ticket lifecycle email.', ['event' => $event, 'exception' => $exception::class]);
        }
    }

    private function stopValue(mixed $stop, string $key, ?string $fallback = null): ?string
    {
        if (is_string($stop)) {
            return $key === 'name' ? $stop : $fallback;
        }

        return is_array($stop) ? ($stop[$key] ?? $fallback) : $fallback;
    }
}

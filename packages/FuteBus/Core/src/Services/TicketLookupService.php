<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Facades\DB;

class TicketLookupService
{
    public function find(string $phone, string $code): ?array
    {
        $booking = DB::table('bookings')
            ->join('customers', 'customers.id', '=', 'bookings.customer_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->whereIn('customers.phone', [
                $phone,
                '+84'.substr($phone, 1),
                '84'.substr($phone, 1),
            ])
            ->where(function ($query) use ($code): void {
                $query->where('bookings.booking_code', $code)
                    ->orWhereExists(function ($tickets) use ($code): void {
                        $tickets->selectRaw('1')
                            ->from('tickets')
                            ->whereColumn('tickets.booking_id', 'bookings.id')
                            ->where('tickets.ticket_code', $code);
                    });
            })
            ->select([
                'bookings.id',
                'bookings.booking_code',
                'bookings.seat_count',
                'bookings.total_amount',
                'bookings.status',
                'trips.departure_time',
                'trips.arrival_time',
                'routes.origin_city',
                'routes.destination_city',
                'routes.origin_station',
                'routes.destination_station',
                'buses.bus_type',
            ])
            ->first();

        if ($booking === null) {
            return null;
        }

        $tickets = DB::table('tickets')
            ->where('booking_id', $booking->id)
            ->when($booking->booking_code !== $code, fn ($query) => $query->where('ticket_code', $code))
            ->orderBy('id')
            ->get(['ticket_code', 'seat_code', 'status']);

        $paymentStatus = DB::table('payments')
            ->where('booking_id', $booking->id)
            ->orderByDesc('id')
            ->value('status') ?? 'unpaid';

        return compact('booking', 'tickets', 'paymentStatus');
    }
}

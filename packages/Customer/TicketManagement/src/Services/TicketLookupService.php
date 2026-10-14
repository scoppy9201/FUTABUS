<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Services;

use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\Payment\Models\Payment as PaymentRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;

class TicketLookupService
{
    public function find(string $phone, string $code): ?array
    {
        $booking = BookingRecord::query()->toBase()
            ->join('customers', 'customers.id', '=', 'bookings.customer_id')
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('bus_companies', 'bus_companies.id', '=', 'trips.bus_company_id')
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
                'customers.full_name as customer_name',
                'customers.phone as customer_phone',
                'customers.email as customer_email',
                'trips.departure_time',
                'trips.arrival_time',
                'routes.origin_city',
                'routes.destination_city',
                'routes.origin_station',
                'routes.destination_station',
                'buses.bus_type',
                'buses.license_plate',
                'bus_companies.name as company_name',
                'bus_companies.code as company_code',
                'bus_companies.hotline as company_hotline',
            ])
            ->first();

        if ($booking === null) {
            return null;
        }

        $tickets = TicketRecord::query()->toBase()
            ->where('booking_id', $booking->id)
            ->when($booking->booking_code !== $code, fn ($query) => $query->where('ticket_code', $code))
            ->orderBy('id')
            ->get(['ticket_code', 'passenger_name', 'passenger_phone', 'seat_code', 'price', 'status']);

        $payment = PaymentRecord::query()->toBase()
            ->where('booking_id', $booking->id)
            ->orderByDesc('id')
            ->first(['method', 'status']);
        $paymentStatus = $payment?->status ?? 'unpaid';
        $paymentMethod = $payment?->method;

        return compact('booking', 'tickets', 'paymentStatus', 'paymentMethod');
    }
}

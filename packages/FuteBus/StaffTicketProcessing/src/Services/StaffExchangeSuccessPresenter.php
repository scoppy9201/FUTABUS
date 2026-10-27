<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use FuteBus\Payment\Models\TicketExchange;
use Illuminate\Support\Facades\DB;

class StaffExchangeSuccessPresenter
{
    public function render(TicketExchange $exchange, string $qr): string
    {
        $ticket = DB::table('tickets')->where('id', $exchange->new_ticket_id)->first();
        $booking = DB::table('bookings')->where('id', $exchange->new_booking_id)->first();
        $customer = DB::table('customers')->where('id', $booking->customer_id)->first();
        $trip = DB::table('trips')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->where('trips.id', $ticket->trip_id)
            ->first(['trips.id', 'trips.departure_time', 'routes.origin_city as origin', 'routes.destination_city as destination']);
        $vehicles = DB::table('trips')->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('bus_companies', 'bus_companies.id', '=', 'trips.bus_company_id')
            ->where('trips.id', $ticket->trip_id)
            ->get(['trips.id', 'buses.license_plate', 'buses.bus_type', 'buses.capacity', 'bus_companies.name'])->keyBy('id');
        $notes = json_decode($booking->notes ?? '', true) ?: [];
        $preview = ['customer' => ['name' => $ticket->passenger_name, 'phone' => $ticket->passenger_phone,
            'email'                       => $customer?->email ?? '—'], 'pickup' => $notes['pickup'], 'dropoff' => $notes['dropoff']];
        $ticketData = ['code' => $ticket->ticket_code, 'qr' => $qr, 'trip' => (array) $trip,
            'seat'            => $ticket->seat_code, 'price' => (int) $ticket->price];
        $method = DB::table('payments')->where('id', $exchange->payment_id)->value('method');

        return view('StaffTicketProcessing::partials.exchange-success', [
            'ticket'             => $ticketData, 'preview' => $preview, 'vehicles' => $vehicles,
            'bookingCodes'       => collect([$ticket->ticket_code => $booking->booking_code]),
            'paymentMethodLabel' => $method ? __('core::ticket-lookup.methods.'.$method) : __('StaffTicketProcessing::sale.unknown_method'),
            'shareUrl'           => route('ticket-lookup'),
            'exchangeDetails'    => ['old_fare' => (int) ($exchange->snapshot['old']['price'] ?? $exchange->fare),
                'new_fare'                      => (int) $ticket->price, 'fee' => (int) $exchange->fee,
                'additional'                    => max(0, (int) $ticket->price + (int) $exchange->fee - (int) $exchange->fare)],
        ])->render();
    }
}

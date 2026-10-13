<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffPaymentPresenter
{
    public function render(array $sale): array
    {
        if ($sale['booking'] !== null) {
            $method = DB::table('payments')->join('bookings', 'bookings.id', '=', 'payments.booking_id')
                ->where('bookings.booking_code', $sale['booking']->booking_code)
                ->where('payments.payment_code', $sale['reference'])->where('payments.status', 'completed')->value('payments.method');
            $vehicles = DB::table('trips')->join('buses', 'buses.id', '=', 'trips.bus_id')->join('bus_companies', 'bus_companies.id', '=', 'trips.bus_company_id')
                ->whereIn('trips.id', collect($sale['tickets'])->pluck('trip.id')->unique())
                ->get(['trips.id', 'buses.license_plate', 'buses.bus_type', 'buses.capacity', 'bus_companies.name'])->keyBy('id');
            $bookingCodes = DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
                ->whereIn('tickets.ticket_code', collect($sale['tickets'])->pluck('code'))->pluck('bookings.booking_code', 'tickets.ticket_code');
            $sale['html'] = view('StaffTickets::partials.customer-success', [
                'preview'            => $sale['preview'], 'total' => $sale['amount'], 'tickets' => collect($sale['tickets']), 'bookingCode' => $sale['booking']->booking_code,
                'cashReceipt'        => $method === 'cash',
                'paymentMethodLabel' => $method !== null ? __('core::ticket-lookup.methods.'.$method) : __('StaffTickets::sale.unknown_method'),
                'vehicles'           => $vehicles, 'bookingCodes' => $bookingCodes,
            ])->render();

            return $sale;
        }
        if ($sale['status'] === 'needs_review') {
            $sale['html'] = view('StaffTickets::partials.customer-review', ['paymentCode' => $sale['reference']])->render();

            return $sale;
        }
        $sale['html'] = view('StaffTickets::partials.customer-payment', [
            'preview'    => [...$sale['preview'], 'token' => $sale['id'] ?? $sale['token'] ?? (string) Str::uuid()],
            'expiresAt'  => $sale['expires_at'] ?? date('Y-m-d H:i:s', $sale['preview']['created_at'] + 600),
            'sePayQrUrl' => $sale['qr_url'], 'paymentEnabled' => $sale['qr_url'] !== null, 'paymentCanActivate' => false,
        ])->render();

        return $sale;
    }
}

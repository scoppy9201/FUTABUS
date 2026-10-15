<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Carbon\Carbon;
use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\CustomerManagement\Models\Customer as CustomerRecord;
use FuteBus\Notification\Services\CompanyNotificationService;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TicketIssuer
{
    public function __construct(private readonly CompanyNotificationService $notifications) {}

    public function issue(object $intent, array $preview, array $seatIds, bool $outbound = false): int
    {
        $trip = $outbound ? $preview['outbound_trip'] : $preview['trip'];
        $seatCodes = $outbound ? $preview['outbound_seats'] : $preview['seats'];
        $tripId = (int) $trip['id'];
        $phone = $preview['customer']['phone'];
        $customerId = CustomerRecord::query()->toBase()->where('phone', $phone)->value('id');
        if ($customerId === null) {
            $customerId = CustomerRecord::query()->toBase()->insertGetId([
                'user_id'    => $preview['user_id'] ?? null,
                'full_name'  => $preview['customer']['name'],
                'phone'      => $phone,
                'email'      => $preview['customer']['email'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $bookingCode = 'SP'.$intent->code.($outbound ? 'O' : '');
        $bookingId = BookingRecord::query()->toBase()->insertGetId([
            'booking_code' => $bookingCode,
            'trip_id'      => $tripId,
            'customer_id'  => $customerId,
            'user_id'      => $preview['user_id'] ?? null,
            'seat_count'   => count($seatIds),
            'total_amount' => count($seatIds) * (int) $trip['fare'],
            'status'       => 'confirmed',
            'notes'        => json_encode([
                'pickup'  => $outbound
                    ? ($preview['outbound_pickup'] ?? ['name' => $trip['origin']])
                    : $preview['pickup'],
                'dropoff' => $outbound
                    ? ($preview['outbound_dropoff'] ?? ['name' => $trip['destination']])
                    : $preview['dropoff'],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($seatIds as $index => $seatId) {
            $seatCode = $seatCodes[$index];
            BookedSeatRecord::query()->toBase()->insert([
                'booking_id'     => $bookingId,
                'trip_id'        => $tripId,
                'seat_layout_id' => $seatId,
                'seat_code'      => $seatCode,
                'price'          => $trip['fare'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            TicketRecord::query()->toBase()->insert([
                'ticket_code'     => $bookingCode.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'booking_id'      => $bookingId,
                'trip_id'         => $tripId,
                'seat_layout_id'  => $seatId,
                'passenger_name'  => $preview['customer']['name'],
                'passenger_phone' => $phone,
                'seat_code'       => $seatCode,
                'price'           => $trip['fare'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->notifyCompany($bookingId, $tripId, $bookingCode, $preview['customer']['name'], $seatCodes);

        return $bookingId;
    }

    private function notifyCompany(int $bookingId, int $tripId, string $bookingCode, string $customer, array $seats): void
    {
        DB::afterCommit(function () use ($bookingId, $tripId, $bookingCode, $customer, $seats): void {
            try {
                $trip = DB::table('trips')->join('routes', 'routes.id', '=', 'trips.route_id')
                    ->where('trips.id', $tripId)
                    ->first([
                        'trips.id', 'trips.bus_company_id', 'trips.departure_time',
                        'routes.origin_city', 'routes.destination_city',
                    ]);
                if ($trip === null) {
                    return;
                }

                $this->notifications->notifyManagers((int) $trip->bus_company_id, [
                    'title'   => __('Notification::app.booking_created_title'),
                    'summary' => __('Notification::app.booking_created_summary', ['customer' => $customer]),
                    'url'     => route('bookings.show', ['kind' => 'booking', 'record' => $bookingId], false),
                    'message' => __('Notification::app.booking_created_message', [
                        'customer'  => $customer,
                        'route'     => $trip->origin_city.' - '.$trip->destination_city,
                        'trip_id'   => $trip->id,
                        'departure' => Carbon::parse($trip->departure_time)->format('d/m/Y H:i'),
                        'booking'   => $bookingCode,
                        'seats'     => implode(', ', $seats),
                    ]),
                ]);
            } catch (Throwable $exception) {
                Log::warning('Could not create a ticket booking notification.', ['exception' => $exception::class]);
            }
        });
    }
}

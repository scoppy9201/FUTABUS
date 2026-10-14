<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\CustomerManagement\Models\Customer as CustomerRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;

class TicketIssuer
{
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

        return $bookingId;
    }
}

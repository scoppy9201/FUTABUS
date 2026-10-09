<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use Illuminate\Support\Facades\DB;

class TicketIssuer
{
    public function issue(object $intent, array $preview, array $seatIds): int
    {
        $phone = $preview['customer']['phone'];
        $customerId = DB::table('customers')->where('phone', $phone)->value('id');
        if ($customerId === null) {
            $customerId = DB::table('customers')->insertGetId([
                'user_id'    => $preview['user_id'] ?? null,
                'full_name'  => $preview['customer']['name'],
                'phone'      => $phone,
                'email'      => $preview['customer']['email'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $bookingCode = 'SP'.$intent->code;
        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => $bookingCode,
            'trip_id'      => $intent->trip_id,
            'customer_id'  => $customerId,
            'user_id'      => $preview['user_id'] ?? null,
            'seat_count'   => count($seatIds),
            'total_amount' => $intent->amount,
            'status'       => 'confirmed',
            'notes'        => json_encode([
                'pickup'  => $preview['pickup'],
                'dropoff' => $preview['dropoff'],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($seatIds as $index => $seatId) {
            $seatCode = $preview['seats'][$index];
            DB::table('booked_seats')->insert([
                'booking_id'     => $bookingId,
                'trip_id'        => $intent->trip_id,
                'seat_layout_id' => $seatId,
                'seat_code'      => $seatCode,
                'price'          => $preview['trip']['fare'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            DB::table('tickets')->insert([
                'ticket_code'     => $bookingCode.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'booking_id'      => $bookingId,
                'trip_id'         => $intent->trip_id,
                'seat_layout_id'  => $seatId,
                'passenger_name'  => $preview['customer']['name'],
                'passenger_phone' => $phone,
                'seat_code'       => $seatCode,
                'price'           => $preview['trip']['fare'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return $bookingId;
    }
}

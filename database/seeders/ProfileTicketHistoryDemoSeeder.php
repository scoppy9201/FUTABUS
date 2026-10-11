<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ProfileTicketHistoryDemoSeeder extends Seeder
{
    private const NOTE = 'Local ticket-history display data only; no real payment, seat, or ticket.';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Ticket history demo data may only be seeded locally.');
        }
        if (! Schema::hasTable('booking_reviews')) {
            throw new RuntimeException('Run the booking_reviews migration before seeding demo history.');
        }

        $email = trim((string) getenv('TICKET_HISTORY_DEMO_EMAIL'));
        if ($email !== '') {
            $user = DB::table('users')->where('email', $email)->first(['id', 'name', 'email']);
        } else {
            $userIds = DB::table('bookings')->where('booking_code', 'like', 'DEMOHIST%')
                ->distinct()->pluck('user_id')->filter()->unique();
            if ($userIds->count() !== 1) {
                throw new RuntimeException('Set TICKET_HISTORY_DEMO_EMAIL to the local account that should see demo history.');
            }
            $user = DB::table('users')->where('id', $userIds->first())->first(['id', 'name', 'email']);
        }
        if ($user === null) {
            throw new RuntimeException('The selected local account does not exist.');
        }

        DB::transaction(function () use ($user): void {
            $companyId = DB::table('bus_companies')->where('code', 'DEMOHIST')->value('id');
            if ($companyId === null) {
                $companyId = DB::table('bus_companies')->insertGetId([
                    'code'       => 'DEMOHIST', 'name' => 'Nhà xe mẫu - Lịch sử vé', 'is_active' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $busId = DB::table('buses')->where('license_plate', 'DEMOHIST-'.$user->id)->value('id');
            if ($busId === null) {
                $busId = DB::table('buses')->insertGetId([
                    'bus_company_id' => $companyId, 'license_plate' => 'DEMOHIST-'.$user->id,
                    'bus_type'       => 'sleeper', 'capacity' => 34, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $routeId = DB::table('routes')->where('code', 'DEMOHIST-'.$user->id)->value('id');
            if ($routeId === null) {
                $routeId = DB::table('routes')->insertGetId([
                    'bus_company_id'   => $companyId, 'code' => 'DEMOHIST-'.$user->id,
                    'name'             => 'TP. Hồ Chí Minh - Lâm Đồng',
                    'origin_city'      => 'TP. Hồ Chí Minh', 'origin_station' => 'Bến xe Miền Đông Mới',
                    'destination_city' => 'Lâm Đồng', 'destination_station' => 'Đà Lạt',
                    'base_price'       => 340000, 'is_active' => false,
                    'created_at'       => now(), 'updated_at' => now(),
                ]);
            }
            $phone = 'DEMO'.str_pad((string) $user->id, 12, '0', STR_PAD_LEFT);
            $customerId = DB::table('customers')->where('phone', $phone)->value('id');
            if ($customerId === null) {
                $customerId = DB::table('customers')->insertGetId([
                    'user_id'    => $user->id, 'full_name' => $user->name,
                    'email'      => $user->email, 'phone' => $phone,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $now = Carbon::now();
            $cases = [
                1 => ['booking' => 'confirmed', 'trip' => 'scheduled', 'departure' => $now->copy()->addDay(), 'payment' => 'completed'],
                2 => ['booking' => 'cancelled', 'trip' => 'scheduled', 'departure' => $now->copy()->subDay(), 'payment' => null],
                3 => ['booking' => 'confirmed', 'trip' => 'departed', 'departure' => $now->copy()->subHours(2), 'payment' => 'completed'],
                4 => ['booking' => 'completed', 'trip' => 'arrived', 'departure' => $now->copy()->subHours(10), 'payment' => 'completed'],
                5 => ['booking' => 'completed', 'trip' => 'arrived', 'departure' => $now->copy()->subHours(30), 'payment' => 'completed'],
                6 => ['booking' => 'pending', 'trip' => 'scheduled', 'departure' => $now->copy()->addDays(2), 'payment' => null],
                7 => ['booking' => 'confirmed', 'trip' => 'scheduled', 'departure' => $now->copy()->addDays(3), 'payment' => 'refunded'],
                8 => ['booking' => 'pending', 'trip' => 'scheduled', 'departure' => $now->copy()->addDays(4), 'payment' => 'failed'],
            ];

            foreach ($cases as $number => $case) {
                $code = 'DEMOHIST'.$user->id.'-'.$number;
                $existing = DB::table('bookings')->where('booking_code', $code)->first(['id', 'trip_id', 'notes']);
                if ($existing && ! str_starts_with((string) $existing->notes, 'Local ticket-history display data only;')) {
                    throw new RuntimeException('A demo code is already used by another booking.');
                }
                $tripId = $existing?->trip_id;
                $ownsTrip = $tripId && DB::table('trips')->where('id', $tripId)->where('route_id', $routeId)->exists()
                    && ! DB::table('bookings')->where('trip_id', $tripId)->where('id', '!=', $existing->id)->exists();
                $tripData = [
                    'route_id'       => $routeId, 'bus_id' => $busId, 'bus_company_id' => $companyId,
                    'departure_time' => $case['departure'], 'arrival_time' => $case['departure']->copy()->addHours(8),
                    'price'          => 340000, 'status' => $case['trip'], 'available_seats' => 0, 'updated_at' => now(),
                ];
                if ($ownsTrip) {
                    DB::table('trips')->where('id', $tripId)->update($tripData);
                } else {
                    $tripId = DB::table('trips')->insertGetId([...$tripData, 'created_at' => now()]);
                }
                $bookingData = [
                    'trip_id'    => $tripId, 'customer_id' => $customerId, 'user_id' => $user->id,
                    'seat_count' => 1, 'total_amount' => 340000, 'status' => $case['booking'],
                    'notes'      => self::NOTE, 'created_at' => $now->copy()->subDays(3), 'updated_at' => now(),
                ];
                if ($existing) {
                    DB::table('bookings')->where('id', $existing->id)->update($bookingData);
                    $bookingId = $existing->id;
                } else {
                    $bookingId = DB::table('bookings')->insertGetId([
                        ...$bookingData, 'booking_code' => $code,
                    ]);
                }

                if ($case['payment'] !== null) {
                    $paymentCode = $code.'-MOCK';
                    if (DB::table('payments')->where('booking_id', $bookingId)->where('payment_code', '!=', $paymentCode)->exists()) {
                        throw new RuntimeException('Demo booking has a payment outside the local fixture.');
                    }
                    DB::table('payments')->updateOrInsert(['payment_code' => $paymentCode], [
                        'booking_id' => $bookingId, 'amount' => 340000, 'method' => 'mock',
                        'status'     => $case['payment'], 'payload' => json_encode(['local_demo' => true]),
                        'paid_at'    => $case['payment'] === 'completed' ? $now->copy()->subDays(2) : null,
                        'updated_at' => now(), 'created_at' => $now->copy()->subDays(2),
                    ]);
                }
                if ($number === 5) {
                    DB::table('booking_reviews')->insertOrIgnore([
                        'booking_id' => $bookingId,
                        'user_id'    => $user->id, 'rating' => 5,
                        'comment'    => 'Chuyến đi mẫu để kiểm tra giao diện.',
                        'created_at' => $now->copy()->subHours(20), 'updated_at' => $now->copy()->subHours(20),
                    ]);
                }
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfileTicketHistoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Ticket history demo data may only be seeded locally.');
        }

        $email = trim((string) getenv('TICKET_HISTORY_DEMO_EMAIL'));
        if ($email === '') {
            throw new RuntimeException('Set TICKET_HISTORY_DEMO_EMAIL to the local account that should see demo history.');
        }

        $user = DB::table('users')->where('email', $email)->first(['id', 'name', 'email']);
        if ($user === null) {
            throw new RuntimeException('The selected local account does not exist.');
        }

        $trips = DB::table('trips')
            ->where('status', 'scheduled')
            ->where('departure_time', '>', now())
            ->orderBy('departure_time')
            ->limit(2)
            ->get(['id', 'price']);

        if ($trips->count() < 2) {
            throw new RuntimeException('At least two future scheduled trips are required for demo history.');
        }

        DB::transaction(function () use ($user, $trips): void {
            $phone = 'DEMO'.str_pad((string) $user->id, 12, '0', STR_PAD_LEFT);
            $customerId = DB::table('customers')->where('phone', $phone)->value('id');

            if ($customerId === null) {
                $customerId = DB::table('customers')->insertGetId([
                    'user_id'    => $user->id,
                    'full_name'  => $user->name,
                    'email'      => $user->email,
                    'phone'      => $phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($trips as $index => $trip) {
                DB::table('bookings')->insertOrIgnore([
                    'booking_code' => 'DEMOHIST'.$user->id.'-'.($index + 1),
                    'trip_id'      => $trip->id,
                    'customer_id'  => $customerId,
                    'user_id'      => $user->id,
                    'seat_count'   => 1,
                    'total_amount' => $trip->price,
                    'status'       => $index === 0 ? 'pending' : 'cancelled',
                    'notes'        => 'Local ticket-history display data only; no seat, payment, or ticket issued.',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        });
    }
}

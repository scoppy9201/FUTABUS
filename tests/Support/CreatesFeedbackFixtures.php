<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait CreatesFeedbackFixtures
{
    private int $sequence = 0;

    private function validFeedback(array $overrides = []): array
    {
        return array_replace([
            'category'      => 'trip_service',
            'urgency'       => 'complaint',
            'subject'       => 'An issue during the trip',
            'message'       => 'The pickup time changed and I would like assistance with this issue.',
            'contact_name'  => 'Customer Example',
            'contact_phone' => '0901234567',
            'contact_email' => 'customer@example.test',
            'truth_confirm' => true,
            'allow_contact' => true,
        ], $overrides);
    }

    private function createReport(User $owner, array $overrides = []): int
    {
        return DB::table('customer_feedback_reports')->insertGetId(array_replace($this->validFeedback(), [
            'user_id'    => $owner->id,
            'status'     => 'received',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function createBooking(User $owner, array $overrides = []): int
    {
        $number = ++$this->sequence;
        $companyId = DB::table('bus_companies')->insertGetId(['name' => 'FUTA', 'code' => 'FEEDBACK-'.$number]);
        $busId = DB::table('buses')->insertGetId(['bus_company_id' => $companyId, 'license_plate' => 'FEEDBACK-'.$number]);
        $routeId = DB::table('routes')->insertGetId([
            'bus_company_id'   => $companyId,
            'code'             => 'FEEDBACK-'.$number,
            'name'             => 'Ho Chi Minh City - Da Lat',
            'origin_city'      => 'Ho Chi Minh City',
            'destination_city' => 'Da Lat',
            'base_price'       => 250000,
        ]);
        $tripId = DB::table('trips')->insertGetId([
            'route_id'       => $routeId,
            'bus_id'         => $busId,
            'bus_company_id' => $companyId,
            'departure_time' => now()->addDay(),
            'arrival_time'   => now()->addDay()->addHours(6),
            'status'         => 'scheduled',
            'price'          => 250000,
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'user_id'   => $owner->id,
            'full_name' => $owner->name,
            'phone'     => '09012345'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        ]);

        return DB::table('bookings')->insertGetId(array_replace([
            'booking_code' => 'BOOK-FEEDBACK-'.$number,
            'user_id'      => $owner->id,
            'trip_id'      => $tripId,
            'customer_id'  => $customerId,
            'seat_count'   => 1,
            'total_amount' => 250000,
            'status'       => 'confirmed',
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides));
    }
}

<?php

namespace Tests\Feature\StaffDashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\CreatesFeedbackFixtures;
use Tests\TestCase;

class StaffActivityTest extends TestCase
{
    use CreatesFeedbackFixtures;
    use RefreshDatabase;

    public function test_activity_counts_audit_dates_and_scopes_company_and_assigned_queue(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        $customer = $this->createTestUser();
        $booking = $this->createBooking($customer);
        $foreign = $this->createBooking($customer);
        $trip = DB::table('bookings')->where('id', $booking)->value('trip_id');
        $company = DB::table('trips')->where('id', $trip)->value('bus_company_id');
        $staff = $this->staff($company, ['ticket.view', 'support.view', 'payment.view']);
        $ticket = $this->ticket($booking, 'OWN', '2026-10-01 00:00:00');
        $this->ticket($booking, 'BEFORE', '2026-09-30 23:59:59');
        $this->ticket($booking, 'AFTER', '2026-11-01 00:00:00');
        $foreignTicket = $this->ticket($foreign, 'FOREIGN', now());
        $refund = $this->refund($ticket, $booking, $customer);
        $this->refund($foreignTicket, $foreign, $customer);
        $report = $this->createReport($customer, ['booking_id' => $booking, 'subject' => 'OWN-SUPPORT']);
        $this->createReport($customer, ['booking_id' => $foreign, 'subject' => 'FOREIGN-SECRET']);
        $this->createReport($customer, ['booking_id' => $booking, 'status' => 'resolved']);
        $other = $this->createTestUser();
        $this->createReport($customer, ['booking_id' => $booking, 'assigned_staff_id' => $other->id, 'subject' => 'OTHER-ASSIGNEE']);
        $replacement = $this->ticket($booking, 'REPLACEMENT', now());
        $bus = DB::table('trips')->where('id', $trip)->value('bus_id');
        $seat = DB::table('seat_layouts')->insertGetId(['bus_id' => $bus, 'seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1]);
        DB::table('ticket_exchanges')->insert([
            'token'          => (string) Str::uuid(), 'user_id' => $customer->id, 'old_ticket_id' => $ticket, 'new_ticket_id' => $replacement,
            'payment_id'     => DB::table('ticket_refunds')->where('id', $refund)->value('payment_id'), 'target_trip_id' => $trip,
            'seat_layout_id' => $seat, 'actor_id' => $staff->id, 'status' => 'completed', 'fare' => 250000,
            'policy_version' => 'test', 'original_departure_at' => now()->addDays(2), 'expires_at' => now()->addHour(), 'snapshot' => '{}',
        ]);

        $url = route('api.v1.staff.activity', ['month' => '2026-10']);
        $response = $this->actingAs($staff)->getJson($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonCount(31, 'data.days')->assertJsonPath('data.days.0.issued', 1)
            ->assertJsonPath('data.days.14.issued', 0)->assertJsonPath('data.days.14.cancelled', 1)
            ->assertJsonPath('data.days.14.refund_pending', 1)->assertJsonPath('data.days.14.support_pending', 2)
            ->assertJsonCount(2, 'data.queue')->assertDontSee('FOREIGN-SECRET')->assertDontSee('OTHER-ASSIGNEE')->assertDontSee('FOREIGN');
        $this->assertContains(route('staff.support', ['report' => $report]), array_column($response->json('data.queue'), 'url'));
        $this->assertContains(route('staff.refunds.index', ['selected' => $refund]), array_column($response->json('data.queue'), 'url'));

        DB::table('customer_feedback_reports')->where('id', $report)->update(['status' => 'resolved']);
        DB::table('ticket_refunds')->where('id', $refund)->update(['status' => 'refund_succeeded']);
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data.queue')
            ->assertJsonPath('data.days.14.refund_pending', 0)->assertJsonPath('data.days.14.support_pending', 1);
        $this->getJson(route('api.v1.staff.activity', ['month' => '2026-09']))->assertOk()
            ->assertJsonCount(30, 'data.days')->assertJsonPath('data.days.29.issued', 1);
    }

    public function test_unavailable_permissions_return_null_and_missing_company_never_exposes_data(): void
    {
        $company = DB::table('bus_companies')->insertGetId(['name' => 'Company', 'code' => 'ACTIVITY']);
        $staff = $this->staff($company, []);
        $url = route('api.v1.staff.activity', ['month' => '2026-02']);
        $this->actingAs($staff)->getJson($url)->assertOk()->assertJsonCount(28, 'data.days')
            ->assertJsonPath('data.days.0.issued', null)->assertJsonPath('data.days.0.cancelled', null)
            ->assertJsonPath('data.days.0.support_pending', null)->assertJsonPath('data.days.0.refund_pending', null)
            ->assertJsonCount(0, 'data.queue');
        $staff->update(['bus_company_id' => null]);
        $this->getJson($url)->assertOk()->assertJsonPath('data.access.issued', false)->assertJsonCount(0, 'data.queue');
    }

    public function test_activity_requires_verified_staff_valid_month_and_api_token_ability(): void
    {
        $url = route('api.v1.staff.activity');
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs($this->createTestUser())->getJson($url)->assertForbidden();
        $company = DB::table('bus_companies')->insertGetId(['name' => 'Company', 'code' => 'ACTIVITY']);
        $staff = $this->staff($company, ['ticket.view']);
        foreach (['2026-13', 'invalid', '2026-01-01', '1800-01'] as $month) {
            $this->actingAs($staff)->getJson(route('api.v1.staff.activity', ['month' => $month]))
                ->assertUnprocessable()->assertJsonValidationErrors('month');
        }
        $staff->update(['is_active' => false]);
        $this->getJson($url)->assertForbidden();
        $staff->forceFill(['is_active' => true, 'email_verified_at' => null])->save();
        $this->actingAs($staff->fresh())->getJson($url)->assertForbidden();
        $staff->forceFill(['email_verified_at' => now()])->save();
        $this->app['auth']->forgetGuards();
        $this->withToken($staff->createToken('wrong scope', ['other'])->plainTextToken)->getJson($url)->assertForbidden();
    }

    private function staff(int $company, array $permissions): User
    {
        $staff = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true]);
        $identity = DB::table('roles')->where('slug', 'staff')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        $role = DB::table('roles')->insertGetId(['name' => 'Activity', 'slug' => 'activity-'.$staff->id, 'bus_company_id' => $company]);
        DB::table('role_user')->insert([['user_id' => $staff->id, 'role_id' => $identity], ['user_id' => $staff->id, 'role_id' => $role]]);
        foreach ($permissions as $slug) {
            $permission = DB::table('permissions')->where('slug', $slug)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
            DB::table('permission_role')->insert(['permission_id' => $permission, 'role_id' => $role]);
        }

        return $staff;
    }

    private function ticket(int $booking, string $code, mixed $created): int
    {
        return DB::table('tickets')->insertGetId(['ticket_code' => $code, 'booking_id' => $booking,
            'trip_id'                                           => DB::table('bookings')->where('id', $booking)->value('trip_id'),
            'passenger_name'                                    => 'Test', 'price' => 250000, 'status' => 'active', 'created_at' => $created]);
    }

    private function refund(int $ticket, int $booking, User $customer): int
    {
        $cancellation = DB::table('ticket_cancellations')->insertGetId(['reference' => (string) Str::uuid(), 'ticket_id' => $ticket,
            'user_id'                                                               => $customer->id, 'reason' => 'plans', 'ticket_amount' => 250000, 'fee_percent' => 10, 'fee_amount' => 25000,
            'refund_amount'                                                         => 225000, 'policy_version' => 'test', 'departure_at' => now()->addDays(2), 'created_at' => now()]);
        $payment = DB::table('payments')->insertGetId(['payment_code' => 'ACTIVITY-'.$ticket, 'booking_id' => $booking,
            'amount'                                                  => 250000, 'status' => 'completed', 'method' => 'bank_transfer']);

        return DB::table('ticket_refunds')->insertGetId(['cancellation_id' => $cancellation, 'payment_id' => $payment,
            'transaction_id'                                               => 'TEST-'.$ticket, 'idempotency_key' => (string) Str::uuid(), 'amount' => 225000,
            'environment'                                                  => 'sandbox', 'processing_mode' => 'manual', 'status' => 'refund_pending', 'created_at' => now()]);
    }
}

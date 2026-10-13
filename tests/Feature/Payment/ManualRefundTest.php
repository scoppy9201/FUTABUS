<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use FuteBus\Payment\Notifications\TicketRefundCompleted;
use FuteBus\Payment\Services\SePayRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManualRefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->travelTo(now()->startOfSecond());
        Http::preventStrayRequests();
        Notification::fake();
    }

    public function test_staff_claims_and_records_verified_refund_with_idempotent_completion(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $staff = $this->operator($company);
        $this->actingAs($staff)->get(route('refunds.index'))->assertOk()->assertSee('TICKET-1');
        $this->get(route('refunds.show', $refund))->assertOk()->assertSee('data-refund-form', false);
        $url = route('refunds.update', $refund);
        $this->postJson($url, ['action' => 'claim', 'version' => 0])->assertOk()->assertJsonPath('status', 'refund_processing');
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $data = $this->settlement();
        $this->postJson($url, $data)->assertOk()->assertJsonPath('status', 'refunded');
        $this->postJson($url, $data)->assertOk()->assertJsonPath('version', 2);
        $this->assertDatabaseCount('ticket_refund_events', 2);
        $this->assertDatabaseHas('ticket_refund_events', ['refund_id' => $refund, 'actor_id' => $staff->id, 'to_status' => 'refunded', 'amount' => 315000]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'refunded']);
        $this->assertDatabaseHas('payments', ['status' => 'completed', 'amount' => 350000]);
        Notification::assertSentTimes(TicketRefundCompleted::class, 1);
        Http::assertNothingSent();
    }

    public function test_customers_read_only_staff_and_foreign_company_cannot_process_refunds(): void
    {
        [$refund, , $company] = $this->fixture();
        $this->actingAs($this->createTestUser())->get(route('refunds.index'))->assertForbidden();
        $readOnly = $this->operator($company, false);
        $this->actingAs($readOnly)->get(route('refunds.index'))->assertOk();
        $this->postJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertForbidden();
        $otherCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $this->actingAs($this->operator($otherCompany))->get(route('refunds.show', $refund))->assertNotFound();
        $this->postJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertNotFound();
        $this->get(route('refunds.index'))->assertOk()->assertDontSee('TICKET-1');
        $this->assertDatabaseCount('ticket_refund_events', 0);
    }

    public function test_staff_uses_employee_portal_and_refund_forms_stay_in_employee_routes(): void
    {
        [$refund, , $company] = $this->fixture();
        $staff = $this->operator($company);
        $this->actingAs($staff)->get(route('staff.refunds.index'))->assertForbidden();
        $role = DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $role]);
        $this->get(route('refunds.index'))->assertRedirect(route('staff.refunds.index'));
        $this->get(route('staff.refunds.index'))->assertOk()
            ->assertSee('href="'.route('staff.dashboard').'"', false)
            ->assertSee('href="'.route('staff.refunds.show', $refund).'"', false)
            ->assertDontSee(__('Dashboard::app.owner_portal'));
        $this->get(route('staff.refunds.show', $refund))->assertOk()
            ->assertSee('action="'.route('staff.refunds.update', $refund).'"', false);
        $this->postJson(route('staff.refunds.update', $refund), ['action' => 'claim', 'version' => 0])
            ->assertOk()->assertJsonPath('status', 'refund_processing');
    }

    public function test_second_staff_and_stale_version_cannot_take_over_or_complete(): void
    {
        [$refund, , $company] = $this->fixture();
        $first = $this->operator($company);
        $second = $this->operator($company);
        $url = route('refunds.update', $refund);
        $this->actingAs($first)->postJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        $this->actingAs($second)->postJson($url, ['action' => 'claim', 'version' => 0])->assertConflict();
        $this->postJson($url, $this->settlement())->assertConflict();
        $this->actingAs($first)->postJson($url, [...$this->settlement(), 'version' => 0])->assertConflict();
        $this->assertDatabaseCount('ticket_refund_events', 1);
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund, 'assigned_to' => $first->id, 'status' => 'refund_processing']);
    }

    public function test_incorrect_amount_missing_confirmation_and_invalid_dates_cannot_mark_success(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $this->actingAs($this->operator($company));
        $url = route('refunds.update', $refund);
        $this->postJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        foreach ([['amount' => 314999], ['amount' => 315001], ['bank_verified' => 0], ['bank_reference' => ''],
            ['bank_paid_at' => now()->addDay()->toDateTimeString()], ['bank_paid_at' => now()->subDay()->toDateTimeString()]] as $override) {
            $this->postJson($url, [...$this->settlement(), ...$override])->assertUnprocessable();
        }
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->assertDatabaseCount('ticket_refund_events', 1);
    }

    public function test_failed_refund_remains_cancelled_and_can_be_claimed_for_follow_up(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $this->actingAs($this->operator($company));
        $url = route('refunds.update', $refund);
        $this->postJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        $this->postJson($url, ['action' => 'fail', 'version' => 1])->assertUnprocessable();
        $this->postJson($url, ['action' => 'fail', 'version' => 1, 'note' => 'Bank declined'])->assertOk()->assertJsonPath('status', 'refund_failed');
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->postJson($url, ['action' => 'claim', 'version' => 2])->assertOk()->assertJsonPath('version', 3);
        $this->assertDatabaseCount('ticket_refund_events', 3);
    }

    public function test_duplicate_bank_reference_cannot_confirm_a_second_refund(): void
    {
        [$first, , $company] = $this->fixture();
        [$second, $ticket] = $this->fixture(2, $company);
        $this->actingAs($this->operator($company));
        foreach ([$first, $second] as $refund) {
            $this->postJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertOk();
        }
        $this->postJson(route('refunds.update', $first), $this->settlement())->assertOk();
        $this->postJson(route('refunds.update', $second), $this->settlement())->assertUnprocessable()->assertJsonValidationErrors('bank_reference');
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
    }

    public function test_manual_requests_never_call_sepay_even_if_automatic_flags_are_enabled(): void
    {
        [$refund] = $this->fixture();
        config(['services.sepay.refund_enabled' => true, 'services.sepay.refund_mode' => 'automatic',
            'services.sepay.refund_token'       => 'test', 'services.sepay.refund_bank_id' => 'test']);
        app(SePayRefundService::class)->process($refund);
        $this->artisan('tickets:process-refunds')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_inactive_staff_is_denied_and_legacy_automatic_refund_cannot_be_claimed(): void
    {
        [$refund, , $company] = $this->fixture();
        $staff = $this->operator($company);
        $staff->update(['is_active' => false]);
        $this->actingAs($staff)->postJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertForbidden();
        $staff->update(['is_active' => true]);
        DB::table('ticket_refunds')->where('id', $refund)->update(['processing_mode' => 'automatic', 'status' => 'pending']);
        $this->postJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertConflict();
        $this->assertDatabaseCount('ticket_refund_events', 0);
    }

    public function test_workspace_filters_requests_but_keeps_scoped_overview_totals(): void
    {
        [$recent, , $company] = $this->fixture();
        [$old] = $this->fixture(2, $company);
        DB::table('ticket_refunds')->where('id', $old)->update([
            'status' => 'refund_failed', 'created_at' => now()->subDays(8),
        ]);
        $this->actingAs($this->operator($company));
        $this->get(route('refunds.index', ['period' => '7']))->assertOk()
            ->assertSee('TICKET-1')->assertDontSee('TICKET-2')
            ->assertViewHas('summary', fn ($summary) => (int) $summary['refund_pending']->amount === 315000
                && (int) $summary['refund_failed']->total === 1)
            ->assertViewHas('selectedRefund', fn ($record) => $record->id === $recent);
        $this->get(route('refunds.index', ['status' => 'refund_failed', 'search' => 'BOOK-2']))
            ->assertOk()->assertSee('TICKET-2')->assertDontSee('TICKET-1');
        $this->getJson(route('refunds.index', ['period' => '365']))->assertUnprocessable();
    }

    public function test_report_export_uses_company_and_filters_and_escapes_spreadsheet_formulas(): void
    {
        [$refund, , $company] = $this->fixture();
        $this->fixture(2, $company);
        $other = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $this->fixture(3, $other);
        $customer = DB::table('ticket_cancellations')->where('id',
            DB::table('ticket_refunds')->where('id', $refund)->value('cancellation_id'))->value('user_id');
        DB::table('users')->where('id', $customer)->update(['name' => '=HYPERLINK("unsafe")']);
        $this->actingAs($this->operator($company));
        $response = $this->get(route('refunds.index', ['export' => 'csv', 'search' => 'TICKET-1']));
        $response->assertOk()->assertDownload('refunds-'.now()->format('Y-m-d').'.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('TICKET-1', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('TICKET-2', $csv);
        $this->assertStringNotContainsString('TICKET-3', $csv);
        $this->actingAs($this->createTestUser())->get(route('refunds.index', ['export' => 'csv']))->assertForbidden();
        Http::assertNothingSent();
    }

    private function settlement(): array
    {
        return ['action'     => 'complete', 'version' => 1, 'amount' => 315000,
            'bank_reference' => 'BANK-REF-1', 'bank_paid_at' => now()->toDateTimeString(), 'bank_verified' => 1];
    }

    private function operator(int $company, bool $write = true): User
    {
        $user = $this->createTestUser(['bus_company_id' => $company, 'is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Refund operator '.$user->id, 'slug' => 'refund-'.$user->id, 'bus_company_id' => $company]);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $user->id]);
        foreach ($write ? ['payment.view', 'payment.refund'] : ['payment.view'] as $slug) {
            $permission = DB::table('permissions')->where('slug', $slug)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $slug, 'slug' => $slug, 'group' => 'payment', 'is_active' => true]);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => $permission]);
        }

        return $user;
    }

    private function fixture(int $sequence = 1, ?int $company = null): array
    {
        $customer = $this->createTestUser();
        $company ??= DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $bus = DB::table('buses')->insertGetId(['license_plate' => 'BUS-'.$sequence, 'bus_company_id' => $company]);
        $route = DB::table('routes')->insertGetId(['bus_company_id' => $company, 'code' => 'R-'.$sequence, 'name' => 'Route',
            'origin_city'                                           => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000]);
        $trip = DB::table('trips')->insertGetId(['bus_company_id' => $company, 'bus_id' => $bus, 'route_id' => $route,
            'departure_time'                                      => now()->addDays(3), 'arrival_time' => now()->addDays(4), 'price' => 350000]);
        $profile = DB::table('customers')->insertGetId(['full_name' => 'Customer', 'phone' => '090000000'.$sequence, 'user_id' => $customer->id]);
        $booking = DB::table('bookings')->insertGetId(['booking_code' => 'BOOK-'.$sequence, 'trip_id' => $trip,
            'customer_id'                                             => $profile, 'user_id' => $customer->id, 'seat_count' => 1, 'total_amount' => 350000, 'status' => 'cancelled']);
        $ticket = DB::table('tickets')->insertGetId(['ticket_code' => 'TICKET-'.$sequence, 'trip_id' => $trip,
            'booking_id'                                           => $booking, 'passenger_name' => 'Customer', 'price' => 350000, 'status' => 'cancelled']);
        $payment = DB::table('payments')->insertGetId(['payment_code' => 'PAY-'.$sequence, 'booking_id' => $booking,
            'amount'                                                  => 350000, 'status' => 'completed', 'method' => 'bank_transfer', 'transaction_id' => (string) $sequence]);
        $cancellation = DB::table('ticket_cancellations')->insertGetId(['reference' => (string) Str::uuid(), 'ticket_id' => $ticket,
            'user_id'                                                               => $customer->id, 'reason' => 'plans', 'ticket_amount' => 350000, 'fee_percent' => 10,
            'fee_amount'                                                            => 35000, 'refund_amount' => 315000, 'policy_version' => 'test', 'departure_at' => now()->addDays(3),
            'created_at'                                                            => now(), 'updated_at' => now()]);
        $refund = DB::table('ticket_refunds')->insertGetId(['cancellation_id' => $cancellation, 'payment_id' => $payment,
            'transaction_id'                                                  => (string) $sequence, 'idempotency_key' => (string) Str::uuid(), 'amount' => 315000,
            'environment'                                                     => 'live', 'processing_mode' => 'manual', 'status' => 'refund_pending', 'created_at' => now(), 'updated_at' => now()]);

        return [$refund, $ticket, $company];
    }
}

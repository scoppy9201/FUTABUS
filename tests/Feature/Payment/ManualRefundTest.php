<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use FuteBus\Payment\Notifications\TicketRefundCompleted;
use FuteBus\Payment\Services\SePayRefundService;
use FuteBus\Payment\Services\TicketEmailService;
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
        $this->actingAs($staff)->get(route('refunds.index'))->assertOk()->assertSee('TICKET-1')
            ->assertSee('<dialog id="refund-details"', false)
            ->assertSee('data-refund-open="false"', false)
            ->assertSee('aria-haspopup="dialog"', false);
        $this->get(route('refunds.index', ['selected' => $refund]))->assertOk()
            ->assertSee('data-refund-open="true"', false);
        $this->get(route('refunds.show', $refund))->assertOk()->assertSee('data-refund-form', false);
        $url = route('refunds.update', $refund);
        $this->postJson($url, ['action' => 'claim', 'version' => 0])->assertMethodNotAllowed();
        $this->patchJson($url, ['action' => 'claim', 'version' => 0])->assertOk()->assertJsonPath('status', 'refund_processing');
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->mock(TicketEmailService::class, function ($mock) use ($refund): void {
            $mock->shouldReceive('refundCompleted')->once()->with($refund);
        });
        $data = $this->settlement();
        $this->patchJson($url, $data)->assertOk()->assertJsonPath('status', 'refunded');
        $this->patchJson($url, $data)->assertOk()->assertJsonPath('version', 2);
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
        $this->patchJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertForbidden();
        $otherCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $this->actingAs($this->operator($otherCompany))->get(route('refunds.show', $refund))->assertNotFound();
        $this->patchJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertNotFound();
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
        $this->patchJson(route('staff.refunds.update', $refund), ['action' => 'claim', 'version' => 0])
            ->assertOk()->assertJsonPath('status', 'refund_processing');
    }

    public function test_second_staff_and_stale_version_cannot_take_over_or_complete(): void
    {
        [$refund, , $company] = $this->fixture();
        $first = $this->operator($company);
        $second = $this->operator($company);
        $url = route('refunds.update', $refund);
        $this->actingAs($first)->patchJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        $this->actingAs($second)->patchJson($url, ['action' => 'claim', 'version' => 0])->assertConflict();
        $this->patchJson($url, $this->settlement())->assertConflict();
        $this->actingAs($first)->patchJson($url, [...$this->settlement(), 'version' => 0])->assertConflict();
        $this->assertDatabaseCount('ticket_refund_events', 1);
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund, 'assigned_to' => $first->id, 'status' => 'refund_processing']);
    }

    public function test_incorrect_amount_missing_confirmation_and_invalid_dates_cannot_mark_success(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $this->actingAs($this->operator($company));
        $url = route('refunds.update', $refund);
        $this->patchJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        foreach ([['amount' => 314999], ['amount' => 315001], ['bank_verified' => 0], ['bank_reference' => ''],
            ['bank_paid_at' => now()->addDay()->toDateTimeString()], ['bank_paid_at' => now()->subDay()->toDateTimeString()]] as $override) {
            $this->patchJson($url, [...$this->settlement(), ...$override])->assertUnprocessable();
        }
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->assertDatabaseCount('ticket_refund_events', 1);
    }

    public function test_failed_refund_remains_cancelled_and_can_be_claimed_for_follow_up(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $this->actingAs($this->operator($company));
        $url = route('refunds.update', $refund);
        $this->patchJson($url, ['action' => 'claim', 'version' => 0])->assertOk();
        $this->patchJson($url, ['action' => 'fail', 'version' => 1])->assertUnprocessable();
        $this->patchJson($url, ['action' => 'fail', 'version' => 1, 'note' => 'Bank declined'])->assertOk()->assertJsonPath('status', 'refund_failed');
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->patchJson($url, ['action' => 'claim', 'version' => 2])->assertOk()->assertJsonPath('version', 3);
        $this->assertDatabaseCount('ticket_refund_events', 3);
    }

    public function test_duplicate_bank_reference_cannot_confirm_a_second_refund(): void
    {
        [$first, , $company] = $this->fixture();
        [$second, $ticket] = $this->fixture(2, $company);
        $this->actingAs($this->operator($company));
        foreach ([$first, $second] as $refund) {
            $this->patchJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertOk();
        }
        $this->patchJson(route('refunds.update', $first), $this->settlement())->assertOk();
        $this->patchJson(route('refunds.update', $second), $this->settlement())->assertUnprocessable()->assertJsonValidationErrors('bank_reference');
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
        $this->actingAs($staff)->patchJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertForbidden();
        $staff->update(['is_active' => true]);
        DB::table('ticket_refunds')->where('id', $refund)->update(['processing_mode' => 'automatic', 'status' => 'pending']);
        $this->patchJson(route('refunds.update', $refund), ['action' => 'claim', 'version' => 0])->assertConflict();
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

    public function test_admin_assigns_only_eligible_staff_and_records_the_entire_processing_chain(): void
    {
        [$refund, $ticket, $company] = $this->fixture();
        $admin = $this->admin();
        $handler = $this->staff($company);
        $reader = $this->staff($company, false);
        $this->actingAs($admin)->get(route('refunds.index'))->assertOk()
            ->assertViewHas('eligibleHandlers', fn ($users) => $users->pluck('id')->all() === [$handler->id]);
        $url = route('refunds.update', $refund);
        $this->patchJson($url, ['action' => 'assign', 'assigned_to' => $reader->id, 'version' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('assigned_to');
        $this->patchJson($url, ['action' => 'assign', 'assigned_to' => $handler->id, 'version' => 0, 'note' => 'Accounting review'])
            ->assertOk()->assertJsonPath('status', 'refund_processing');
        $this->assertDatabaseHas('ticket_refund_events', ['refund_id' => $refund, 'actor_id' => $admin->id,
            'action'                                                  => 'assign', 'assigned_to' => $handler->id, 'previous_assigned_to' => null, 'version' => 1]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'cancelled']);
        $this->actingAs($reader)->patchJson($url, $this->settlement())->assertForbidden();
        $this->actingAs($admin)->patchJson($url, $this->settlement())->assertConflict();
        $this->actingAs($handler)->patchJson($url, $this->settlement())->assertOk();
        $this->assertDatabaseHas('ticket_refund_events', ['refund_id' => $refund, 'actor_id' => $handler->id,
            'action'                                                  => 'complete', 'assigned_to' => $handler->id, 'version' => 2, 'amount' => 315000]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket, 'status' => 'refunded']);
        Http::assertNothingSent();
    }

    public function test_assignment_rejects_inactive_cross_company_and_non_staff_users_without_mutation(): void
    {
        [$refund, , $company] = $this->fixture();
        $inactive = $this->staff($company);
        $inactive->update(['is_active' => false]);
        $otherCompany = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $outsider = $this->staff($otherCompany);
        $nonStaff = $this->operator($company);
        $this->actingAs($this->admin());
        foreach ([$inactive->id, $outsider->id, $nonStaff->id, 999999] as $handler) {
            $this->patchJson(route('refunds.update', $refund), ['action' => 'assign', 'assigned_to' => $handler, 'version' => 0])
                ->assertUnprocessable()->assertJsonValidationErrors('assigned_to');
        }
        $this->assertDatabaseHas('ticket_refunds', ['id' => $refund, 'version' => 0, 'assigned_to' => null, 'status' => 'refund_pending']);
        $this->assertDatabaseCount('ticket_refund_events', 0);
    }

    public function test_reassignment_requires_a_reason_and_prevents_old_handler_and_stale_admin_writes(): void
    {
        [$refund, , $company] = $this->fixture();
        $first = $this->staff($company);
        $second = $this->staff($company);
        $admin = $this->admin();
        $url = route('refunds.update', $refund);
        $this->actingAs($first)->patchJson($url, ['action' => 'assign', 'assigned_to' => $second->id, 'version' => 0])->assertForbidden();
        $this->actingAs($admin)->patchJson($url, ['action' => 'assign', 'assigned_to' => $first->id, 'version' => 0])->assertOk();
        $this->patchJson($url, ['action' => 'assign', 'assigned_to' => $second->id, 'version' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('note');
        $reassign = ['action' => 'assign', 'assigned_to' => $second->id, 'version' => 1, 'note' => 'Shift change'];
        $this->patchJson($url, $reassign)->assertOk()->assertJsonPath('version', 2);
        $this->patchJson($url, $reassign)->assertConflict();
        $this->assertDatabaseHas('ticket_refund_events', ['refund_id' => $refund, 'action' => 'assign',
            'actor_id'                                                => $admin->id, 'previous_assigned_to' => $first->id, 'assigned_to' => $second->id, 'note' => 'Shift change']);
        $this->actingAs($first)->patchJson($url, [...$this->settlement(), 'version' => 2])->assertConflict();
        $this->actingAs($second)->patchJson($url, [...$this->settlement(), 'version' => 2])->assertOk();
        $this->actingAs($admin)->patchJson($url, [...$reassign, 'version' => 3])->assertConflict();
        $this->assertDatabaseCount('ticket_refund_events', 3);
    }

    private function admin(): User
    {
        $admin = $this->createTestUser(['is_active' => true]);
        $role = DB::table('roles')->where('slug', 'admin')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $role]);

        return $admin;
    }

    private function staff(int $company, bool $write = true): User
    {
        $staff = $this->operator($company, $write);
        $role = DB::table('roles')->where('slug', 'staff')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $role]);

        return $staff;
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

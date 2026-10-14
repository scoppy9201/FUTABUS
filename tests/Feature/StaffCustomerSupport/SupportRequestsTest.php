<?php

namespace Tests\Feature\StaffCustomerSupport;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\CreatesFeedbackFixtures;
use Tests\TestCase;

class SupportRequestsTest extends TestCase
{
    use CreatesFeedbackFixtures;
    use RefreshDatabase;

    public function test_staff_claims_responds_and_resolves_with_idempotent_audit_and_customer_history(): void
    {
        [$owner, $staff, $report] = $this->fixture();
        $this->withToken($staff->createToken('support', ['api'])->plainTextToken);
        $this->getJson(route('api.v1.staff.support.index'))->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson(route('api.v1.staff.dashboard.show'))->assertOk()
            ->assertJsonPath('data.groups.support.requests', true)->assertJsonPath('data.statistics.support_requests', 1);
        $this->getJson(route('api.v1.staff.support.show', $report))->assertOk()->assertJsonPath('data.contact.phone', '0901234567');
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk()->assertJsonPath('data.version', 1);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk();
        $this->assertDatabaseCount('customer_support_events', 1);
        $payload = ['version' => 1, 'status' => 'resolved', 'response' => '<script>customer response</script>', 'idempotency_key' => (string) Str::uuid()];
        $result = $this->postJson(route('api.v1.staff.support.responses.store', $report), $payload)->assertCreated()
            ->assertJsonPath('data.status', 'resolved')->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson($result->headers->get('Location'))->assertOk()->assertJsonPath('data.response', $payload['response']);
        $this->postJson(route('api.v1.staff.support.responses.store', $report), $payload)->assertCreated()->assertJsonPath('data.id', $result->json('data.id'));
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [...$payload, 'response' => 'Changed response'])->assertConflict();
        $this->assertDatabaseCount('customer_support_events', 2);
        $this->assertDatabaseHas('customer_feedback_reports', ['id' => $report, 'status' => 'resolved', 'version' => 2]);
        $originalName = $staff->name;
        $staff->delete();
        $this->flushHeaders()->actingAs($owner)->get(route('profile.feedback.show', $report))->assertOk()
            ->assertSeeText($originalName)->assertSee('&lt;script&gt;customer response&lt;/script&gt;', false);
        $this->assertDatabaseHas('customer_support_events', ['id' => $result->json('data.id'), 'actor_id' => null, 'actor_name' => $originalName]);
    }

    public function test_claim_ownership_versions_validation_and_view_only_permissions_are_enforced(): void
    {
        [$owner, $staff, $report, $company] = $this->fixture();
        $other = $this->staff($company);
        $viewer = $this->staff($company, ['support.view']);
        $this->actingAs($viewer)->get(route('staff.support', $report))->assertOk();
        $this->withToken($viewer->createToken('view only', ['api'])->plainTextToken);
        $this->app['auth']->forgetGuards();
        $this->getJson(route('api.v1.staff.support.show', $report))->assertOk()->assertJsonPath('data.can_respond', false);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertForbidden();
        $this->flushHeaders()->withToken($staff->createToken('support', ['api'])->plainTextToken);
        $this->app['auth']->forgetGuards();
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 9])->assertConflict();
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk();
        $payload = ['version' => 1, 'status' => 'reviewing', 'response' => 'An update', 'idempotency_key' => (string) Str::uuid()];
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [...$payload, 'response' => '   '])
            ->assertUnprocessable()->assertJsonValidationErrors('response');
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [...$payload, 'status' => 'received'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [...$payload, 'version' => 0])->assertConflict();
        $this->postJson(route('api.v1.staff.support.responses.store', $report), $payload)->assertCreated();
        $this->app['auth']->forgetGuards();
        $this->withToken($other->createToken('other', ['api'])->plainTextToken);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 2])->assertConflict();
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [...$payload, 'version' => 2])->assertConflict();
        $this->assertDatabaseCount('customer_support_events', 2);
    }

    public function test_assigned_booking_reports_still_require_matching_trip_route_and_bus_company(): void
    {
        [$owner, $staff, $report, $company] = $this->fixture();
        $foreign = DB::table('bus_companies')->insertGetId(['name' => 'Foreign', 'code' => 'FOREIGN']);
        $booking = DB::table('customer_feedback_reports')->where('id', $report)->value('booking_id');
        $bus = DB::table('bookings')->join('trips', 'trips.id', '=', 'bookings.trip_id')->where('bookings.id', $booking)->value('trips.bus_id');
        DB::table('customer_feedback_reports')->where('id', $report)->update(['assigned_bus_company_id' => $company]);
        DB::table('buses')->where('id', $bus)->update(['bus_company_id' => $foreign]);
        $this->withToken($staff->createToken('support', ['api'])->plainTextToken);
        $this->getJson(route('api.v1.staff.support.index'))->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson(route('api.v1.staff.support.show', $report))->assertNotFound();
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertNotFound();
        $this->assertDatabaseCount('customer_support_events', 0);
    }

    public function test_admin_routes_unlinked_reports_and_transfer_revokes_previous_company_access(): void
    {
        [$owner, $staff, $linked, $company] = $this->fixture();
        $report = $this->createReport($owner, ['allow_contact' => false]);
        $admin = $this->createTestUser(['is_active' => true]);
        $role = DB::table('roles')->insertGetId(['name' => 'Admin', 'slug' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $role]);
        $foreign = DB::table('bus_companies')->insertGetId(['name' => 'Foreign', 'code' => 'FOREIGN']);
        $this->actingAs($owner)->get(route('staff.support-triage'))->assertForbidden();
        $this->actingAs($admin)->get(route('staff.support-triage'))->assertOk()->assertSeeText(__('StaffCustomerSupport::support.triage'));
        $this->put(route('staff.support-triage.assign', $linked), ['version' => 0, 'bus_company_id' => $foreign])->assertUnprocessable();
        $this->put(route('staff.support-triage.assign', $report), ['version' => 0, 'bus_company_id' => $company])->assertRedirect();
        $this->actingAs($staff)->getJson(route('api.v1.staff.support.show', $report))->assertOk()
            ->assertJsonPath('data.contact.phone', null)->assertJsonPath('data.contact.email', null);
        $this->actingAs($admin)->put(route('staff.support-triage.assign', $report), ['version' => 1, 'bus_company_id' => $foreign])->assertRedirect();
        $this->actingAs($staff)->getJson(route('api.v1.staff.support.show', $report))->assertNotFound();
        $this->actingAs($this->staff($foreign))->getJson(route('api.v1.staff.support.show', $report))->assertOk();
        $admin->update(['is_active' => false]);
        $this->actingAs($admin)->get(route('staff.support-triage'))->assertForbidden();
    }

    public function test_staff_token_ability_csrf_and_private_attachments_are_checked(): void
    {
        [$owner, $staff, $report] = $this->fixture();
        Storage::fake('local');
        $path = 'customer-feedback/'.$report.'/'.Str::uuid().'.png';
        Storage::disk('local')->put($path, 'private image');
        $attachment = DB::table('customer_feedback_attachments')->insertGetId([
            'report_id' => $report, 'path' => $path, 'original_name' => 'evidence.png', 'mime_type' => 'image/png', 'size_bytes' => 13,
        ]);
        $this->withToken($staff->createToken('restricted', ['other'])->plainTextToken)
            ->getJson(route('api.v1.staff.support.show', $report))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->withToken($staff->createToken('support', ['api'])->plainTextToken)
            ->get(route('api.v1.staff.support.attachments', ['report' => $report, 'attachment' => $attachment]))
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $another = $this->createReport($owner);
        $this->get(route('api.v1.staff.support.attachments', ['report' => $another, 'attachment' => $attachment]))->assertNotFound();
        $this->flushHeaders()->actingAs($staff)->withHeader('Origin', config('app.url'))->withSession(['_token' => 'support-csrf']);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertStatus(419);
        $this->withHeader('X-CSRF-TOKEN', 'support-csrf')->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk();
        $this->get(route('staff.support'))->assertOk()->assertSeeText(__('StaffCustomerSupport::support.title'));
    }

    public function test_transfer_hides_internal_reason_notifies_customer_and_restricts_claim_to_receiving_team(): void
    {
        [$owner, $staff, $report, $company] = $this->fixture();
        $receiver = $this->staff($company);
        $role = DB::table('roles')->where('slug', 'support-'.$receiver->id)->value('id');
        $this->withToken($staff->createToken('support', ['api'])->plainTextToken);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk();
        $note = ['version' => 1, 'content' => 'Private investigation only', 'idempotency_key' => (string) Str::uuid()];
        $result = $this->postJson(route('api.v1.staff.support.notes.store', $report), $note)->assertCreated();
        $this->getJson($result->headers->get('Location'))->assertOk()->assertJsonPath('data.internal_note', $note['content']);
        $this->postJson(route('api.v1.staff.support.notes.store', $report), $note)->assertCreated()->assertJsonPath('data.id', $result->json('data.id'));
        $this->assertDatabaseCount('notifications', 0);
        $transfer = ['version' => 2, 'content' => 'Private escalation reason', 'department_role_id' => $role, 'idempotency_key' => (string) Str::uuid()];
        $this->postJson(route('api.v1.staff.support.transfers.store', $report), [...$transfer, 'department_role_id' => 999999])->assertUnprocessable();
        $this->postJson(route('api.v1.staff.support.transfers.store', $report), $transfer)->assertCreated()->assertJsonPath('data.status', 'waiting');
        $this->postJson(route('api.v1.staff.support.transfers.store', $report), $transfer)->assertCreated();
        $this->assertDatabaseCount('notifications', 1);
        $this->putJson(route('api.v1.staff.support.claim', $report), ['version' => 3])->assertForbidden();
        $this->getJson(route('api.v1.staff.support.index', ['queue' => 'waiting']))->assertOk()->assertJsonPath('meta.total', 1);
        $this->flushHeaders()->actingAs($owner)->get(route('profile.feedback.show', $report))->assertOk()
            ->assertDontSee($note['content'])->assertDontSee($transfer['content'])->assertSeeText(__('StaffCustomerSupport::support.transfer_customer'));
        $this->get(route('profile.feedback.index'))->assertOk()->assertSeeText(__('StaffCustomerSupport::support.notification_title'));
        $this->app['auth']->forgetGuards();
        $this->withToken($receiver->createToken('support', ['api'])->plainTextToken)->putJson(route('api.v1.staff.support.claim', $report), ['version' => 3])->assertOk()->assertJsonPath('data.status', 'reviewing');
        $payload = ['version' => 4, 'status' => 'resolved', 'response' => 'Public answer', 'idempotency_key' => (string) Str::uuid()];
        $this->postJson(route('api.v1.staff.support.responses.store', $report), $payload)->assertCreated();
        $this->postJson(route('api.v1.staff.support.responses.store', $report), $payload)->assertCreated();
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_notification_failure_rolls_back_response_and_resolution(): void
    {
        [$owner, $staff, $report] = $this->fixture();
        $this->withToken($staff->createToken('support', ['api'])->plainTextToken)->putJson(route('api.v1.staff.support.claim', $report), ['version' => 0])->assertOk();
        DB::listen(function ($query): void {
            if (str_contains(str_replace(['"', '`'], '', $query->sql), 'insert into notifications')) {
                throw new \RuntimeException('Simulated notification persistence failure');
            }
        });
        $this->postJson(route('api.v1.staff.support.responses.store', $report), [
            'version' => 1, 'status' => 'resolved', 'response' => 'Must roll back', 'idempotency_key' => (string) Str::uuid(),
        ])->assertStatus(500);
        $this->assertDatabaseHas('customer_feedback_reports', ['id' => $report, 'status' => 'reviewing', 'version' => 1, 'response' => null]);
        $this->assertDatabaseCount('customer_support_events', 1);
        $this->assertDatabaseCount('notifications', 0);
    }

    private function fixture(): array
    {
        $owner = $this->createTestUser();
        $booking = $this->createBooking($owner);
        $company = (int) DB::table('bookings')->join('trips', 'trips.id', '=', 'bookings.trip_id')->where('bookings.id', $booking)->value('trips.bus_company_id');
        $report = $this->createReport($owner, ['booking_id' => $booking, 'booking_code' => 'BOOK-FEEDBACK-1']);

        return [$owner, $this->staff($company), $report, $company];
    }

    private function staff(int $company, array $permissions = ['support.view', 'support.respond']): User
    {
        $staff = $this->createTestUser(['is_active' => true, 'bus_company_id' => $company]);
        $base = DB::table('roles')->whereNull('bus_company_id')->where('slug', 'staff')->value('id');
        $base ??= DB::table('roles')->insertGetId(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $base]);
        $role = DB::table('roles')->insertGetId(['name' => 'Support '.$staff->id, 'slug' => 'support-'.$staff->id, 'bus_company_id' => $company]);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $role]);
        foreach ($permissions as $permission) {
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => DB::table('permissions')->where('slug', $permission)->value('id')]);
        }

        return $staff;
    }
}

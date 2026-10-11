<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use FuteBus\Profile\Services\FeedbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CreatesFeedbackFixtures;
use Tests\TestCase;

class FeedbackStatusTest extends TestCase
{
    use CreatesFeedbackFixtures, RefreshDatabase;

    public function test_processing_moves_forward_and_preserves_milestones_when_response_is_updated(): void
    {
        $admin = $this->userWithRole('admin');
        $owner = $this->createTestUser();
        $reportId = $this->createReport($owner);
        $service = app(FeedbackService::class);

        $this->travelTo(now()->startOfSecond());
        $reviewing = $service->updateStatus($admin, $reportId, 'reviewing', ' We are investigating. ');
        $reviewingAt = now()->toDateTimeString();
        $this->assertSame('reviewing', $reviewing->status);
        $this->assertSame('We are investigating.', $reviewing->response);
        $this->assertSame($reviewingAt, $reviewing->reviewing_at);
        $this->assertNull($reviewing->resolved_at);

        $this->travel(1)->hour();
        $sameState = $service->updateStatus($admin, $reportId, 'reviewing', 'The team is checking the pickup record.');
        $this->assertSame($reviewingAt, $sameState->reviewing_at);
        $this->assertNull($sameState->resolved_at);

        $this->travel(1)->hour();
        $resolved = $service->updateStatus($admin, $reportId, 'resolved', 'The pickup issue has been addressed.');
        $resolvedAt = now()->toDateTimeString();
        $this->assertSame('resolved', $resolved->status);
        $this->assertSame($reviewingAt, $resolved->reviewing_at);
        $this->assertSame($resolvedAt, $resolved->resolved_at);

        $this->travel(1)->day();
        $updatedResponse = $service->updateStatus($admin, $reportId, 'resolved', 'Final response updated.');
        $this->assertSame($reviewingAt, $updatedResponse->reviewing_at);
        $this->assertSame($resolvedAt, $updatedResponse->resolved_at);
        $this->assertSame('Final response updated.', $updatedResponse->response);
        $this->assertDatabaseHas('customer_feedback_reports', [
            'id' => $reportId, 'status' => 'resolved', 'response' => 'Final response updated.',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('profile.feedback.show', ['report' => $reportId, 'modal' => 1]))
            ->assertOk()
            ->assertSeeText(__('Profile::feedback.status.resolved'))
            ->assertSeeText('Final response updated.')
            ->assertSeeText(Carbon::parse($reviewingAt)->format('d/m/Y H:i'))
            ->assertSeeText(Carbon::parse($resolvedAt)->format('d/m/Y H:i'))
            ->assertSee('aria-current="step"', false);
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    #[DataProvider('invalidTransitions')]
    public function test_invalid_or_backward_processing_never_changes_the_report(string $current, string $next): void
    {
        $admin = $this->userWithRole('admin');
        $reportId = $this->createReport($this->createTestUser(), ['status' => $current]);
        $before = (array) DB::table('customer_feedback_reports')->find($reportId);

        try {
            app(FeedbackService::class)->updateStatus($admin, $reportId, $next, 'Attempted change.');
            $this->fail('An invalid feedback transition must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertSame($before, (array) DB::table('customer_feedback_reports')->find($reportId));
    }

    public static function invalidTransitions(): array
    {
        return [
            'skip reviewing'           => ['received', 'resolved'],
            'return to received'       => ['reviewing', 'received'],
            'reopen resolved'          => ['resolved', 'reviewing'],
            'reset resolved'           => ['resolved', 'received'],
            'unknown requested status' => ['received', 'cancelled'],
            'corrupt current status'   => ['unknown', 'reviewing'],
        ];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_processing_is_restricted_to_an_administrator(string $role): void
    {
        $user = $this->userWithRole($role);
        $reportId = $this->createReport($user);

        try {
            app(FeedbackService::class)->updateStatus($user, $reportId, 'reviewing', 'Unauthorized change.');
            $this->fail('Only an administrator may process a customer report.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('customer_feedback_reports', [
            'id' => $reportId, 'status' => 'received', 'response' => null, 'reviewing_at' => null,
        ]);
    }

    public static function nonAdminRoles(): array
    {
        return ['customer' => ['customer'], 'staff' => ['staff'], 'bus company' => ['bus-company']];
    }

    public function test_overlong_response_is_rejected_without_changing_processing_state(): void
    {
        $admin = $this->userWithRole('admin');
        $reportId = $this->createReport($this->createTestUser());

        try {
            app(FeedbackService::class)->updateStatus($admin, $reportId, 'reviewing', str_repeat('x', 3001));
            $this->fail('An overlong processing response must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('response', $exception->errors());
        }

        $this->assertDatabaseHas('customer_feedback_reports', [
            'id' => $reportId, 'status' => 'received', 'response' => null, 'reviewing_at' => null,
        ]);
    }

    public function test_missing_report_does_not_create_a_record_when_processed(): void
    {
        try {
            app(FeedbackService::class)->updateStatus($this->userWithRole('admin'), 99999, 'reviewing', null);
            $this->fail('A nonexistent feedback report must not be processed.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('customer_feedback_reports', 0);
    }

    private function userWithRole(string $role): User
    {
        $user = $this->createTestUser();
        $roleId = DB::table('roles')->insertGetId(['name' => $role, 'slug' => $role]);
        DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }
}

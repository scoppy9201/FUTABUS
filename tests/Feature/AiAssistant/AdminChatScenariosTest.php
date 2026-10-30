<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use FuteBus\AiAssistant\Models\AiChatSession;
use FuteBus\AiAssistant\Services\LlmService;
use FuteBus\Reporting\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminChatScenariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = $this->createTestUser(['name' => 'Test Admin', 'is_active' => true]);
        $role = DB::table('roles')->insertGetId(['slug' => 'admin', 'name' => 'Admin']);
        DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $admin->id]);
        $this->assertTrue($admin->isAdmin());
        $this->actingAs($admin);
        config(['services.openai.api_key' => '']);
        Http::preventStrayRequests();
    }

    public static function guidance(): array
    {
        return [
            'booking'   => ['mình muốn hỏi cách đặt vé', 'trip-search', 'booking_guide'],
            'lookup'    => ['tra cứu vé', 'ticket-lookup', 'ticket_lookup_guidance'],
            'cancel'    => ['hướng dẫn hủy vé', 'profile.tickets.index', 'cancellation_handoff'],
            'change'    => ['mình muốn đổi vé', 'profile.tickets.index', 'change_handoff'],
            'feedback'  => ['mình muốn góp ý dịch vụ', 'profile.feedback.index', 'feedback_handoff'],
            'branches'  => ['các điểm giao dịch ở đâu', 'branches', 'branches_guidance'],
            'schedules' => ['thông tin khởi hành và giá vé', 'schedules', 'schedules_guidance'],
        ];
    }

    #[DataProvider('guidance')]
    public function test_admin_can_chat_without_customer_intake(string $message, string $route, string $copy): void
    {
        $this->postJson(route('ai-assistant.ask'), ['message' => $message])->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.'.$copy))
            ->assertJsonPath('pending_action.url', route($route));
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_admin_report_uses_futa_company_and_real_report_service_with_empty_data(): void
    {
        DB::table('bus_companies')->insert(['code' => 'FUTA', 'name' => 'FUTA']);
        $this->postJson(route('ai-assistant.ask'), ['message' => 'xem báo cáo doanh thu'])->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.report_summary'))
            ->assertJsonCount(3, 'metrics')
            ->assertJsonPath('metrics.0.value', 0)
            ->assertJsonPath('metrics.1.value', 0)
            ->assertJsonPath('pending_action.url', route('reporting.index'));
    }

    public function test_llm_report_tool_uses_server_company_and_seven_day_filters(): void
    {
        $company = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA']);
        $other = DB::table('bus_companies')->insertGetId(['code' => 'OTHER', 'name' => 'Other']);
        $this->mock(LlmService::class)->shouldReceive('chat')->once()->andReturn([
            'tool_calls' => [['function' => ['name' => 'get_report', 'arguments' => json_encode(['company_id' => $other])]]],
        ]);
        $this->mock(ReportService::class)->shouldReceive('build')->once()
            ->withArgs(fn (int $id, array $filters): bool => $id === $company
                && $filters['period'] === '7days'
                && $filters['from'] === now('Asia/Ho_Chi_Minh')->subDays(6)->toDateString()
                && $filters['to'] === now('Asia/Ho_Chi_Minh')->toDateString())
            ->andReturn(['kpis' => ['net_revenue' => 1200000, 'tickets_sold' => 5, 'occupancy' => 25]]);
        $this->postJson(route('ai-assistant.ask'), ['message' => 'tổng hợp số liệu'])->assertOk()
            ->assertJsonPath('metrics.0.value', 1200000)
            ->assertJsonPath('metrics.1.value', 5)
            ->assertJsonPath('metrics.2.value', 25);
    }

    public function test_report_without_configured_company_returns_an_explanation(): void
    {
        $this->postJson(route('ai-assistant.ask'), ['message' => 'xem báo cáo'])->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.report_unavailable'))
            ->assertJsonMissingPath('metrics');
    }

    public function test_report_failure_does_not_invent_metrics(): void
    {
        DB::table('bus_companies')->insert(['code' => 'FUTA', 'name' => 'FUTA']);
        $this->mock(ReportService::class)->shouldReceive('build')->once()->andThrow(new \RuntimeException('Test outage'));
        $this->postJson(route('ai-assistant.ask'), ['message' => 'xem doanh thu'])->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.report_failed'))
            ->assertJsonMissingPath('metrics');
    }

    public function test_admin_conversation_retains_history_without_contact_details(): void
    {
        $first = $this->postJson(route('ai-assistant.ask'), ['message' => 'cách đặt vé'])->assertOk();
        $id = $first->json('session_id');
        $this->postJson(route('ai-assistant.ask'), ['message' => 'tra cứu vé', 'session_id' => $id])
            ->assertOk()->assertJsonPath('session_id', $id);
        $session = AiChatSession::findOrFail($id);
        $this->assertCount(4, $session->history);
        $this->assertSame(auth()->id(), $session->user_id);
        $this->assertArrayNotHasKey('customer', $session->context);
    }

    public function test_unknown_session_is_rejected_even_for_admin(): void
    {
        $session = AiChatSession::create(['history' => [], 'context' => [], 'last_active_at' => now()]);
        $this->postJson(route('ai-assistant.ask'), ['message' => 'xem báo cáo', 'session_id' => $session->id])->assertNotFound();
    }

    public function test_admin_message_length_is_still_validated(): void
    {
        $this->postJson(route('ai-assistant.ask'), ['message' => str_repeat('a', 501)])
            ->assertUnprocessable()->assertJsonValidationErrors('message');
    }
}

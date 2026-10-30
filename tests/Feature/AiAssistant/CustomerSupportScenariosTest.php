<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use FuteBus\AiAssistant\Services\LlmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerSupportScenariosTest extends TestCase
{
    use RefreshDatabase;

    public static function supportScenarios(): array
    {
        $cases = [
            'booking instructions'       => ['mình muốn hỏi cách đặt vé', 'trip-search', 'booking_guide'],
            'ticket lookup'              => ['mình muốn tra cứu vé', 'ticket-lookup', 'ticket_lookup_guidance'],
            'cancel ticket'              => ['mình muốn hủy vé', 'ticket-lookup', 'cancellation_handoff'],
            'change ticket'              => ['mình muốn đổi vé', 'ticket-lookup', 'change_handoff'],
            'service feedback'           => ['mình muốn phản hồi góp ý dịch vụ', 'contact', 'feedback_handoff'],
            'branches'                   => ['mình muốn biết thông tin các điểm giao dịch', 'branches', 'branches_guidance'],
            'schedules and fares'        => ['mình muốn hỏi thông tin khởi hành và giá vé', 'schedules', 'schedules_guidance'],
            'booking with greeting'      => ['chào bạn, mình muốn hỏi cách đặt vé', 'trip-search', 'booking_guide'],
            'cancellation with greeting' => ['chào bạn, mình muốn hủy vé', 'ticket-lookup', 'cancellation_handoff'],
            'ticket lookup correction'   => ['tra cứu chứ không phải tìm kiếm', 'ticket-lookup', 'ticket_lookup_guidance'],
        ];
        $result = [];
        foreach ([false, true] as $loggedIn) {
            foreach ($cases as $label => [$message, $route, $copy]) {
                if ($loggedIn && in_array($copy, ['cancellation_handoff', 'change_handoff'], true)) {
                    $route = 'profile.tickets.index';
                }
                if ($loggedIn && $copy === 'feedback_handoff') {
                    $route = 'profile.feedback.index';
                }
                $result[($loggedIn ? 'customer: ' : 'guest: ').$label] = [$loggedIn, $message, $route, $copy];
            }
        }

        return $result;
    }

    #[DataProvider('supportScenarios')]
    public function test_customer_receives_relevant_guidance_when_ai_is_unavailable(bool $loggedIn, string $message, string $route, string $copy): void
    {
        if ($loggedIn) {
            $this->actingAs($this->createTestUser());
        }
        $this->mock(LlmService::class)->shouldReceive('chat')->andThrow(new \RuntimeException('Simulated AI outage'));
        $this->ask($message)->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.'.$copy))
            ->assertJsonPath('pending_action.url', route($route))
            ->assertJsonMissingPath('trips')->assertJsonMissingPath('ticket');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_customer_cannot_access_operational_reports(): void
    {
        $this->actingAs($this->createTestUser());
        $this->mock(LlmService::class)->shouldReceive('chat')->andThrow(new \RuntimeException('Simulated AI outage'));
        $this->ask('cho mình xem báo cáo doanh thu')->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.report_permission_required'))
            ->assertJsonMissingPath('metrics');
    }

    public function test_trip_search_requests_missing_details_instead_of_giving_booking_instructions(): void
    {
        $this->mock(LlmService::class)->shouldReceive('chat')->andThrow(new \RuntimeException('Simulated AI outage'));
        $this->ask('mình muốn tìm chuyến xe')->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.trip_details_required'));
    }

    public function test_ai_tool_response_produces_a_navigation_offer_for_customer_support(): void
    {
        $this->actingAs($this->createTestUser());
        config(['services.openai.api_key' => 'fake-test-key', 'services.openai.base_url' => 'https://ai.test/v1']);
        Http::preventStrayRequests();
        Http::fake(['ai.test/*' => Http::response(['choices' => [['message' => ['tool_calls' => [
            ['function' => ['name' => 'confirm_navigation', 'arguments' => json_encode([
                'url' => route('profile.feedback.index'), 'label' => 'Gửi phản hồi', 'description' => 'Mở mục phản hồi để gửi góp ý dịch vụ.',
            ])]],
        ]]]]])]);
        $this->ask('góp ý về thái độ phục vụ')->assertOk()
            ->assertJsonPath('pending_action.url', route('profile.feedback.index'));
        Http::assertSentCount(1);
    }

    private function ask(string $message): TestResponse
    {
        return $this->postJson(route('ai-assistant.ask'), [
            'message'  => $message,
            'customer' => ['name' => 'Test Customer', 'phone' => '0912345678', 'phone_country' => 'VN', 'topic' => 'other', 'content' => $message],
        ]);
    }
}

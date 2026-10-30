<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use FuteBus\AiAssistant\Models\AiChatSession;
use FuteBus\AiAssistant\Services\LlmService;
use FuteBus\TicketManagement\Services\TicketLookupService;
use FuteBus\TripSearch\Services\TripSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class AiAssistantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchased_ticket_questions_offer_the_system_lookup_without_searching_trips(): void
    {
        $this->mock(LlmService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('chat'));
        $this->mock(TripSearchService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('search'));
        $this->mock(TicketLookupService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('find'));

        foreach (['mình muốn tra cứu vé', 'tra cứu chứ không phải tìm kiếm', 'kiểm tra vé đã mua', 'mã vé ABC123', 'tra cuu ve', 'check my ticket'] as $message) {
            $response = $this->postJson(route('ai-assistant.ask'), $this->payload($message))
                ->assertOk()
                ->assertJsonPath('message', __('AiAssistant::app.ticket_lookup_guidance', [], $message === 'check my ticket' ? 'en' : 'vi'))
                ->assertJsonPath('pending_action.url', route('ticket-lookup'))
                ->assertJsonPath('pending_action.label', __('AiAssistant::app.open_ticket_lookup', [], $message === 'check my ticket' ? 'en' : 'vi'))
                ->assertJsonMissingPath('trips')
                ->assertJsonMissingPath('ticket');

            $this->assertSame('ticket_lookup', AiChatSession::findOrFail($response->json('session_id'))->context['intent']);
        }
    }

    public function test_ticket_lookup_overrides_previous_trip_search_context(): void
    {
        $this->mock(LlmService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('chat')->once()->andReturn([
                'tool_calls' => [['function' => ['name' => 'search_trips', 'arguments' => '{"departure":"Hà Nội"}']]],
            ]);
        });
        $first = $this->postJson(route('ai-assistant.ask'), $this->payload('tìm chuyến từ Hà Nội'))
            ->assertOk();

        $this->postJson(route('ai-assistant.ask'), [
            ...$this->payload('mình muốn tra cứu vé'),
            'session_id' => $first->json('session_id'),
        ])->assertOk()
            ->assertJsonPath('pending_action.url', route('ticket-lookup'))
            ->assertJsonPath('message', __('AiAssistant::app.ticket_lookup_guidance'))
            ->assertJsonMissingPath('trips');
    }

    public function test_llm_ticket_lookup_tool_hands_off_without_querying_private_booking_data(): void
    {
        $this->mock(LlmService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('chat')->once()->andReturn([
                'tool_calls' => [['function' => ['name' => 'lookup_ticket', 'arguments' => '{}']]],
            ]);
        });
        $this->mock(TicketLookupService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('find'));

        $this->postJson(route('ai-assistant.ask'), $this->payload('I have already paid and want to see my booking'))
            ->assertOk()
            ->assertJsonPath('pending_action.url', route('ticket-lookup'))
            ->assertJsonMissingPath('ticket');
    }

    public function test_chat_rejects_messages_longer_than_the_input_limit(): void
    {
        $this->postJson(route('ai-assistant.ask'), $this->payload(str_repeat('a', 501)))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');
    }

    public function test_operational_reports_are_not_returned_to_customers_without_report_permission(): void
    {
        $this->mock(LlmService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('chat')->once()->andReturn([
                'tool_calls' => [['function' => ['name' => 'get_report', 'arguments' => '{}']]],
            ]);
        });

        $this->postJson(route('ai-assistant.ask'), $this->payload('show revenue'))
            ->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.report_permission_required', [], 'en'))
            ->assertJsonMissingPath('metrics');
    }

    public function test_email_is_optional_but_is_validated_when_provided(): void
    {
        $payload = $this->payload('tra cứu vé');
        unset($payload['customer']['email']);
        $this->postJson(route('ai-assistant.ask'), $payload)->assertOk();
        $payload['customer']['email'] = 'invalid-email';
        $this->postJson(route('ai-assistant.ask'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('customer.email');
    }

    public function test_name_phone_and_support_topic_are_required(): void
    {
        $payload = $this->payload('tra cứu vé');
        unset($payload['customer']['name'], $payload['customer']['phone'], $payload['customer']['topic']);
        $response = $this->postJson(route('ai-assistant.ask'), $payload)->assertUnprocessable()
            ->assertJsonValidationErrors(['customer.name', 'customer.phone', 'customer.topic']);
        $this->assertSame(__('AiAssistant::app.phone_incomplete'), $response->json('errors')['customer.phone'][0]);
    }

    public function test_invalid_phone_is_rejected_even_without_an_email(): void
    {
        $payload = $this->payload('tra cứu vé');
        unset($payload['customer']['email']);
        $payload['customer']['phone'] = '123';
        $this->postJson(route('ai-assistant.ask'), $payload)->assertUnprocessable()
            ->assertJsonValidationErrors('customer.phone');
    }

    public function test_booking_instructions_are_not_treated_as_a_trip_search(): void
    {
        $this->mock(LlmService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('chat'));
        $this->mock(TripSearchService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('search'));
        foreach (['mình muốn hỏi cách đặt vé', 'hướng dẫn mua vé', 'đặt vé như thế nào', 'làm sao để đặt vé', 'how do I book a ticket'] as $message) {
            $this->postJson(route('ai-assistant.ask'), $this->payload($message))->assertOk()
                ->assertJsonPath('message', __('AiAssistant::app.booking_guide', [], $message === 'how do I book a ticket' ? 'en' : 'vi'))
                ->assertJsonPath('pending_action.url', route('trip-search'))
                ->assertJsonMissingPath('trips');
        }
    }

    private function payload(string $message): array
    {
        return [
            'message'  => $message,
            'customer' => [
                'phone'         => '0912345678',
                'phone_country' => 'VN',
                'name'          => 'Test Customer',
                'email'         => 'customer@example.test',
                'content'       => 'Hỗ trợ tra cứu vé',
                'topic'         => 'booking',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use FuteBus\AiAssistant\Services\LlmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnglishChatScenariosTest extends TestCase
{
    use RefreshDatabase;

    public static function englishScenarios(): array
    {
        $cases = [
            'booking guide' => ['How do I book a ticket?', 'booking_guide', 'trip-search'],
            'ticket lookup' => ['I want to check my ticket', 'ticket_lookup_guidance', 'ticket-lookup'],
            'cancellation'  => ['How can I cancel my ticket?', 'cancellation_handoff', 'ticket-lookup'],
            'change ticket' => ['How can I change my ticket?', 'change_handoff', 'ticket-lookup'],
            'feedback'      => ['I want to give feedback', 'feedback_handoff', 'contact'],
            'branches'      => ['Where are your branches?', 'branches_guidance', 'branches'],
            'schedules'     => ['Show me the schedules', 'schedules_guidance', 'schedules'],
            'trip search'   => ['Find a bus trip', 'trip_details_required', null],
            'reports'       => ['Show revenue report', 'report_permission_required', null],
        ];
        $result = [];
        foreach (['vi', 'en'] as $locale) {
            foreach (['guest', 'customer', 'admin'] as $actor) {
                foreach ($cases as $name => [$message, $key, $route]) {
                    if ($actor !== 'guest' && in_array($key, ['cancellation_handoff', 'change_handoff'], true)) {
                        $route = 'profile.tickets.index';
                    }
                    if ($actor !== 'guest' && $key === 'feedback_handoff') {
                        $route = 'profile.feedback.index';
                    }
                    if ($actor === 'admin' && $key === 'report_permission_required') {
                        $key = 'report_summary';
                        $route = 'reporting.index';
                    }
                    $result["$locale UI / $actor / $name"] = [$locale, $actor, $message, $key, $route];
                }
            }
        }

        return $result;
    }

    public static function ticketChangeQuestions(): array
    {
        $cases = [];
        foreach (['vi', 'en'] as $locale) {
            foreach (['guest', 'customer'] as $actor) {
                foreach (['How can I change my ticket?', 'I want to reschedule my ticket', 'Can I exchange my ticket?', 'Please change my booking', 'Hello, can I change my trip?', 'How do I change my travel date?', 'Can I change the date of my ticket?'] as $message) {
                    $cases["$locale / $actor / $message"] = [$locale, $actor, $message];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('ticketChangeQuestions')]
    public function test_english_ticket_change_is_guided_in_english_on_either_interface(string $locale, string $actor, string $message): void
    {
        $this->mock(LlmService::class)->shouldNotReceive('chat');
        if ($actor === 'customer') {
            $user = $this->createTestUser();
            $role = DB::table('roles')->insertGetId(['slug' => 'customer', 'name' => 'Customer']);
            DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $user->id]);
            $this->actingAs($user);
        }
        $this->withSession(['locale' => $locale])->postJson(route('ai-assistant.ask'), [
            'message'  => $message,
            'customer' => ['name' => 'Test Customer', 'phone' => '0912345678', 'phone_country' => 'VN', 'topic' => 'other', 'content' => $message],
        ])->assertOk()
            ->assertJsonPath('message', __('AiAssistant::app.change_handoff', [], 'en'))
            ->assertJsonPath('pending_action.label', __('AiAssistant::app.continue_change', [], 'en'))
            ->assertJsonPath('pending_action.url', route($actor === 'customer' ? 'profile.tickets.index' : 'ticket-lookup'))
            ->assertJsonMissingPath('trips')->assertJsonMissingPath('ticket');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    #[DataProvider('englishScenarios')]
    public function test_english_question_receives_english_guidance(string $locale, string $actor, string $message, string $key, ?string $route): void
    {
        config(['services.openai.api_key' => '']);
        Http::preventStrayRequests();
        if ($actor !== 'guest') {
            $user = $this->createTestUser(['is_active' => true]);
            $role = DB::table('roles')->insertGetId(['slug' => $actor, 'name' => ucfirst($actor)]);
            DB::table('role_user')->insert(['role_id' => $role, 'user_id' => $user->id]);
            $this->actingAs($user);
        }
        if ($actor === 'admin') {
            DB::table('bus_companies')->insert(['code' => 'FUTA', 'name' => 'FUTA']);
        }
        $payload = ['message' => $message];
        if ($actor !== 'admin') {
            $payload['customer'] = ['name' => 'Test Customer', 'phone' => '0912345678', 'phone_country' => 'VN', 'topic' => 'other', 'content' => $message];
        }
        $response = $this->withSession(['locale' => $locale])->postJson(route('ai-assistant.ask'), $payload)->assertOk();
        $response->assertJsonPath('message', __('AiAssistant::app.'.$key, [], 'en'));
        if ($route !== null) {
            $response->assertJsonPath('pending_action.url', route($route));
        }
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }
}

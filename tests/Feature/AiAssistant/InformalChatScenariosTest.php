<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use FuteBus\AiAssistant\Models\AiChatSession;
use FuteBus\AiAssistant\Services\LlmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InformalChatScenariosTest extends TestCase
{
    use RefreshDatabase;

    public static function informalQuestions(): array
    {
        $questions = [
            'vi abbreviation booking'  => ['vi', 'hd dat ve giup mik', 'booking_guide', 'trip-search'],
            'vi slang booking'         => ['vi', 'chỉ mình cách đặt vé nha', 'booking_guide', 'trip-search'],
            'vi slang change'          => ['vi', 'mún đổi vé á bạn', 'change_handoff', 'ticket-lookup'],
            'vi unaccented change'     => ['vi', 'doi ve dc ko', 'change_handoff', 'ticket-lookup'],
            'vi typo lookup'           => ['vi', 'tra cu ve giup minh', 'ticket_lookup_guidance', 'ticket-lookup'],
            'vi abbreviation lookup'   => ['vi', 'check vé giúp mik', 'ticket_lookup_guidance', 'ticket-lookup'],
            'vi unaccented lookup'     => ['vi', 'tra cuu ve AB12-CD', 'ticket_lookup_guidance', 'ticket-lookup'],
            'vi slang cancellation'    => ['vi', 'huy ve giup mik nha', 'cancellation_handoff', 'ticket-lookup'],
            'vi typo cancellation'     => ['vi', 'mình muốn huỷ vé', 'cancellation_handoff', 'ticket-lookup'],
            'vi abbreviation feedback' => ['vi', 'cho mik gui phan hoi dv', 'feedback_handoff', 'contact'],
            'vi abbreviation branches' => ['vi', 'cho xin dc phong ve', 'branches_guidance', 'branches'],
            'vi abbreviation report'   => ['vi', 'xem bc dt', 'report_permission_required', null],
            'vi slang report'          => ['vi', 'cho mik xem doanh thu nha', 'report_permission_required', null],
            'en slang change'          => ['en', 'plz change my ticket', 'change_handoff', 'ticket-lookup'],
            'en abbreviation change'   => ['en', 'pls change my tkt', 'change_handoff', 'ticket-lookup'],
            'en typo change'           => ['en', 'can u reshedule my ticket', 'change_handoff', 'ticket-lookup'],
            'en typo ticket'           => ['en', 'exchange my tickt pls', 'change_handoff', 'ticket-lookup'],
            'en abbreviation lookup'   => ['en', 'check my tkt pls', 'ticket_lookup_guidance', 'ticket-lookup'],
            'en slang refund'          => ['en', 'gimme a refund pls', 'cancellation_handoff', 'ticket-lookup'],
            'en abbreviation cancel'   => ['en', 'pls cancel my tkt', 'cancellation_handoff', 'ticket-lookup'],
            'en typo feedback'         => ['en', 'send feedbak pls', 'feedback_handoff', 'contact'],
            'en typo branches'         => ['en', 'where r ur branchs', 'branches_guidance', 'branches'],
            'en typo reports'          => ['en', 'show revnue rpt', 'report_permission_required', null],
            'en informal reports'      => ['en', 'show revenue pls', 'report_permission_required', null],
        ];
        $cases = [];
        foreach (['guest', 'customer', 'admin'] as $actor) {
            foreach ($questions as $name => [$locale, $message, $key, $route]) {
                if ($actor !== 'guest' && in_array($key, ['change_handoff', 'cancellation_handoff'], true)) {
                    $route = 'profile.tickets.index';
                }
                if ($actor !== 'guest' && $key === 'feedback_handoff') {
                    $route = 'profile.feedback.index';
                }
                if ($actor === 'admin' && $key === 'report_permission_required') {
                    $key = 'report_summary';
                    $route = 'reporting.index';
                }
                $cases["$actor / $name"] = [$actor, $locale, $message, $key, $route];
            }
        }

        return $cases;
    }

    #[DataProvider('informalQuestions')]
    public function test_informal_support_request(string $actor, string $locale, string $message, string $key, ?string $route): void
    {
        config(['services.openai.api_key' => '']);
        Http::preventStrayRequests();
        if ($actor !== 'guest') {
            $user = $this->createTestUser(['is_active' => true]);
            $role = DB::table('roles')->insertGetId(['slug' => $actor, 'name' => ucfirst($actor)]);
            DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $role]);
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
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
        $response->assertJsonPath('message', __('AiAssistant::app.'.$key, [], $locale));
        if ($route !== null) {
            $response->assertJsonPath('pending_action.url', route($route));
        }
    }

    public function test_intent_normalization_preserves_original_message_in_model_input_and_history(): void
    {
        $message = 'send feedbak pls, reference HD-TKT-ABC123';
        $this->mock(LlmService::class)->shouldReceive('chat')->once()
            ->withArgs(fn (array $messages): bool => end($messages)['content'] === $message)
            ->andThrow(new \RuntimeException('Simulated outage'));
        $response = $this->withSession(['locale' => 'en'])->postJson(route('ai-assistant.ask'), [
            'message'  => $message,
            'customer' => ['name' => 'Test Customer', 'phone' => '0912345678', 'phone_country' => 'VN', 'topic' => 'other', 'content' => $message],
        ])->assertOk()->assertJsonPath('pending_action.url', route('contact'));
        $session = AiChatSession::findOrFail($response->json('session_id'));
        $this->assertSame($message, $session->history[0]['content']);
        $this->assertSame($message, $session->context['customer']['content']);
        $this->assertSame('+84912345678', $session->context['customer']['phone']);
    }

    public function test_ambiguous_words_or_contact_changes_do_not_trigger_ticket_navigation(): void
    {
        config(['services.openai.api_key' => '']);
        Http::preventStrayRequests();
        foreach (['đổi', 'đổi sđt', 'change my email', 'tkt?'] as $message) {
            $this->postJson(route('ai-assistant.ask'), [
                'message'  => $message,
                'customer' => ['name' => 'Test Customer', 'phone' => '0912345678', 'phone_country' => 'VN', 'topic' => 'other', 'content' => $message],
            ])->assertOk()->assertJsonMissingPath('pending_action')->assertJsonMissingPath('ticket');
        }
    }
}

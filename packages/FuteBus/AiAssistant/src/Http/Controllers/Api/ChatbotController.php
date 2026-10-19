<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Http\Controllers\Api;

use FuteBus\AiAssistant\Http\Requests\ChatbotMessageRequest;
use FuteBus\AiAssistant\Models\AiChatHistory;
use FuteBus\AiAssistant\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotController extends Controller
{
    public function __invoke(ChatbotMessageRequest $request, GeminiChatService $chat): JsonResponse
    {
        $locale = $request->validated('locale', app()->getLocale());

        try {
            $result = $chat->reply($request->validated('messages'), $locale);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => Lang::get('core::chatbot.unavailable', [], $locale)], 503)
                ->header('Cache-Control', 'no-store');
        }

        $this->storeLatestTurn($request->user()?->getAuthIdentifier(), $request->validated('messages'), $locale, $result);

        $action = match ($result['intent']) {
            'ticket_lookup' => [
                'type' => 'link', 'label' => Lang::get('core::chatbot.actions.ticket_lookup', [], $locale), 'url' => route('ticket-lookup'),
            ],
            'exchange' => [
                'type' => 'link', 'label' => Lang::get('core::chatbot.actions.exchange', [], $locale),
                'url'  => route('profile.tickets.index'),
            ],
            'cancel' => [
                'type' => 'link', 'label' => Lang::get('core::chatbot.actions.cancel', [], $locale),
                'url'  => route('profile.tickets.index'),
            ],
            'complaint' => [
                'type' => 'feedback', 'label' => Lang::get('core::chatbot.actions.complaint', [], $locale),
            ],
            'unsupported' => [
                'type' => 'link', 'label' => Lang::get('core::chatbot.actions.contact', [], $locale), 'url' => route('contact'),
            ],
            default => null,
        };

        return response()->json([
            'reply'  => $result['reply'],
            'intent' => $result['intent'],
            'action' => $action,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $messages
     * @param  array{reply: string, intent: string}  $result
     */
    private function storeLatestTurn(?int $userId, array $messages, string $locale, array $result): void
    {
        if ($userId === null) {
            return;
        }

        $latestUserMessage = collect($messages)->last(fn (array $message): bool => $message['role'] === 'user');
        if ($latestUserMessage === null) {
            return;
        }

        try {
            DB::transaction(function () use ($userId, $latestUserMessage, $locale, $result): void {
                AiChatHistory::query()->create([
                    'user_id'  => $userId,
                    'role'     => 'user',
                    'content'  => $latestUserMessage['text'],
                    'metadata' => ['locale' => $locale],
                ]);
                AiChatHistory::query()->create([
                    'user_id'  => $userId,
                    'role'     => 'assistant',
                    'content'  => $result['reply'],
                    'metadata' => ['locale' => $locale, 'intent' => $result['intent']],
                ]);
            });
        } catch (Throwable $exception) {
            Log::warning('Could not persist chatbot history.', ['exception' => $exception::class]);
        }
    }
}

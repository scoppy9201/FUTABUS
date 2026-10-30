<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Http\Controllers;

use FuteBus\AiAssistant\Http\Requests\ChatRequest;
use FuteBus\AiAssistant\Services\ChatAssistant;
use FuteBus\AiAssistant\Support\PhoneNumbers;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use libphonenumber\PhoneNumberFormat;

class ChatController extends Controller
{
    public function __invoke(ChatRequest $request, ChatAssistant $assistant): JsonResponse
    {
        $input = $request->validated();

        if (! empty($input['customer']['phone'])) {
            $util = PhoneNumbers::util();
            $input['customer']['phone'] = $util->format(
                $util->parse($input['customer']['phone'], $input['customer']['phone_country']),
                PhoneNumberFormat::E164,
            );
        }

        $sessionIds = $request->session()->get('ai_assistant.sessions', []);
        if (isset($input['session_id']) && ! in_array($input['session_id'], $sessionIds, true)) {
            abort(404);
        }

        $result = $assistant->reply(
            $input['message'],
            $input['session_id'] ?? null,
            $request->user(),
            $input['customer'] ?? null,
        );
        $sessionIds[] = $result['session_id'];
        $request->session()->put('ai_assistant.sessions', array_slice(array_unique($sessionIds), -20));

        return response()->json($result);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Shared helpers dùng chung giữa AIAssistantController và các AI sub-controllers.
 * Tách ra để tránh circular dependency khi controller cha và con inject lẫn nhau.
 */
class AIService
{
    // Pending action 
    public function savePendingAction(int $userId, string $action, array $data): void
    {
        DB::table('ai_pending_actions')->updateOrInsert(
            ['user_id' => $userId],
            [
                'action'     => $action,
                'data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function getPendingAction(int $userId): ?array
    {
        $row = DB::table('ai_pending_actions')
            ->where('user_id', $userId)
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->first();

        if (!$row) return null;

        return ['action' => $row->action, 'data' => json_decode($row->data, true)];
    }

    public function clearPendingAction(int $userId): void
    {
        DB::table('ai_pending_actions')->where('user_id', $userId)->delete();
    }

    // Chat history 
    public function saveChatHistory(int $userId, string $userMessage, string $aiResponse): void
    {
        DB::table('ai_chat_history')->insert([
            'user_id'      => $userId,
            'user_message' => $userMessage,
            'ai_response'  => $aiResponse,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function getChatHistory(int $userId): array
    {
        $rows = DB::table('ai_chat_history')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $history = [];
        foreach ($rows as $item) {
            $history[] = ['role' => 'user',  'parts' => [['text' => $item->user_message]]];
            $history[] = ['role' => 'model', 'parts' => [['text' => $item->ai_response]]];
        }

        return $history;
    }

    // Text helpers 
    public function formatResponse(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/',     '$1', $text);
        $text = preg_replace('/#{1,6}\s/',      '',   $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    public function parseGeminiResponse(string $raw, string $fallbackIntent = 'CHAT'): array
    {
        $text = trim($raw);

        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $m)) {
            $text = trim($m[1]);
        } elseif (preg_match('/(\{.*\})/s', $text, $m)) {
            $text = trim($m[1]);
        }

        $parsed = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($parsed['intent'])) {
            return $parsed;
        }

        // Log để debug
        \Illuminate\Support\Facades\Log::warning('Gemini parse failed', [
            'raw'        => $raw,
            'cleaned'    => $text,
            'json_error' => json_last_error_msg(),
        ]);

        if (preg_match('/"intent"\s*:\s*"([^"]+)"/', $text, $intentMatch)) {
            $intent = $intentMatch[1];

            if ($intent === 'CHAT' && preg_match('/"message"\s*:\s*"(.*?)(?:"|$)/s', $text, $msgMatch)) {
                return ['intent' => 'CHAT', 'message' => $msgMatch[1]];
            }

            if ($intent !== 'CHAT') {
                // Trả về CHAT thay vì lỗi, kèm gợi ý
                return [
                    'intent'  => 'CHAT',
                    'message' => "Mình không tìm thấy danh mục phù hợp. Bạn có thể tạo danh mục mới trước nhé!\nVí dụ: \"Tạo danh mục Đám cưới\"",
                ];
            }
        }

        return ['intent' => $fallbackIntent, 'message' => $raw];
    }
}
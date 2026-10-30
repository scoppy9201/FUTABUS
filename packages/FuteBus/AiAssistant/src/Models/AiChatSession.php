<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Persisted chat session storing multi-turn conversation history
 * and partial booking/ticket context for one browser session.
 *
 * @property string $id
 * @property int|null $user_id
 * @property array $history [{role, content}, …]
 * @property array $context {departure?, destination?, date?, budget?, ticket_code?, phone?}
 * @property Carbon $last_active_at
 */
class AiChatSession extends Model
{
    use HasUuids;

    protected $table = 'ai_chat_sessions';

    protected $fillable = ['user_id', 'history', 'context', 'last_active_at'];

    protected $casts = [
        'history'        => 'array',
        'context'        => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Append a turn to the conversation history and update the last-active timestamp.
     */
    public function appendTurn(string $role, string $content): void
    {
        $this->history = array_merge($this->history ?? [], [
            ['role' => $role, 'content' => $content],
        ]);
        $this->last_active_at = now();
    }

    /**
     * Return a sliding window of history for sending to the LLM
     * (avoids blowing past the context-window limit).
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function recentHistory(int $maxTurns = 10): array
    {
        $history = $this->history ?? [];

        // Each "turn" is one message; keep the most recent maxTurns messages
        return array_slice($history, -$maxTurns);
    }
}

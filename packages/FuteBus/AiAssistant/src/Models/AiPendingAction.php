<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPendingAction extends Model
{
    protected $table = 'ai_pending_actions';

    protected $fillable = ['user_id', 'action', 'payload', 'status', 'result'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result'  => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

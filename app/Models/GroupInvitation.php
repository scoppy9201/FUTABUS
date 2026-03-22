<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class GroupInvitation extends Model
{
    protected $fillable = [
        'group_id', 'invited_by', 'email', 'token',
        'trang_thai', 'expires_at', 'responded_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->trang_thai === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    // Kiểm tra token còn hợp lệ để dùng
    public function isUsable(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }
}

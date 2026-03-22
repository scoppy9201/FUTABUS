<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemNotification extends Model
{
    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id', 'loai', 'tieu_de', 'noi_dung',
        'url', 'actor_id', 'entity_type', 'entity_id',
        'da_doc', 'doc_luc',
    ];

    protected $casts = [
        'da_doc'  => 'boolean',
        'doc_luc' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeUnread($q)
    {
        return $q->where('da_doc', false);
    }

    public function scopeRecent($q)
    {
        return $q->orderByDesc('created_at');
    }

    // ── Helpers ─────────────────────────────────────────────
    public function markAsRead(): void
    {
        if (!$this->da_doc) {
            $this->update(['da_doc' => true, 'doc_luc' => now()]);
        }
    }

    // Icon + màu theo loại
    public function getIconAttribute(): string
    {
        return match($this->loai) {
            'transaction_created'           => '💰',
            'transaction_updated'           => '✏️',
            'transaction_deleted'           => '🗑️',
            'wallet_warning'                => '⚠️',
            'wallet_exceeded'               => '🚨',
            'group_invited'                 => '📩',
            'group_joined'                  => '🎉',
            'group_left'                    => '👋',
            'group_removed'                 => '❌',
            'group_promoted'                => '👑',
            'group_demoted'                 => '⬇️',
            'balance_proposed','expense_proposed' => '📋',
            'balance_approved','expense_approved' => '✅',
            'balance_rejected','expense_rejected' => '❌',
            'balance_executed','expense_executed' => '⚡',
            'debt_recorded'                 => '📝',
            'debt_settled'                  => '✅',
            default                         => '🔔',
        };
    }

    public function getColorAttribute(): string
    {
        return match(true) {
            in_array($this->loai, ['wallet_warning','wallet_exceeded','group_removed','balance_rejected','expense_rejected']) => '#ef4444',
            in_array($this->loai, ['balance_executed','expense_executed','debt_settled','group_joined','group_promoted'])     => '#10b981',
            in_array($this->loai, ['balance_proposed','expense_proposed','debt_recorded','group_invited'])                   => '#f59e0b',
            default => '#4a90e2',
        };
    }

    // Thời gian tương đối (giống FB)
    public function getTimeAgoAttribute(): string
    {
        $diff = now()->diffInSeconds($this->created_at);
        if ($diff < 60)          return 'Vừa xong';
        if ($diff < 3600)        return round($diff / 60) . ' phút trước';
        if ($diff < 86400)       return round($diff / 3600) . ' giờ trước';
        if ($diff < 604800)      return round($diff / 86400) . ' ngày trước';
        if ($diff < 2592000)     return round($diff / 604800) . ' tuần trước';
        return $this->created_at->format('d/m/Y');
    }
}

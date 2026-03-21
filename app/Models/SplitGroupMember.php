<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitGroupMember extends Model
{
    protected $fillable = [
        'group_id', 'user_id', 'vai_tro', 'trang_thai',
        'joined_at', 'left_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at'   => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAdmin(): bool
    {
        return $this->vai_tro === 'admin';
    }

    public function isActive(): bool
    {
        return $this->trang_thai === 'active';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupExpenseDebt extends Model
{
    protected $fillable = [
        'group_id', 'chu_no_id', 'nguoi_no_id',
        'so_tien', 'ghi_chu', 'trang_thai', 'settled_at',
    ];

    protected $casts = [
        'so_tien'    => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    // Người cho nợ (chủ nợ)
    public function chuNo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chu_no_id');
    }

    // Người đang nợ
    public function nguoiNo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_no_id');
    }

    public function isPending(): bool
    {
        return $this->trang_thai === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->trang_thai === 'confirmed';
    }
}

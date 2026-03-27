<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupExpenseSplit extends Model
{
    protected $fillable = [
        'proposal_id', 'user_id',
        'so_tien', 'ty_le',
        'transaction_id', 'trang_thai_dong_y', 'responded_at',
    ];

    protected $casts = [
        'so_tien'      => 'decimal:2',
        'ty_le'        => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(GroupExpenseProposal::class, 'proposal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}

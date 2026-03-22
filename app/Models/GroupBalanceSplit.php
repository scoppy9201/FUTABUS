<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupBalanceSplit extends Model
{
    protected $fillable = [
        'proposal_id', 'user_id',
        'so_du_cu', 'so_du_moi', 'chenh_lech',
        'transaction_id', 'trang_thai_dong_y', 'responded_at',
    ];

    protected $casts = [
        'so_du_cu'     => 'decimal:2',
        'so_du_moi'    => 'decimal:2',
        'chenh_lech'   => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(GroupBalanceProposal::class, 'proposal_id');
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

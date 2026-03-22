<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupExpenseProposal extends Model
{
    protected $fillable = [
        'group_id', 'proposed_by', 'category_id',
        'mo_ta', 'tong_tien', 'ngay_chi',
        'kieu_chia', 'trang_thai', 'executed_at',
    ];

    protected $casts = [
        'tong_tien'   => 'decimal:2',
        'ngay_chi'    => 'date',
        'executed_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function splits(): HasMany
    {
        return $this->hasMany(GroupExpenseSplit::class, 'proposal_id');
    }

    public function approvals()
    {
        return $this->morphMany(GroupApproval::class, 'approvable');
    }

    public function isPending(): bool
    {
        return $this->trang_thai === 'pending';
    }

    public function isFullyApproved(): bool
    {
        $memberIds = SplitGroupMember::where('group_id', $this->group_id)
            ->where('trang_thai', 'active')
            ->pluck('user_id');

        $approvedIds = $this->approvals()
            ->where('quyet_dinh', 'approved')
            ->pluck('user_id');

        return $memberIds->diff($approvedIds)->isEmpty();
    }

    public function getUserApproval(int $userId): ?GroupApproval
    {
        return $this->approvals()->where('user_id', $userId)->first();
    }
}

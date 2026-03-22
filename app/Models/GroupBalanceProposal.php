<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupBalanceProposal extends Model
{
    protected $fillable = [
        'group_id', 'proposed_by', 'mo_ta',
        'tong_so_du', 'trang_thai', 'executed_at',
    ];

    protected $casts = [
        'tong_so_du'  => 'decimal:2',
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

    public function splits(): HasMany
    {
        return $this->hasMany(GroupBalanceSplit::class, 'proposal_id');
    }

    public function approvals()
    {
        return $this->morphMany(GroupApproval::class, 'approvable');
    }

    public function isPending(): bool
    {
        return $this->trang_thai === 'pending';
    }

    // Kiểm tra tất cả thành viên đã approve chưa
    public function isFullyApproved(): bool
    {
        $memberIds = SplitGroupMember::where('group_id', $this->group_id)
            ->where('trang_thai', 'active')
            ->pluck('user_id');

        $approvedIds = $this->approvals()
            ->where('quyet_dinh', 'approved')
            ->pluck('user_id');

        // Tất cả member phải có trong approved (trừ người đề xuất — tự approve)
        return $memberIds->diff($approvedIds)->isEmpty();
    }

    // Kiểm tra có ai reject không
    public function hasRejection(): bool
    {
        return $this->approvals()->where('quyet_dinh', 'rejected')->exists();
    }

    // Lấy trạng thái approval của 1 user cụ thể
    public function getUserApproval(int $userId): ?GroupApproval
    {
        return $this->approvals()->where('user_id', $userId)->first();
    }
}

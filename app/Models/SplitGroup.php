<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitGroup extends Model
{
    protected $fillable = [
        'created_by', 'ten_nhom', 'mo_ta',
        'che_do', 'hien_so_du', 'trang_thai',
    ];

    protected $casts = [
        'hien_so_du' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SplitGroupMember::class, 'group_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(SplitGroupMember::class, 'group_id')
                    ->where('trang_thai', 'active');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class, 'group_id');
    }

    public function balanceProposals(): HasMany
    {
        return $this->hasMany(GroupBalanceProposal::class, 'group_id');
    }

    public function expenseProposals(): HasMany
    {
        return $this->hasMany(GroupExpenseProposal::class, 'group_id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(GroupExpenseDebt::class, 'group_id');
    }

    // Kiểm tra user có phải thành viên active không
    public function isMember(int $userId): bool
    {
        return $this->activeMembers()->where('user_id', $userId)->exists();
    }

    // Kiểm tra user có phải admin không
    public function isAdmin(int $userId): bool
    {
        return $this->activeMembers()
                    ->where('user_id', $userId)
                    ->where('vai_tro', 'admin')
                    ->exists();
    }
}

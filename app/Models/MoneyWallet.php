<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyWallet extends Model
{
    use HasFactory;

    protected $table = 'money_wallets';

    protected $fillable = [
        'user_id',
        'ten_vi',
        'loai_vi',
        'so_du',
        'so_du_ban_dau',
        'don_vi_tien_te',
        'bieu_tuong',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'so_du'         => 'decimal:2',
        'so_du_ban_dau' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'money_wallet_id');
    }

    public function transfersFrom()
    {
        return $this->hasMany(WalletTransfer::class, 'from_wallet_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(WalletTransfer::class, 'to_wallet_id');
    }

    public function adjustments()
    {
        return $this->hasMany(WalletAdjustment::class, 'wallet_id');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->so_du, 0, ',', '.') . ' ' . $this->don_vi_tien_te;
    }

    public function getLoaiViLabelAttribute(): string
    {
        return match($this->loai_vi) {
            'tien_mat'     => 'Tiền mặt',
            'ngan_hang'    => 'Ngân hàng',
            'vi_dien_tu'   => 'Ví điện tử',
            'the_tin_dung' => 'Thẻ tín dụng',
            'dau_tu'       => 'Đầu tư',
            default        => 'Khác',
        };
    }

    public function getLoaiViColorAttribute(): string
    {
        return match($this->loai_vi) {
            'tien_mat'     => '#10b981',
            'ngan_hang'    => '#4a90e2',
            'vi_dien_tu'   => '#f59e0b',
            'the_tin_dung' => '#ef4444',
            'dau_tu'       => '#8b5cf6',
            default        => '#6b7280',
        };
    }

    // ── Business Logic ────────────────────────────────────
    /**
     * Cập nhật số dư khi có giao dịch mới
     */
    public function updateBalance(string $loai, float $soTien): void
    {
        if ($loai === 'THU') {
            $this->increment('so_du', $soTien);
        } else {
            $this->decrement('so_du', $soTien);
        }
    }

    /**
     * Hoàn tác số dư khi xóa/sửa giao dịch
     */
    public function revertBalance(string $loai, float $soTien): void
    {
        if ($loai === 'THU') {
            $this->decrement('so_du', $soTien);
        } else {
            $this->increment('so_du', $soTien);
        }
    }

    /**
     * Kiểm tra có thể xóa không (chưa có giao dịch)
     */
    public function canDelete(): bool
    {
        return $this->transactions()->where('la_chuyen_vi', false)->count() === 0
            && $this->transfersFrom()->count() === 0
            && $this->transfersTo()->count() === 0
            && $this->adjustments()->count() === 0;
    }

    /**
     * Tính tổng thu/chi thực tế của ví (loại bỏ giao dịch chuyển ví)
     */
    public function getTotalIncome(): float
    {
        return (float) $this->transactions()
            ->where('loai_giao_dich', 'THU')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');
    }

    public function getTotalExpense(): float
    {
        return (float) $this->transactions()
            ->where('loai_giao_dich', 'CHI')
            ->where('la_chuyen_vi', false)
            ->sum('so_tien');
    }

    // ── Scopes ────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}

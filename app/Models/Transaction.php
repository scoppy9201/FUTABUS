<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'category_id',
        'so_tien',
        'loai_giao_dich',
        'phuong_thuc_thanh_toan',
        'ngay_giao_dich',
        'ghi_chu',
        'money_wallet_id',
    ];

    protected $casts = [
        'ngay_giao_dich' => 'date',
        'so_tien' => 'decimal:2',
    ];

    /**
     * Relationship: Giao dịch thuộc về một user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moneyWallet()
    {
        return $this->belongsTo(\App\Models\MoneyWallet::class, 'money_wallet_id');
    }

    /**
     * Relationship: Giao dịch thuộc về một danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Giao dịch có thể thuộc về một ngân sách (thông qua category)
     */
    public function wallet()
    {
        return $this->hasOneThrough(
            Wallet::class,
            Category::class,
            'id',
            'category_id',
            'category_id',
            'id'
        )->where('wallets.user_id', $this->user_id);
    }

    /**
     * Scope: Lọc giao dịch theo loại (THU/CHI)
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('loai_giao_dich', $type);
    }

    /**
     * Scope: Lọc giao dịch theo khoảng thời gian
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('ngay_giao_dich', [$startDate, $endDate]);
    }

    /**
     * Scope: Chỉ lấy giao dịch của user hiện tại
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Lọc theo phương thức thanh toán
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('phuong_thuc_thanh_toan', $method);
    }

    /**
     * Accessor: Format số tiền
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->so_tien, 0, ',', '.') . 'đ';
    }

    /**
     * Accessor: Lấy màu badge theo loại giao dịch
     */
    public function getTypeColorAttribute()
    {
        return $this->loai_giao_dich == 'THU' ? 'success' : 'danger';
    }
}

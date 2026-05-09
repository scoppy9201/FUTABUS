<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Budgets extends Model
{
    use HasFactory;
    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'category_id',
        'ten_ngan_sach',
        'ngan_sach_goc',
        'so_du',
        'mo_ta',
        'trang_thai',
        'loai_thoi_gian',   
        'ngay_bat_dau',     
        'ngay_ket_thuc',    
        'tu_dong_reset',   
        'da_het_han',      
    ];

    protected $casts = [
        'ngan_sach_goc' => 'decimal:2',
        'so_du'         => 'decimal:2',
        'trang_thai'    => 'boolean',
        'tu_dong_reset' => 'boolean',   
        'da_het_han'    => 'boolean',   
        'ngay_bat_dau'  => 'date',      
        'ngay_ket_thuc' => 'date',   
    ];
    
    /**
     * Relationship: Ngân sách thuộc về một user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Ngân sách thuộc về một danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Giao dịch của ngân sách này (trực tiệp wallet_id)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
    
    /**
     * Accessor: Format ngân sách gốc
     */
    public function getFormattedBudgetAttribute()
    {
        return number_format($this->ngan_sach_goc, 0, ',', '.') . 'đ';
    }

    /**
     * Accessor: Format số dư
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->so_du, 0, ',', '.') . 'đ';
    }

    /**
     * Accessor: Số tiền đã chi
     */
    public function getSpentAmountAttribute()
    {
        return $this->ngan_sach_goc - $this->so_du;
    }

    /**
     * Accessor: Format số tiền đã chi
     */
    public function getFormattedSpentAmountAttribute()
    {
        return number_format($this->spent_amount, 0, ',', '.') . 'đ';
    }

    /**
     * Accessor: Format số tiền còn lại
     */
    public function getFormattedRemainingAmountAttribute()
    {
        return number_format($this->so_du, 0, ',', '.') . 'đ';
    }

    /**
     * Accessor: Phần trăm đã chi tiêu
     */
    public function getSpentPercentageAttribute()
    {
        if ($this->ngan_sach_goc <= 0) {
            return 0;
        }
        return round((($this->ngan_sach_goc - $this->so_du) / $this->ngan_sach_goc) * 100, 2);
    }

    /**
     * Accessor: Check ngân sách có bị vượt không
     */
    public function getIsOverBudgetAttribute()
    {
        return $this->so_du < 0;
    }

    /**
     * Accessor: Check ngân sách sắp hết (dưới 20%)
     */
    public function getIsLowBalanceAttribute()
    {
        if ($this->ngan_sach_goc <= 0) {
            return false;
        }
        return $this->spent_percentage >= 80;
    }

    /**
     * Accessor: Check ngân sách gần hết (dưới 10%)
     */
    public function getIsCriticalBalanceAttribute()
    {
        if ($this->ngan_sach_goc <= 0) {
            return false;
        }
        return $this->spent_percentage >= 90;
    }

    /**
     * Accessor: Trạng thái ngân sách (text)
     */
    public function getStatusTextAttribute()
    {
        if ($this->is_over_budget) {
            return 'Vượt ngân sách';
        }
        
        if ($this->is_critical_balance) {
            return 'Nguy hiểm';
        }
        
        if ($this->is_low_balance) {
            return 'Sắp hết';
        }
        
        if ($this->spent_percentage >= 50) {
            return 'Trung bình';
        }
        
        return 'Tốt';
    }

    /**
     * Accessor: Màu sắc trạng thái (cho UI)
     */
    public function getStatusColorAttribute()
    {
        if ($this->is_over_budget || $this->is_critical_balance) {
            return 'danger';
        }
        if ($this->is_low_balance) return 'warning';
        if ($this->spent_percentage >= 50) return 'info';
        return 'success';
    }
    
    /**
     * Scope: Lọc theo danh mục
     */
    public function scopeByCategory(Builder $query, int $categoryId) : Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Chỉ lấy ngân sách đang hoạt động
     */
    public function scopeActive(Builder $query) : Builder
    {
        return $query->where('trang_thai', true);
    }

    /**
     * Scope: Tìm kiếm theo tên
     */
    public function scopeSearch(Builder $query, String $keyword) : Builder
    {
        if (empty($keyword)) {
            return $query;
        }
        
        $keyword = trim($keyword);
        $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
        
        return $query->where('ten_ngan_sach', 'like', '%' . $searchEscaped . '%');
    }

    /**
     * Scope: Lọc ngân sách vượt chi
     */
    public function scopeOverBudget(Builder $query) : Builder
    {
        return $query->where('so_du', '<', 0);
    }

    /**
     * Scope: Lọc ngân sách sắp hết
     */
    public function scopeLowBalance(Builder $query, $threshold = 20) : Builder
    {
        return $query->whereRaw('((ngan_sach_goc - so_du) / ngan_sach_goc * 100) >= ?', [100 - $threshold]);
    }
    
    /**
     * Tính lại số dư dựa trên giao dịch thực tế
     */
    public function recalculateBalance()
    {
        $totalSpent = $this->transactions()
            ->where('loai_giao_dich', 'CHI')
            ->sum('so_tien');

        $totalIncome = $this->transactions()
            ->where('loai_giao_dich', 'THU')
            ->sum('so_tien');

        $newBalance = $this->ngan_sach_goc + $totalIncome - $totalSpent;
        $this->update(['so_du' => $newBalance]);

        return $newBalance;
    }

    /**
     * Kiểm tra ngân sách có thể xóa không
     */
    public function canDelete(): bool
    {
        return !$this->transactions()->exists();
    }

    /**
     * Reset ngân sách về trạng thái ban đầu
     */
    public function reset()
    {
        $this->update(['so_du' => $this->ngan_sach_goc]);
        return $this;
    }

    /**
     * Lấy giao dịch của ngân sách này
     */
    public function getUserTransactions()
    {
        return $this->transactions()->get();
    }

    /**
     * Lấy giao dịch gần nhất
     */
    public function getRecentTransactions($limit = 5)
    {
        return $this->transactions()
                    ->orderBy('ngay_giao_dich', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Lấy thống kê chi tiết của ngân sách
     */
    public function getStatistics()
    {
        $transactions = $this->transactions()->get();
        
        return [
            'total_transactions' => $transactions->count(),
            'total_income' => $transactions->where('loai_giao_dich', 'THU')->sum('so_tien'),
            'total_expense' => $transactions->where('loai_giao_dich', 'CHI')->sum('so_tien'),
            'spent_amount' => $this->spent_amount,
            'remaining_amount' => $this->so_du,
            'spent_percentage' => $this->spent_percentage,
            'status' => $this->status_text,
            'status_color' => $this->status_color,
            'is_over_budget' => $this->is_over_budget,
            'is_low_balance' => $this->is_low_balance,
            'is_critical_balance' => $this->is_critical_balance,
        ];
    }

    /**
     * Accessor: Kiểm tra ngân sách có đang trong thời hạn không
     */
    public function getIsActiveTimeAttribute(): bool
    {
        $today = now()->startOfDay();

        if ($this->loai_thoi_gian === 'thang') {
            // Loại tháng → kiểm tra ngày hiện tại có trong tháng của ngay_bat_dau không
            if (!$this->ngay_bat_dau) return true;
            return $today->lte($this->ngay_ket_thuc);
        }

        // Loại ngày → kiểm tra khoảng thời gian
        if (!$this->ngay_bat_dau || !$this->ngay_ket_thuc) return true;
        return $today->gte($this->ngay_bat_dau) && $today->lte($this->ngay_ket_thuc);
    }

    /**
     * Accessor: Số ngày còn lại
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->ngay_ket_thuc) return null;
        $today = now()->startOfDay();
        if ($today->gt($this->ngay_ket_thuc)) return 0;
        return $today->diffInDays($this->ngay_ket_thuc);
    }

    /**
     * Accessor: Phần trăm thời gian đã trôi qua
     */
    public function getTimeProgressAttribute(): ?float
    {
        if (!$this->ngay_bat_dau || !$this->ngay_ket_thuc) return null;
        $total   = $this->ngay_bat_dau->diffInDays($this->ngay_ket_thuc);
        $elapsed = $this->ngay_bat_dau->diffInDays(now()->startOfDay());
        if ($total <= 0) return 100;
        return round(min(($elapsed / $total) * 100, 100), 2);
    }

    /**
     * Accessor: Text thời hạn hiển thị UI
     */
    public function getTimeRangeTextAttribute(): string
    {
        if (!$this->ngay_bat_dau || !$this->ngay_ket_thuc) return 'Không giới hạn';

        if ($this->loai_thoi_gian === 'thang') {
            return 'Tháng ' . $this->ngay_bat_dau->format('m/Y');
        }

        return $this->ngay_bat_dau->format('d/m/Y') . ' - ' . $this->ngay_ket_thuc->format('d/m/Y');
    }

    /**
     * Kiểm tra và khóa ngân sách nếu hết hạn
     */
    public function checkAndExpire(): bool
    {
        if ($this->da_het_han) return true; // đã hết hạn rồi

        if (!$this->is_active_time) {
            $this->update([
                'trang_thai' => false,
                'da_het_han' => true,
            ]);
            return true;
        }

        return false;
    }
}

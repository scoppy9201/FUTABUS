<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Budgets;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ten_danh_muc',
        'loai_danh_muc',
        'danh_muc_cha_id',
        'bieu_tuong',
        'mo_ta',
        'trang_thai'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    /**
     * Relationship: Category thuộc về một user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Danh mục cha
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'danh_muc_cha_id');
    }

    /**
     * Relationship: Danh mục con
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'danh_muc_cha_id');
    }

    /**
     * Relationship: Giao dịch thuộc danh mục này
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    /**
     * Relationship: Ngân sách cho danh mục này
     */
    public function wallets()
    {
        return $this->hasMany(Budgets::class, 'category_id');
    }

    /**
     * Kiểm tra danh mục có thể xóa không
     */
    public function canDelete()
    {
        return $this->transactions()->count() === 0 && $this->wallets()->count() === 0;
    }

    /**
     * Scope: Filter theo loại (THU/CHI)
     */
    public function scopeLoai($query, $loai)
    {
        if ($loai && in_array($loai, ['THU', 'CHI'])) {
            return $query->where('loai_danh_muc', $loai);
        }
        return $query;
    }

    /**
     * Scope: Tìm kiếm theo tên
     */
    public function scopeSearch($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }
        
        $keyword = trim($keyword);
        
        return $query->where(function ($q) use ($keyword) {
            // Tìm theo tên
            $q->where('ten_danh_muc', 'like', "%{$keyword}%");
            
            // Nếu là số, tìm thêm theo ID
            if (is_numeric($keyword) && $keyword > 0) {
                $q->orWhere('id', '=', intval($keyword));
            }
        });
    }

    /**
     * Scope: Lọc theo trạng thái
     */
    public function scopeTrangThai($query, $status)
    {
        if ($status !== null && $status !== '') {
            return $query->where('trang_thai', $status);
        }
        return $query;
    }

    /**
     * Accessor: Tổng ngân sách đã đặt cho danh mục này
     */
    public function getTotalBudgetAttribute()
    {
        return $this->wallets()->sum('ngan_sach_goc');
    }

    /**
     * Accessor: Tổng số dư còn lại của danh mục này
     */
    public function getTotalBalanceAttribute()
    {
        return $this->wallets()->sum('so_du');
    }
}
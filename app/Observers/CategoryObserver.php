<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\NotificationService;

class CategoryObserver
{
    public function created(Category $category): void
    {
        if (!$category->user_id) return;

        $loai = $category->loai_danh_muc === 'THU' ? 'thu nhập' : 'chi tiêu';

        NotificationService::send(
            userId:     $category->user_id,
            loai:       'system',
            tieuDe:     'Danh mục mới',
            noiDung:    "Đã tạo danh mục \"{$category->ten_danh_muc}\" ({$loai})",
            url:        route('categories.index'),
            actorId:    $category->user_id,
            entityType: Category::class,
            entityId:   $category->id,
        );
    }

    public function updated(Category $category): void
    {
        if (!$category->user_id) return;
        if (!$category->wasChanged(['ten_danh_muc', 'loai_danh_muc', 'trang_thai'])) return;

        $msg = $category->wasChanged('trang_thai')
            ? ($category->trang_thai
                ? "Đã kích hoạt danh mục \"{$category->ten_danh_muc}\""
                : "Đã vô hiệu hóa danh mục \"{$category->ten_danh_muc}\"")
            : "Đã cập nhật danh mục \"{$category->ten_danh_muc}\"";

        NotificationService::send(
            userId:     $category->user_id,
            loai:       'system',
            tieuDe:     'Danh mục đã cập nhật',
            noiDung:    $msg,
            url:        route('categories.index'),
            actorId:    $category->user_id,
            entityType: Category::class,
            entityId:   $category->id,
        );
    }

    public function deleted(Category $category): void
    {
        if (!$category->user_id) return;

        NotificationService::send(
            userId:     $category->user_id,
            loai:       'system',
            tieuDe:     'Danh mục đã xóa',
            noiDung:    "Đã xóa danh mục \"{$category->ten_danh_muc}\"",
            url:        route('categories.index'),
            actorId:    $category->user_id,
            entityType: null,
            entityId:   null,
        );
    }
}

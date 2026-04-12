<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\NotificationService;

class TransactionObserver
{
    public function created(Transaction $tx): void
    {
        $type  = $tx->loai_giao_dich === 'THU' ? 'thu nhập' : 'chi tiêu';
        $emoji = $tx->loai_giao_dich === 'THU' ? '↑' : '↓';

        NotificationService::send(
            userId:     $tx->user_id,
            loai:       'transaction_created',
            tieuDe:     'Giao dịch mới',
            noiDung:    "Đã ghi nhận {$type} " . number_format($tx->so_tien) . 'đ',
            url:        route('transactions.index'), 
            actorId:    $tx->user_id,
            entityType: Transaction::class,
            entityId:   $tx->id,
        );
    }

    public function updated(Transaction $tx): void
    {
        if (!$tx->wasChanged(['so_tien', 'loai_giao_dich', 'category_id', 'ngay_giao_dich'])) {
            return;
        }

        NotificationService::send(
            userId:     $tx->user_id,
            loai:       'transaction_updated',
            tieuDe:     'Giao dịch đã cập nhật',
            noiDung:    'Giao dịch ' . number_format($tx->so_tien) . 'đ vừa được chỉnh sửa',
            url:        route('transactions.index'), 
            actorId:    $tx->user_id,
            entityType: Transaction::class,
            entityId:   $tx->id,
        );
    }

    public function deleted(Transaction $tx): void
    {
        NotificationService::send(
            userId:     $tx->user_id,
            loai:       'transaction_deleted',
            tieuDe:     'Giao dịch đã xóa',
            noiDung:    'Đã xóa giao dịch ' . number_format($tx->so_tien) . 'đ',
            url:        route('transactions.index'),
            actorId:    $tx->user_id,
            entityType: null,
            entityId:   null,
        );
    }
}

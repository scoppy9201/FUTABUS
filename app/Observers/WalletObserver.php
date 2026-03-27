<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Services\NotificationService;

class WalletObserver
{
    /**
     * Gọi sau mỗi lần so_du thay đổi (từ TransactionController)
     * Kiểm tra ngưỡng cảnh báo
     */
    public function updated(Wallet $wallet): void
    {
        if (!$wallet->wasChanged('so_du') || $wallet->trang_thai !== true) {
            return;
        }

        $phanTram = $wallet->ngan_sach_goc > 0
            ? ($wallet->so_du / $wallet->ngan_sach_goc) * 100
            : 100;

        // Vượt ngân sách
        if ($wallet->so_du < 0) {
            NotificationService::send(
                userId:     $wallet->user_id,
                loai:       'wallet_exceeded',
                tieuDe:     '🚨 Vượt ngân sách',
                noiDung:    "Ngân sách \"{$wallet->ten_ngan_sach}\" đã vượt mức " . number_format(abs($wallet->so_du)) . 'đ',
                url:        route('wallets.show', $wallet),
                actorId:    $wallet->user_id,
                entityType: Wallet::class,
                entityId:   $wallet->id,
            );
            return;
        }

        // Sắp hết (còn dưới 20%)
        if ($phanTram <= 20 && $phanTram > 0) {
            NotificationService::send(
                userId:     $wallet->user_id,
                loai:       'wallet_warning',
                tieuDe:     '⚠️ Gần đạt giới hạn ngân sách',
                noiDung:    "Ngân sách \"{$wallet->ten_ngan_sach}\" còn " . number_format($wallet->so_du) . 'đ (' . round($phanTram) . '%)',
                url:        route('wallets.show', $wallet),
                actorId:    $wallet->user_id,
                entityType: Wallet::class,
                entityId:   $wallet->id,
            );
        }
    }
}

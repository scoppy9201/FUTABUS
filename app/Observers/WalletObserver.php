<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Services\NotificationService;

class WalletObserver
{
    public function created(Wallet $wallet): void
    {
        NotificationService::send(
            userId:     $wallet->user_id,
            loai:       'system',
            tieuDe:     'Ngân sách mới',
            noiDung:    "Đã tạo ngân sách \"{$wallet->ten_ngan_sach}\" với hạn mức " . number_format($wallet->ngan_sach_goc) . 'đ',
            url:        route('wallets.index'),
            actorId:    $wallet->user_id,
            entityType: Wallet::class,
            entityId:   $wallet->id,
        );
    }

    public function updated(Wallet $wallet): void
    {
        // Cảnh báo khi số dư thay đổi
        if ($wallet->wasChanged('so_du') && $wallet->trang_thai === true) {
            $phanTram = $wallet->ngan_sach_goc > 0
                ? ($wallet->so_du / $wallet->ngan_sach_goc) * 100
                : 100;

            if ($wallet->so_du < 0) {
                NotificationService::send(
                    userId:     $wallet->user_id,
                    loai:       'wallet_exceeded',
                    tieuDe:     '🚨 Vượt ngân sách',
                    noiDung:    "Ngân sách \"{$wallet->ten_ngan_sach}\" đã vượt mức " . number_format(abs($wallet->so_du)) . 'đ',
                    url:        route('wallets.index'),
                    actorId:    $wallet->user_id,
                    entityType: Wallet::class,
                    entityId:   $wallet->id,
                );
                return;
            }

            if ($phanTram <= 20 && $phanTram > 0) {
                NotificationService::send(
                    userId:     $wallet->user_id,
                    loai:       'wallet_warning',
                    tieuDe:     '⚠️ Gần đạt giới hạn ngân sách',
                    noiDung:    "Ngân sách \"{$wallet->ten_ngan_sach}\" còn " . number_format($wallet->so_du) . 'đ (' . round($phanTram) . '%)',
                    url:        route('wallets.index'),
                    actorId:    $wallet->user_id,
                    entityType: Wallet::class,
                    entityId:   $wallet->id,
                );
                return;
            }
        }

        // Thông báo khi cập nhật hạn mức
        if ($wallet->wasChanged('ngan_sach_goc')) {
            NotificationService::send(
                userId:     $wallet->user_id,
                loai:       'system',
                tieuDe:     'Ngân sách đã cập nhật',
                noiDung:    "Hạn mức \"{$wallet->ten_ngan_sach}\" đã đổi thành " . number_format($wallet->ngan_sach_goc) . 'đ',
                url:        route('wallets.index'),
                actorId:    $wallet->user_id,
                entityType: Wallet::class,
                entityId:   $wallet->id,
            );
        }

        // Thông báo khi toggle trạng thái
        if ($wallet->wasChanged('trang_thai')) {
            $msg = $wallet->trang_thai
                ? "Đã kích hoạt ngân sách \"{$wallet->ten_ngan_sach}\""
                : "Đã vô hiệu hóa ngân sách \"{$wallet->ten_ngan_sach}\"";

            NotificationService::send(
                userId:     $wallet->user_id,
                loai:       'system',
                tieuDe:     'Trạng thái ngân sách thay đổi',
                noiDung:    $msg,
                url:        route('wallets.index'),
                actorId:    $wallet->user_id,
                entityType: Wallet::class,
                entityId:   $wallet->id,
            );
        }
    }

    public function deleted(Wallet $wallet): void
    {
        NotificationService::send(
            userId:     $wallet->user_id,
            loai:       'system',
            tieuDe:     'Ngân sách đã xóa',
            noiDung:    "Đã xóa ngân sách \"{$wallet->ten_ngan_sach}\"",
            url:        route('wallets.index'),
            actorId:    $wallet->user_id,
            entityType: null,
            entityId:   null,
        );
    }
}

<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Tạo thông báo cho 1 user
     */
    public static function send(
        int    $userId,
        string $loai,
        string $tieuDe,
        string $noiDung,
        string $url       = null,
        int    $actorId   = null,
        string $entityType = null,
        int    $entityId   = null,
    ): SystemNotification {
        return SystemNotification::create([
            'user_id'     => $userId,
            'loai'        => $loai,
            'tieu_de'     => $tieuDe,
            'noi_dung'    => $noiDung,
            'url'         => $url,
            'actor_id'    => $actorId ?? Auth::id(),
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ]);
    }

    /**
     * Gửi cho nhiều users cùng lúc
     */
    public static function sendMany(
        array  $userIds,
        string $loai,
        string $tieuDe,
        string $noiDung,
        string $url       = null,
        int    $actorId   = null,
        string $entityType = null,
        int    $entityId   = null,
    ): void {
        $actorId = $actorId ?? Auth::id();
        $now     = now();

        $rows = array_map(fn($uid) => [
            'user_id'     => $uid,
            'loai'        => $loai,
            'tieu_de'     => $tieuDe,
            'noi_dung'    => $noiDung,
            'url'         => $url,
            'actor_id'    => $actorId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'da_doc'      => false,
            'created_at'  => $now,
            'updated_at'  => $now,
        ], array_filter($userIds, fn($id) => $id !== $actorId));
        // Không gửi cho chính người thực hiện

        if (!empty($rows)) {
            SystemNotification::insert($rows);
        }
    }

    /**
     * Lấy số thông báo chưa đọc (dùng cho badge)
     */
    public static function unreadCount(int $userId): int
    {
        return SystemNotification::where('user_id', $userId)
            ->where('da_doc', false)
            ->count();
    }

    /**
     * Đánh dấu tất cả đã đọc
     */
    public static function markAllRead(int $userId): void
    {
        SystemNotification::where('user_id', $userId)
            ->where('da_doc', false)
            ->update(['da_doc' => true, 'doc_luc' => now()]);
    }
}

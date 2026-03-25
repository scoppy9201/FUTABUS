<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    // Trang đầy đủ (khi click "Xem tất cả")
    public function index(Request $request)
    {
        $userId = Auth::id();

        $notifications = SystemNotification::where('user_id', $userId)
            ->with('actor')
            ->recent()
            ->paginate(50);

        NotificationService::markAllRead($userId);

        return view('notifications.index', compact('notifications'));
    }

    // AJAX: load dropdown (30 ngày gần nhất hoặc 100 cái)
    public function dropdown()
    {
        $userId = Auth::id();

        $notifications = SystemNotification::where('user_id', $userId)
            ->with('actor')
            ->where('created_at', '>=', now()->subDays(30))
            ->recent()
            ->limit(100)
            ->get();

        $unreadCount = $notifications->where('da_doc', false)->count();

        return response()->json([
            'notifications' => $notifications->map(fn($n) => [
                'id'        => $n->id,
                'icon'      => $n->icon,
                'color'     => $n->color,
                'tieu_de'   => $n->tieu_de,
                'noi_dung'  => $n->noi_dung,
                'url'       => $n->url,
                'da_doc'    => $n->da_doc,
                'time_ago'  => $n->time_ago,
                'date'      => $n->created_at->format('Y-m-d'),
                'actor_avatar' => $n->actor?->avatar,
                'actor_name'   => $n->actor?->name,
            ]),
            'unread_count' => $unreadCount,
            'has_older'    => SystemNotification::where('user_id', $userId)
                ->where('created_at', '<', now()->subDays(30))
                ->exists(),
        ]);
    }

    // AJAX: load thông báo theo ngày (calendar)
    public function byDate(Request $request)
    {
        $date   = $request->get('date'); // Y-m-d
        $userId = Auth::id();

        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ngày không hợp lệ'], 422);
        }

        $notifications = SystemNotification::where('user_id', $userId)
            ->with('actor')
            ->whereDate('created_at', $carbon)
            ->recent()
            ->get()
            ->map(fn($n) => [
                'id'        => $n->id,
                'icon'      => $n->icon,
                'color'     => $n->color,
                'tieu_de'   => $n->tieu_de,
                'noi_dung'  => $n->noi_dung,
                'url'       => $n->url,
                'da_doc'    => $n->da_doc,
                'time_ago'  => $n->created_at->format('H:i'),
                'actor_avatar' => $n->actor?->avatar,
                'actor_name'   => $n->actor?->name,
            ]);

        return response()->json(['notifications' => $notifications, 'date' => $carbon->format('d/m/Y')]);
    }

    // AJAX: đánh dấu 1 thông báo đã đọc
    public function markRead(SystemNotification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->markAsRead();
        return response()->json(['ok' => true]);
    }

    // AJAX: đánh dấu tất cả đã đọc
    public function markAllRead()
    {
        NotificationService::markAllRead(Auth::id());
        return response()->json(['ok' => true]);
    }

    // AJAX: lấy số badge (polling)
    public function badge()
    {
        $count = NotificationService::unreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }
}

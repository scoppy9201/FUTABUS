<?php

declare(strict_types=1);

namespace FuteBus\Notification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationInboxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(10)->get()->map(fn ($notification): array => [
            'id'      => $notification->id,
            'title'   => is_string($notification->data['title'] ?? null) ? $notification->data['title'] : '',
            'summary' => is_string($notification->data['summary'] ?? null) ? $notification->data['summary'] : null,
            'message' => is_string($notification->data['message'] ?? null) ? $notification->data['message'] : '',
            'url'     => is_string($notification->data['url'] ?? null)
                && str_starts_with($notification->data['url'], '/')
                && ! str_starts_with($notification->data['url'], '//')
                ? $notification->data['url'] : null,
            'created_at' => $notification->created_at?->format('d/m/Y H:i'),
            'read_at'    => $notification->read_at?->toIso8601String(),
        ])->values();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $user = $request->user();
        $record = $user->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        return response()->json(['unread_count' => $user->unreadNotifications()->count()]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(AppNotification $notification, Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['status' => 'marked_read']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            AppNotification::where('user_id', $user->id)->update(['is_read' => true]);
        }

        return response()->json(['status' => 'all_read']);
    }
}

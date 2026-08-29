<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Not one of the documented 16 endpoints — the doc describes push
 * notifications (FR-006, FR-017) but never an API to list them. Needed so
 * the client can actually show what TurnApproachingNotification and
 * FavoritePointChangedNotification (both delivered on the `database`
 * channel) have queued up for the logged-in user.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // The bell popover wants a short recent list (default, no ?page);
        // the full /notifications page paginates through everything by
        // passing ?page=2, 3, ...
        $perPage = $request->has('page') ? 20 : 30;
        $notifications = $user->notifications()->latest('created_at')->paginate($perPage);

        return response()->json([
            'data' => collect($notifications->items())->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? $n->type,
                'data' => $n->data,
                'readAt' => $n->read_at?->toIso8601String(),
                'createdAt' => $n->created_at->toIso8601String(),
            ]),
            'unreadCount' => $user->unreadNotifications()->count(),
            'meta' => [
                'page' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $notificationId)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['data' => ['id' => $notification->id, 'readAt' => now()->toIso8601String()]]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked read.']);
    }
}

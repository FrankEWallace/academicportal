<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $query = Notification::forUser($userId);

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by read status
        if ($request->has('unread_only') && $request->unread_only === 'true') {
            $query->unread();
        }

        // Filter by days
        if ($request->has('days')) {
            $query->lastDays($request->days);
        }

        $notifications = $query->recent()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $userId = Auth::id();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($notification->isUnread()) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification->fresh()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();
        
        $count = Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notification(s) as read",
            'data' => ['count' => $count]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();
        
        $count = Notification::forUser($userId)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count]
        ]);
    }
}

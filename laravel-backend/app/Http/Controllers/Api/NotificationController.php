<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationPreference;
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

    /**
     * Get user notification preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        
        $preferences = $user->notificationPreferences;
        
        if (!$preferences) {
            // Create default preferences
            $preferences = NotificationPreference::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'email_grades' => 'boolean',
            'email_payments' => 'boolean',
            'email_announcements' => 'boolean',
            'email_attendance' => 'boolean',
            'email_timetable' => 'boolean',
            'sms_grades' => 'boolean',
            'sms_payments' => 'boolean',
            'sms_urgent' => 'boolean',
            'app_all' => 'boolean',
        ]);

        $preferences = $user->notificationPreferences()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'data' => $preferences
        ]);
    }
}

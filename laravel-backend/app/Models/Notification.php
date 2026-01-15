<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user this notification belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a notification
     */
    public static function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Recent notifications first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Get notifications from last N days
     */
    public function scopeLastDays($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'registration_approved' => 'check-circle',
            'registration_blocked' => 'x-circle',
            'result_published' => 'award',
            'enrollment_confirmed' => 'check',
            'accommodation_allocated' => 'home',
            'feedback_responded' => 'message-circle',
            'insurance_verified' => 'shield-check',
            'insurance_rejected' => 'shield-off',
            default => 'bell',
        };
    }

    /**
     * Get type color class
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'registration_approved', 'enrollment_confirmed', 'insurance_verified' => 'success',
            'registration_blocked', 'insurance_rejected' => 'danger',
            'result_published', 'accommodation_allocated' => 'info',
            'feedback_responded' => 'warning',
            default => 'secondary',
        };
    }
}

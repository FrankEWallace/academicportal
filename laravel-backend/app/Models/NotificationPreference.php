<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'email_grades',
        'email_payments',
        'email_announcements',
        'email_attendance',
        'email_timetable',
        'sms_grades',
        'sms_payments',
        'sms_urgent',
        'app_all',
    ];

    protected $casts = [
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
    ];

    /**
     * Get the user that owns the notification preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user wants email notifications for a specific type
     */
    public function wantsEmailFor(string $type): bool
    {
        if (!$this->email_enabled) {
            return false;
        }

        return match($type) {
            'grades' => $this->email_grades,
            'payments' => $this->email_payments,
            'announcements' => $this->email_announcements,
            'attendance' => $this->email_attendance,
            'timetable' => $this->email_timetable,
            default => true,
        };
    }

    /**
     * Check if user wants SMS notifications for a specific type
     */
    public function wantsSmsFor(string $type): bool
    {
        if (!$this->sms_enabled) {
            return false;
        }

        return match($type) {
            'grades' => $this->sms_grades,
            'payments' => $this->sms_payments,
            'urgent' => $this->sms_urgent,
            default => false,
        };
    }
}

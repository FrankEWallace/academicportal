<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentAuditLog extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'enrollment_id',
        'action',
        'performed_by',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the enrollment this log belongs to
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the user who performed the action
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Create a log entry
     */
    public static function log(
        int $enrollmentId,
        string $action,
        int $performedBy,
        ?string $reason = null
    ): self {
        return self::create([
            'enrollment_id' => $enrollmentId,
            'action' => $action,
            'performed_by' => $performedBy,
            'reason' => $reason,
        ]);
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => 'Enrollment Created',
            'approved' => 'Enrollment Approved',
            'rejected' => 'Enrollment Rejected',
            'removed' => 'Enrollment Removed',
            'confirmed' => 'Enrollment Confirmed',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Scope: Filter by enrollment
     */
    public function scopeForEnrollment($query, int $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('performed_by', $userId);
    }

    /**
     * Scope: Recent logs first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

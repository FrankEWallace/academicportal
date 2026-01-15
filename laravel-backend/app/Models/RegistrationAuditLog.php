<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationAuditLog extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'registration_id',
        'action',
        'performed_by',
        'old_status',
        'new_status',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the registration this log belongs to
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
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
        int $registrationId,
        string $action,
        int $performedBy,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $reason = null
    ): self {
        return self::create([
            'registration_id' => $registrationId,
            'action' => $action,
            'performed_by' => $performedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => 'Registration Created',
            'fees_verified' => 'Fees Verified',
            'insurance_verified' => 'Insurance Verified',
            'blocked' => 'Registration Blocked',
            'unblocked' => 'Registration Unblocked',
            'overridden' => 'Registration Overridden',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Scope: Filter by registration
     */
    public function scopeForRegistration($query, int $registrationId)
    {
        return $query->where('registration_id', $registrationId);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'status',
        'total_fees',
        'amount_paid',
        'balance',
        'fees_verified',
        'insurance_verified',
        'registration_date',
        'verification_date',
        'verified_by',
        'notes',
        // New fields for admin control
        'fees_verified_by',
        'fees_verified_at',
        'registration_blocked',
        'blocked_by',
        'blocked_at',
        'block_reason',
        'override_by',
        'override_at',
        'override_reason',
    ];

    protected $casts = [
        'total_fees' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'fees_verified' => 'boolean',
        'insurance_verified' => 'boolean',
        'registration_date' => 'date',
        'verification_date' => 'date',
        'registration_blocked' => 'boolean',
        'fees_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'override_at' => 'datetime',
    ];

    /**
     * Get the student that owns the registration.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified this registration.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the admin who verified fees
     */
    public function feesVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fees_verified_by');
    }

    /**
     * Get the admin who blocked this registration
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get the admin who overrode this registration
     */
    public function overrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }

    /**
     * Get all audit logs for this registration
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(RegistrationAuditLog::class);
    }

    /**
     * Scope to filter by semester.
     */
    public function scopeBySemester($query, string $semesterCode)
    {
        return $query->where('semester_code', $semesterCode);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get blocked registrations
     */
    public function scopeBlocked($query)
    {
        return $query->where('registration_blocked', true);
    }

    /**
     * Scope to get registrations pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->whereNull('fees_verified_at')
                     ->orWhereNull('insurance_verified');
    }

    /**
     * Check if registration is fully verified.
     */
    public function isFullyVerified(): bool
    {
        return $this->fees_verified && $this->insurance_verified;
    }

    /**
     * Calculate payment percentage.
     */
    public function paymentPercentage(): float
    {
        if ($this->total_fees == 0) {
            return 0;
        }
        return ($this->amount_paid / $this->total_fees) * 100;
    }

    /**
     * Block the registration
     */
    public function block(string $reason, int $userId): bool
    {
        $oldStatus = $this->registration_blocked ? 'blocked' : 'active';
        
        $this->update([
            'registration_blocked' => true,
            'blocked_by' => $userId,
            'blocked_at' => now(),
            'block_reason' => $reason,
        ]);

        $this->createAuditLog('blocked', $userId, $oldStatus, 'blocked', $reason);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'registration_blocked',
            'Registration Blocked',
            "Your registration for {$this->semester_code} has been blocked. Reason: {$reason}",
            route('student.registration.index')
        );

        return true;
    }

    /**
     * Unblock the registration
     */
    public function unblock(int $userId): bool
    {
        $this->update([
            'registration_blocked' => false,
            'blocked_by' => null,
            'blocked_at' => null,
            'block_reason' => null,
        ]);

        $this->createAuditLog('unblocked', $userId, 'blocked', 'active', 'Registration unblocked');

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'registration_approved',
            'Registration Unblocked',
            "Your registration for {$this->semester_code} has been unblocked.",
            route('student.registration.index')
        );

        return true;
    }

    /**
     * Verify fees payment
     */
    public function verifyFees(int $userId): bool
    {
        $this->update([
            'fees_verified_by' => $userId,
            'fees_verified_at' => now(),
        ]);

        $this->createAuditLog('fees_verified', $userId, null, null, 'Fees payment verified');

        return true;
    }

    /**
     * Override registration block
     */
    public function override(string $reason, int $userId): bool
    {
        $this->update([
            'override_by' => $userId,
            'override_at' => now(),
            'override_reason' => $reason,
            'registration_blocked' => false,
        ]);

        $this->createAuditLog('overridden', $userId, 'blocked', 'active', $reason);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'registration_approved',
            'Registration Override',
            "Your registration for {$this->semester_code} has been approved by override.",
            route('student.registration.index')
        );

        return true;
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(
        string $action,
        int $performedBy,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $reason = null
    ): void {
        RegistrationAuditLog::log(
            $this->id,
            $action,
            $performedBy,
            $oldStatus,
            $newStatus,
            $reason
        );
    }
}

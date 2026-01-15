<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_date',
        'status',
        'grade',
        'credits_earned',
        'requires_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'credits_earned' => 'decimal:1',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the student that owns the enrollment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course that owns the enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the admin who approved this enrollment
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all audit logs for this enrollment
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(EnrollmentAuditLog::class);
    }

    /**
     * Require approval for this enrollment
     */
    public function requireApproval(): bool
    {
        $this->update([
            'requires_approval' => true,
            'status' => 'pending_approval',
        ]);

        $this->createAuditLog('created', null, 'Enrollment requires approval');

        return true;
    }

    /**
     * Approve the enrollment
     */
    public function approve(int $userId): bool
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->createAuditLog('approved', $userId, 'Enrollment approved');

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'enrollment_confirmed',
            'Enrollment Approved',
            "Your enrollment in {$this->course->course_code} has been approved.",
            route('student.enrollments.index')
        );

        return true;
    }

    /**
     * Reject the enrollment
     */
    public function reject(string $reason, int $userId): bool
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->createAuditLog('rejected', $userId, $reason);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'enrollment_confirmed',
            'Enrollment Rejected',
            "Your enrollment in {$this->course->course_code} has been rejected. Reason: {$reason}",
            route('student.enrollments.index')
        );

        return true;
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(string $action, ?int $performedBy, ?string $reason = null): void
    {
        EnrollmentAuditLog::log($this->id, $action, $performedBy, $reason);
    }

    /**
     * Scope to get pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                     ->where('status', 'pending_approval');
    }

    /**
     * Scope to get approved enrollments
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'active')
                     ->whereNotNull('approved_at');
    }
}

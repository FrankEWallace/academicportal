<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInsurance extends Model
{
    protected $table = 'student_insurance';

    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'provider',
        'policy_number',
        'document_path',
        'expiry_date',
        'status',
        'submission_date',
        'verification_date',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'resubmission_requested_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'submission_date' => 'date',
        'verification_date' => 'date',
        'verified_at' => 'datetime',
        'resubmission_requested_at' => 'datetime',
    ];

    /**
     * Get the student that owns the insurance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified this insurance.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending verifications
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get resubmission requests
     */
    public function scopeResubmissionRequested($query)
    {
        return $query->whereNotNull('resubmission_requested_at');
    }

    /**
     * Check if insurance is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if insurance is verified and valid.
     */
    public function isValid(): bool
    {
        return $this->status === 'verified' && !$this->isExpired();
    }

    /**
     * Verify the insurance
     */
    public function verify(int $userId): bool
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_date' => now(),
            'rejection_reason' => null,
        ]);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'insurance_verified',
            'Insurance Verified',
            "Your insurance policy ({$this->policy_number}) has been verified.",
            route('student.insurance.index')
        );

        return true;
    }

    /**
     * Reject the insurance
     */
    public function reject(string $reason, int $userId): bool
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $userId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'insurance_rejected',
            'Insurance Rejected',
            "Your insurance submission has been rejected. Reason: {$reason}",
            route('student.insurance.index')
        );

        return true;
    }

    /**
     * Request resubmission
     */
    public function requestResubmission(string $reason, int $userId): bool
    {
        $this->update([
            'status' => 'pending',
            'resubmission_requested_at' => now(),
            'rejection_reason' => $reason,
            'verified_by' => $userId,
        ]);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'insurance_rejected',
            'Insurance Resubmission Required',
            "Please resubmit your insurance document. Reason: {$reason}",
            route('student.insurance.index')
        );

        return true;
    }
}

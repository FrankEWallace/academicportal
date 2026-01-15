<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContinuousAssessment extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'semester_code',
        'assessment_type',
        'assessment_number',
        'score',
        'max_score',
        'weight',
        'weighted_score',
        'assessment_date',
        'remarks',
        'locked_at',
        'locked_by',
        'submitted_for_approval_at',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'assessment_date' => 'date',
        'locked_at' => 'datetime',
        'submitted_for_approval_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the student that owns the assessment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lecturer who locked this assessment
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the admin who approved this assessment
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate and set the weighted score.
     */
    public function calculateWeightedScore(): void
    {
        if ($this->max_score > 0) {
            $this->weighted_score = ($this->score / $this->max_score) * $this->weight;
            $this->save();
        }
    }

    /**
     * Get percentage score.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->max_score == 0) {
            return 0;
        }
        return ($this->score / $this->max_score) * 100;
    }

    /**
     * Lock the assessment scores
     */
    public function lock(int $userId): bool
    {
        $this->update([
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);

        return true;
    }

    /**
     * Submit assessment for approval
     */
    public function submitForApproval(int $userId): bool
    {
        if (!$this->locked_at) {
            $this->lock($userId);
        }

        $this->update([
            'submitted_for_approval_at' => now(),
            'approval_status' => 'pending',
        ]);

        return true;
    }

    /**
     * Approve the assessment
     */
    public function approve(int $userId): bool
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return true;
    }

    /**
     * Reject the assessment
     */
    public function reject(string $reason, int $userId): bool
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
            'locked_at' => null, // Unlock for corrections
            'locked_by' => null,
        ]);

        return true;
    }

    /**
     * Scope to get locked assessments
     */
    public function scopeLocked($query)
    {
        return $query->whereNotNull('locked_at');
    }

    /**
     * Scope to get pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get approved assessments
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }
}

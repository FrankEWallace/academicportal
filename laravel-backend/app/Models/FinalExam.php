<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalExam extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'semester_code',
        'score',
        'max_score',
        'exam_date',
        'exam_venue',
        'status',
        'remarks',
        'locked_at',
        'locked_by',
        'submitted_for_moderation_at',
        'moderation_status',
        'moderated_by',
        'moderated_at',
        'published_at',
        'moderation_notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'exam_date' => 'date',
        'locked_at' => 'datetime',
        'submitted_for_moderation_at' => 'datetime',
        'moderated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Get the student that owns the exam.
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
     * Get the lecturer who locked this exam
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the moderator who moderated this exam
     */
    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
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
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Lock the exam scores
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
     * Submit exam for moderation
     */
    public function submitForModeration(int $userId): bool
    {
        if (!$this->locked_at) {
            $this->lock($userId);
        }

        $this->update([
            'submitted_for_moderation_at' => now(),
            'moderation_status' => 'pending',
        ]);

        return true;
    }

    /**
     * Moderate the exam (approve or request changes)
     */
    public function moderate(string $status, int $userId, ?string $notes = null): bool
    {
        $this->update([
            'moderation_status' => $status, // approved or needs_changes
            'moderated_by' => $userId,
            'moderated_at' => now(),
            'moderation_notes' => $notes,
        ]);

        if ($status === 'needs_changes') {
            // Unlock for corrections
            $this->update([
                'locked_at' => null,
                'locked_by' => null,
            ]);
        }

        return true;
    }

    /**
     * Publish the exam results
     */
    public function publish(int $userId): bool
    {
        $this->update([
            'published_at' => now(),
            'status' => 'published',
        ]);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'result_published',
            'Exam Result Published',
            "Your exam result for {$this->course->course_code} has been published.",
            route('student.results.index')
        );

        return true;
    }

    /**
     * Scope to get locked exams
     */
    public function scopeLocked($query)
    {
        return $query->whereNotNull('locked_at');
    }

    /**
     * Scope to get pending moderation
     */
    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    /**
     * Scope to get moderated exams
     */
    public function scopeModerated($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Scope to get published exams
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}

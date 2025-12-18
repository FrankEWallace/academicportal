<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'file_paths',
        'file_metadata',
        'submitted_at',
        'status',
        'grade',
        'teacher_feedback',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'file_paths' => 'array',
        'file_metadata' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'grade' => 'decimal:2'
    ];

    /**
     * Relationships
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Accessors and Mutators
     */
    public function getIsLateAttribute(): bool
    {
        return $this->submitted_at > $this->assignment->due_date;
    }

    public function getFileUrlsAttribute(): array
    {
        if (!$this->file_paths) {
            return [];
        }

        return collect($this->file_paths)->map(function ($path) {
            return Storage::url($path);
        })->toArray();
    }

    public function getTotalFileSizeAttribute(): int
    {
        if (!$this->file_metadata) {
            return 0;
        }

        return collect($this->file_metadata)->sum('size');
    }

    /**
     * Scopes
     */
    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Helper Methods
     */
    public function markAsGraded(float $grade, string $feedback = null, int $gradedBy = null): void
    {
        $this->update([
            'status' => 'graded',
            'grade' => $grade,
            'teacher_feedback' => $feedback,
            'graded_at' => now(),
            'graded_by' => $gradedBy
        ]);
    }

    public function deleteFiles(): void
    {
        if ($this->file_paths) {
            foreach ($this->file_paths as $path) {
                Storage::delete($path);
            }
        }
    }
}

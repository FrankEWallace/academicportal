<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GradeUpload extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',
        'grade_type',
        'title',
        'file_path',
        'original_filename',
        'upload_metadata',
        'status',
        'processing_results',
        'total_records',
        'successful_records',
        'failed_records',
        'error_messages',
        'processed_at'
    ];

    protected $casts = [
        'upload_metadata' => 'array',
        'processing_results' => 'array',
        'processed_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Accessors
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return ($this->successful_records / $this->total_records) * 100;
    }

    /**
     * Scopes
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByGradeType($query, $gradeType)
    {
        return $query->where('grade_type', $gradeType);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Helper Methods
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(array $results): void
    {
        $this->update([
            'status' => 'completed',
            'processing_results' => $results,
            'processed_at' => now()
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_messages' => $error,
            'processed_at' => now()
        ]);
    }

    public function deleteFile(): void
    {
        Storage::delete($this->file_path);
    }
}

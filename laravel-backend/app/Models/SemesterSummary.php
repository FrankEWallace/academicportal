<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterSummary extends Model
{
    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'total_courses',
        'total_units',
        'semester_gpa',
        'cumulative_gpa',
        'total_units_earned',
        'semester_status',
        'transcript_path',
        'transcript_generated_date',
    ];

    protected $casts = [
        'semester_gpa' => 'decimal:2',
        'cumulative_gpa' => 'decimal:2',
        'transcript_generated_date' => 'date',
    ];

    /**
     * Get the student that owns the summary.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
        return $query->where('semester_status', $status);
    }

    /**
     * Check if student is on probation.
     */
    public function isOnProbation(): bool
    {
        return $this->semester_status === 'probation' || $this->semester_gpa < 2.0;
    }

    /**
     * Check if student is in good standing.
     */
    public function isGoodStanding(): bool
    {
        return $this->semester_gpa >= 2.0 && $this->semester_status === 'completed';
    }
}

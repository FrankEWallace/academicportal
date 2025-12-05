<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'assignment_id',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    /**
     * Get the student that owns the assignment grade.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the assignment that owns the grade.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the user who graded the assignment.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Calculate the percentage score.
     */
    public function getPercentageAttribute(): float
    {
        if (!$this->assignment || !$this->assignment->max_score) {
            return 0;
        }
        
        return ($this->score / $this->assignment->max_score) * 100;
    }

    /**
     * Get the letter grade based on score.
     */
    public function getLetterGradeAttribute(): string
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        return 'F';
    }
}

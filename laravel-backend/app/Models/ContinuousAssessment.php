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
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'assessment_date' => 'date',
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
}

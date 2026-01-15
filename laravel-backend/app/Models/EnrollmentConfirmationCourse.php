<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentConfirmationCourse extends Model
{
    protected $fillable = [
        'enrollment_confirmation_id',
        'course_id',
        'course_code',
        'course_title',
        'units',
        'prerequisites_met',
        'has_schedule_conflict',
        'conflict_details',
    ];

    protected $casts = [
        'prerequisites_met' => 'boolean',
        'has_schedule_conflict' => 'boolean',
    ];

    /**
     * Get the enrollment confirmation that owns the course.
     */
    public function enrollmentConfirmation(): BelongsTo
    {
        return $this->belongsTo(EnrollmentConfirmation::class);
    }

    /**
     * Get the course.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

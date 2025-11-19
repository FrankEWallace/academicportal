<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'assessment_type',
        'assessment_name',
        'max_marks',
        'obtained_marks',
        'grade_letter',
        'grade_point',
        'assessment_date',
        'remarks',
    ];

    protected $casts = [
        'max_marks' => 'decimal:2',
        'obtained_marks' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'assessment_date' => 'date',
    ];

    /**
     * Get the student that owns the grade.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course that owns the grade.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

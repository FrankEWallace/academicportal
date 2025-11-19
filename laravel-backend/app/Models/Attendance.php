<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'teacher_id',
        'date',
        'status',
        'marked_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'marked_at' => 'datetime',
    ];

    /**
     * Get the student that owns the attendance.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course that owns the attendance.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the teacher that marked the attendance.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

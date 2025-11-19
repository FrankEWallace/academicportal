<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'credits',
        'department_id',
        'teacher_id',
        'semester',
        'section',
        'schedule',
        'room',
        'max_students',
        'enrolled_students',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the department that owns the course.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the teacher assigned to the course.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the students enrolled in the course.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments');
    }

    /**
     * Get the attendance records for the course.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the grades for the course.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}

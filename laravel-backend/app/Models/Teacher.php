<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'designation',
        'qualification',
        'specialization',
        'joining_date',
        'salary',
        'experience_years',
        'office_room',
        'office_hours',
        'research_interests',
        'publications',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'publications' => 'array',
    ];

    /**
     * Get the user that owns the teacher.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that owns the teacher.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the courses assigned to the teacher.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Get the attendances marked by the teacher.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'admission_date',
        'department_id',
        'semester',
        'section',
        'batch',
        'parent_name',
        'parent_phone',
        'parent_email',
        'emergency_contact',
        'blood_group',
        'nationality',
        'religion',
        'current_gpa',
        'total_credits',
        'graduation_date',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'graduation_date' => 'date',
        'current_gpa' => 'decimal:2',
    ];

    /**
     * Get the user that owns the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that owns the student.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the enrollments for the student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the attendance records for the student.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the grades for the student.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the fee records for the student.
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}

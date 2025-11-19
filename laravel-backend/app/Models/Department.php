<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_teacher_id',
        'established_year',
        'budget',
        'location',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    /**
     * Get the head teacher of the department.
     */
    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    /**
     * Get the teachers for the department.
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Get the students for the department.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the courses for the department.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}

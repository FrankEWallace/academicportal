<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'teacher_id',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'profile_image',
        'is_active',
        'email_verified_at',
        'program',
        'year_level',
        'student_status',
        'enrollment_date',
        'current_cgpa',
        'bio',
        'social_links',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'current_cgpa' => 'decimal:2',
        'social_links' => 'array',
    ];

    /**
     * Get the student profile associated with the user.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the teacher profile associated with the user.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the notification preferences for the user.
     */
    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }
}

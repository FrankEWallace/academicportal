<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentConfirmation extends Model
{
    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'total_courses',
        'total_units',
        'prerequisites_satisfied',
        'schedule_conflicts_resolved',
        'confirmed',
        'timetable_understood',
        'attendance_policy_agreed',
        'academic_calendar_checked',
        'confirmation_date',
        'confirmation_email_sent',
        'admin_override',
        'override_by',
        'override_reason',
    ];

    protected $casts = [
        'prerequisites_satisfied' => 'boolean',
        'schedule_conflicts_resolved' => 'boolean',
        'confirmed' => 'boolean',
        'timetable_understood' => 'boolean',
        'attendance_policy_agreed' => 'boolean',
        'academic_calendar_checked' => 'boolean',
        'confirmation_date' => 'date',
        'admin_override' => 'boolean',
    ];

    /**
     * Get the student that owns the enrollment confirmation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the courses for this enrollment confirmation.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(EnrollmentConfirmationCourse::class);
    }

    /**
     * Get the admin who overrode this confirmation
     */
    public function overrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }

    /**
     * Check if all confirmations are checked.
     */
    public function allConfirmed(): bool
    {
        return $this->prerequisites_satisfied 
            && $this->schedule_conflicts_resolved 
            && $this->timetable_understood 
            && $this->attendance_policy_agreed 
            && $this->academic_calendar_checked;
    }

    /**
     * Confirm enrollment.
     */
    public function confirmEnrollment(): void
    {
        $this->confirmed = true;
        $this->confirmation_date = now();
        $this->save();
    }

    /**
     * Override confirmation (admin action)
     */
    public function override(string $reason, int $userId): bool
    {
        $this->update([
            'admin_override' => true,
            'override_by' => $userId,
            'override_reason' => $reason,
            'confirmed' => true,
            'confirmation_date' => now(),
        ]);

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'enrollment_confirmed',
            'Enrollment Confirmed (Admin Override)',
            "Your enrollment confirmation for {$this->semester_code} has been approved by admin override.",
            route('student.enrollment-confirmation.index')
        );

        return true;
    }
}

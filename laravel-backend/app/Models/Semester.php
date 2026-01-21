<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Semester extends Model
{
    protected $fillable = [
        'academic_year_id',
        'name',
        'semester_number',
        'start_date',
        'end_date',
        'is_active',
        'registration_start_date',
        'registration_end_date',
        'add_drop_deadline',
        'exam_start_date',
        'exam_end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'add_drop_deadline' => 'date',
        'exam_start_date' => 'date',
        'exam_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year for this semester
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Check if registration is currently open
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();
        
        if (!$this->registration_start_date || !$this->registration_end_date) {
            return false;
        }

        return $now->between($this->registration_start_date, $this->registration_end_date);
    }

    /**
     * Check if add/drop period is still active
     */
    public function canAddDrop(): bool
    {
        if (!$this->add_drop_deadline) {
            return false;
        }

        return now()->lte($this->add_drop_deadline);
    }

    /**
     * Activate this semester and deactivate others
     */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}


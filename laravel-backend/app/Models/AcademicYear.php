<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'registration_start_date',
        'registration_end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the semesters for this academic year
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
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
     * Activate this academic year and deactivate others
     */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}


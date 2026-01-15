<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceConfig extends Model
{
    protected $table = 'insurance_config';

    protected $fillable = [
        'requirement_level',
        'blocks_registration',
        'academic_year',
        'updated_by',
    ];

    protected $casts = [
        'blocks_registration' => 'boolean',
    ];

    /**
     * Get the user who updated this config
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get current active insurance configuration
     */
    public static function current(): ?self
    {
        return self::where('academic_year', self::currentAcademicYear())->first();
    }

    /**
     * Get current academic year in format YYYY/YYYY
     */
    public static function currentAcademicYear(): string
    {
        $month = now()->month;
        $year = now()->year;
        
        // If month is Aug-Dec, academic year is current/next
        // If month is Jan-Jul, academic year is previous/current
        if ($month >= 8) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    /**
     * Check if insurance is mandatory
     */
    public function isMandatory(): bool
    {
        return $this->requirement_level === 'mandatory';
    }

    /**
     * Check if insurance is optional
     */
    public function isOptional(): bool
    {
        return $this->requirement_level === 'optional';
    }

    /**
     * Check if insurance is disabled
     */
    public function isDisabled(): bool
    {
        return $this->requirement_level === 'disabled';
    }

    /**
     * Check if insurance blocks registration
     */
    public function blocksRegistration(): bool
    {
        return $this->blocks_registration;
    }

    /**
     * Get requirement level display name
     */
    public function getRequirementLevelDisplayAttribute(): string
    {
        return match($this->requirement_level) {
            'mandatory' => 'Mandatory',
            'optional' => 'Optional',
            'disabled' => 'Disabled',
            default => ucfirst($this->requirement_level),
        };
    }

    /**
     * Scope: Current academic year
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('academic_year', self::currentAcademicYear());
    }

    /**
     * Scope: Mandatory configurations
     */
    public function scopeMandatory($query)
    {
        return $query->where('requirement_level', 'mandatory');
    }
}

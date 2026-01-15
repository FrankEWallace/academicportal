<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentAccommodation extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year',
        'hostel_name',
        'block',
        'floor',
        'room_number',
        'room_type',
        'bed_number',
        'status',
        'allocation_date',
        'expiry_date',
        'renewal_eligible',
        'renewal_deadline',
        'allocation_letter_path',
        'special_requirements',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'expiry_date' => 'date',
        'renewal_deadline' => 'date',
        'renewal_eligible' => 'boolean',
    ];

    /**
     * Get the student that owns the accommodation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the roommates for this accommodation.
     */
    public function roommates(): HasMany
    {
        return $this->hasMany(AccommodationRoommate::class, 'accommodation_id');
    }

    /**
     * Get the fees for this accommodation.
     */
    public function fees(): HasMany
    {
        return $this->hasMany(AccommodationFee::class, 'accommodation_id');
    }

    /**
     * Get full room designation.
     */
    public function getFullRoomAttribute(): string
    {
        $parts = array_filter([
            $this->hostel_name,
            $this->block ? "Block {$this->block}" : null,
            $this->floor ? "Floor {$this->floor}" : null,
            "Room {$this->room_number}",
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Check if accommodation is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'allocated' && 
               (!$this->expiry_date || $this->expiry_date->isFuture());
    }

    /**
     * Check if renewal is due soon (within 30 days).
     */
    public function renewalDueSoon(): bool
    {
        return $this->renewal_deadline && 
               $this->renewal_deadline->isFuture() &&
               $this->renewal_deadline->diffInDays(now()) <= 30;
    }
}

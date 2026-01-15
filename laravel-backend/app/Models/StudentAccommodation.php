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
        'allocated_by',
        'allocated_at',
        'vacated_by',
        'vacated_at',
        'room_id',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'expiry_date' => 'date',
        'renewal_deadline' => 'date',
        'renewal_eligible' => 'boolean',
        'allocated_at' => 'datetime',
        'vacated_at' => 'datetime',
    ];

    /**
     * Get the student that owns the accommodation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the room
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the admin who allocated this accommodation
     */
    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    /**
     * Get the admin who vacated this accommodation
     */
    public function vacatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vacated_by');
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

    /**
     * Allocate accommodation to student
     */
    public function allocate(int $roomId, int $userId): bool
    {
        $room = Room::find($roomId);
        
        if (!$room || !$room->isAvailable()) {
            return false;
        }

        // Get room details
        $hostel = $room->hostel;

        $this->update([
            'room_id' => $roomId,
            'hostel_name' => $hostel->name,
            'room_number' => $room->room_number,
            'floor' => $room->floor,
            'status' => 'allocated',
            'allocated_by' => $userId,
            'allocated_at' => now(),
            'allocation_date' => now(),
        ]);

        // Update room occupancy
        $room->incrementOccupancy();

        // Send notification to student
        Notification::notify(
            $this->student_id,
            'accommodation_allocated',
            'Accommodation Allocated',
            "You have been allocated to {$hostel->name}, Room {$room->room_number}.",
            route('student.accommodation.index')
        );

        return true;
    }

    /**
     * Vacate accommodation
     */
    public function vacate(int $userId): bool
    {
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            $room?->decrementOccupancy();
        }

        $this->update([
            'status' => 'vacated',
            'vacated_by' => $userId,
            'vacated_at' => now(),
        ]);

        return true;
    }

    /**
     * Scope to get allocated accommodations
     */
    public function scopeAllocated($query)
    {
        return $query->where('status', 'allocated');
    }

    /**
     * Scope to get pending allocations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

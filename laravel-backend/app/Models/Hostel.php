<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    protected $fillable = [
        'name',
        'code',
        'gender',
        'total_rooms',
        'capacity',
        'description',
        'location',
        'is_active',
    ];

    protected $casts = [
        'total_rooms' => 'integer',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all rooms in this hostel
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get all accommodations in this hostel
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(StudentAccommodation::class);
    }

    /**
     * Get available rooms in this hostel
     */
    public function availableRooms(): HasMany
    {
        return $this->rooms()->where('status', 'available');
    }

    /**
     * Get occupancy statistics
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity == 0) {
            return 0;
        }
        
        $occupied = $this->rooms()->sum('current_occupancy');
        return round(($occupied / $this->capacity) * 100, 2);
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacityAttribute(): int
    {
        $occupied = $this->rooms()->sum('current_occupancy');
        return max(0, $this->capacity - $occupied);
    }

    /**
     * Check if hostel has available space
     */
    public function hasAvailableSpace(): bool
    {
        return $this->available_capacity > 0;
    }

    /**
     * Scope: Active hostels only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by gender
     */
    public function scopeForGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope: With available rooms
     */
    public function scopeWithAvailableSpace($query)
    {
        return $query->whereHas('rooms', function ($q) {
            $q->where('status', 'available')
              ->whereColumn('current_occupancy', '<', 'capacity');
        });
    }
}

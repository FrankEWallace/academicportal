<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'hostel_id',
        'room_number',
        'floor',
        'capacity',
        'current_occupancy',
        'status',
        'amenities',
    ];

    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'amenities' => 'array',
    ];

    /**
     * Get the hostel this room belongs to
     */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /**
     * Get all accommodations in this room
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(StudentAccommodation::class);
    }

    /**
     * Get current occupants
     */
    public function currentOccupants(): HasMany
    {
        return $this->accommodations()->where('status', 'active');
    }

    /**
     * Check if room is full
     */
    public function isFull(): bool
    {
        return $this->current_occupancy >= $this->capacity;
    }

    /**
     * Check if room is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && !$this->isFull();
    }

    /**
     * Get available beds
     */
    public function getAvailableBedsAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity == 0) {
            return 0;
        }
        return round(($this->current_occupancy / $this->capacity) * 100, 2);
    }

    /**
     * Increment occupancy
     */
    public function incrementOccupancy(): void
    {
        $this->increment('current_occupancy');
        $this->updateStatus();
    }

    /**
     * Decrement occupancy
     */
    public function decrementOccupancy(): void
    {
        $this->decrement('current_occupancy');
        $this->updateStatus();
    }

    /**
     * Update room status based on occupancy
     */
    public function updateStatus(): void
    {
        if ($this->current_occupancy >= $this->capacity) {
            $this->update(['status' => 'full']);
        } elseif ($this->current_occupancy > 0) {
            $this->update(['status' => 'occupied']);
        } else {
            $this->update(['status' => 'available']);
        }
    }

    /**
     * Scope: Available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                     ->whereColumn('current_occupancy', '<', 'capacity');
    }

    /**
     * Scope: Filter by hostel
     */
    public function scopeInHostel($query, int $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    /**
     * Scope: Filter by floor
     */
    public function scopeOnFloor($query, int $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope: Full rooms
     */
    public function scopeFull($query)
    {
        return $query->where('status', 'full')
                     ->orWhereColumn('current_occupancy', '>=', 'capacity');
    }
}

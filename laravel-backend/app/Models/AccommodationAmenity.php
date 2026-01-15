<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationAmenity extends Model
{
    protected $fillable = [
        'hostel_name',
        'amenity_name',
        'icon',
        'is_available',
        'description',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Scope to filter by hostel.
     */
    public function scopeByHostel($query, string $hostelName)
    {
        return $query->where('hostel_name', $hostelName);
    }

    /**
     * Scope to get available amenities.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}

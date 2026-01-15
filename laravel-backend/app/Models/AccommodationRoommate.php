<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationRoommate extends Model
{
    protected $fillable = [
        'accommodation_id',
        'roommate_student_id',
        'roommate_name',
        'roommate_matric_no',
        'roommate_department',
        'roommate_level',
        'roommate_phone',
        'roommate_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the accommodation that owns the roommate.
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(StudentAccommodation::class, 'accommodation_id');
    }

    /**
     * Get the roommate student.
     */
    public function roommateStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'roommate_student_id');
    }
}

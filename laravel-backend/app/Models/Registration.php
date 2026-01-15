<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'status',
        'total_fees',
        'amount_paid',
        'balance',
        'fees_verified',
        'insurance_verified',
        'registration_date',
        'verification_date',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'total_fees' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'fees_verified' => 'boolean',
        'insurance_verified' => 'boolean',
        'registration_date' => 'date',
        'verification_date' => 'date',
    ];

    /**
     * Get the student that owns the registration.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified this registration.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope to filter by semester.
     */
    public function scopeBySemester($query, string $semesterCode)
    {
        return $query->where('semester_code', $semesterCode);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if registration is fully verified.
     */
    public function isFullyVerified(): bool
    {
        return $this->fees_verified && $this->insurance_verified;
    }

    /**
     * Calculate payment percentage.
     */
    public function paymentPercentage(): float
    {
        if ($this->total_fees == 0) {
            return 0;
        }
        return ($this->amount_paid / $this->total_fees) * 100;
    }
}

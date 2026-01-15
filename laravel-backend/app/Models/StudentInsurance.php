<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInsurance extends Model
{
    protected $table = 'student_insurance';

    protected $fillable = [
        'student_id',
        'semester_code',
        'academic_year',
        'provider',
        'policy_number',
        'document_path',
        'expiry_date',
        'status',
        'submission_date',
        'verification_date',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'submission_date' => 'date',
        'verification_date' => 'date',
    ];

    /**
     * Get the student that owns the insurance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified this insurance.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if insurance is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if insurance is verified and valid.
     */
    public function isValid(): bool
    {
        return $this->status === 'verified' && !$this->isExpired();
    }
}

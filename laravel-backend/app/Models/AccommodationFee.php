<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationFee extends Model
{
    protected $fillable = [
        'accommodation_id',
        'fee_type',
        'amount',
        'amount_paid',
        'balance',
        'status',
        'due_date',
        'payment_date',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Get the accommodation that owns the fee.
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(StudentAccommodation::class, 'accommodation_id');
    }

    /**
     * Calculate payment percentage.
     */
    public function paymentPercentage(): float
    {
        if ($this->amount == 0) {
            return 0;
        }
        return ($this->amount_paid / $this->amount) * 100;
    }

    /**
     * Check if payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && 
               $this->due_date &&
               $this->due_date->isPast();
    }
}

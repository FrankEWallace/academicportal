<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_type',
        'amount',
        'due_date',
        'paid_amount',
        'paid_date',
        'payment_method',
        'transaction_id',
        'status',
        'late_fee',
        'discount',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    /**
     * Get the student that owns the fee.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'program',
        'semester',
        'amount',
        'due_date',
        'fee_type',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'semester' => 'integer',
    ];

    // Scopes for easier querying
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForProgram($query, $program)
    {
        return $query->where('program', $program);
    }

    public function scopeForSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeDueBefore($query, $date)
    {
        return $query->where('due_date', '<=', $date);
    }

    public function scopeDueAfter($query, $date)
    {
        return $query->where('due_date', '>=', $date);
    }

    // Accessor for formatted amount
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    // Check if fee is overdue
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now();
    }
}

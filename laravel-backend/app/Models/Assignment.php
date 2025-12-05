<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'due_date',
        'max_score',
        'status',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_score' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the course that owns the assignment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Note: submissions() method will be added when AssignmentSubmission model is created

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published assignments.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include assignments due in the future.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now());
    }

    /**
     * Scope a query to only include overdue assignments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', 'published');
    }

    /**
     * Get the assignment's formatted due date.
     */
    public function getFormattedDueDateAttribute(): string
    {
        return $this->due_date->format('M j, Y \a\t g:i A');
    }

    /**
     * Check if the assignment is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->status === 'published';
    }

    /**
     * Get days until due date (negative if overdue).
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get the grades for this assignment.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(AssignmentGrade::class);
    }
}

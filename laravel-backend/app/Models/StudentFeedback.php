<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeedback extends Model
{
    protected $table = 'student_feedback';

    protected $fillable = [
        'ticket_number',
        'student_id',
        'category',
        'priority',
        'subject',
        'message',
        'status',
        'submission_date',
        'resolved_date',
        'assigned_to',
        'response_count',
        'student_viewed_response',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'resolved_date' => 'date',
        'student_viewed_response' => 'boolean',
    ];

    /**
     * Get the student that owns the feedback.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user assigned to this feedback.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the responses for this feedback.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackResponse::class, 'feedback_id');
    }

    /**
     * Get the attachments for this feedback.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(FeedbackAttachment::class, 'feedback_id');
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $lastTicket = self::where('ticket_number', 'like', "FB-{$year}-%")
                          ->orderBy('id', 'desc')
                          ->first();
        
        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "FB-{$year}-{$newNumber}";
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if feedback is resolved.
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Check if feedback is open.
     */
    public function isOpen(): bool
    {
        return !$this->isResolved();
    }
}

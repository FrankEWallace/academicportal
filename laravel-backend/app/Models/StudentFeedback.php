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
        'assigned_by',
        'assigned_at',
        'department',
        'priority_changed_by',
        'priority_changed_at',
        'response_count',
        'student_viewed_response',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'resolved_date' => 'date',
        'student_viewed_response' => 'boolean',
        'assigned_at' => 'datetime',
        'priority_changed_at' => 'datetime',
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
     * Get the admin who assigned this feedback
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the admin who changed the priority
     */
    public function priorityChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'priority_changed_by');
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
     * Scope to get unassigned feedback
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope to get assigned feedback
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    /**
     * Scope to get high priority feedback
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
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

    /**
     * Assign feedback to a user
     */
    public function assign(int $assignToUserId, int $assignedByUserId, ?string $department = null): bool
    {
        $this->update([
            'assigned_to' => $assignToUserId,
            'assigned_by' => $assignedByUserId,
            'assigned_at' => now(),
            'department' => $department,
            'status' => 'assigned',
        ]);

        // Send notification to assigned user
        Notification::notify(
            $assignToUserId,
            'feedback_responded',
            'Feedback Assigned',
            "You have been assigned to ticket {$this->ticket_number}: {$this->subject}",
            route('admin.feedback.show', $this->id)
        );

        return true;
    }

    /**
     * Change the priority of feedback
     */
    public function changePriority(string $newPriority, int $userId): bool
    {
        $oldPriority = $this->priority;

        $this->update([
            'priority' => $newPriority,
            'priority_changed_by' => $userId,
            'priority_changed_at' => now(),
        ]);

        // Notify assigned user if priority changed to high
        if ($newPriority === 'high' && $this->assigned_to) {
            Notification::notify(
                $this->assigned_to,
                'feedback_responded',
                'Priority Changed to High',
                "Ticket {$this->ticket_number} priority has been changed from {$oldPriority} to HIGH.",
                route('admin.feedback.show', $this->id)
            );
        }

        return true;
    }
}

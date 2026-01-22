<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'document_type',
        'reason',
        'additional_info',
        'status',
        'requested_at',
        'processed_at',
        'completed_at',
        'rejection_reason',
        'file_path',
        'notes',
        'processed_by',
        'fee_amount',
        'fee_paid',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'fee_amount' => 'decimal:2',
        'fee_paid' => 'boolean',
    ];

    protected $appends = ['download_url'];

    /**
     * Get the student that owns the document request
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get download URL for completed documents
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->status === 'completed' && $this->file_path) {
            return route('api.student.document-requests.download', $this->id);
        }
        return null;
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get processing requests
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to get completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Mark request as processing
     */
    public function markAsProcessing(?int $processedBy = null): bool
    {
        $this->status = 'processing';
        $this->processed_at = now();
        if ($processedBy) {
            $this->processed_by = $processedBy;
        }
        return $this->save();
    }

    /**
     * Mark request as approved
     */
    public function markAsApproved(): bool
    {
        $this->status = 'approved';
        return $this->save();
    }

    /**
     * Mark request as completed
     */
    public function markAsCompleted(string $filePath): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->file_path = $filePath;
        return $this->save();
    }

    /**
     * Mark request as rejected
     */
    public function markAsRejected(string $reason): bool
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->processed_at = now();
        return $this->save();
    }
}

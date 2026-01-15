<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackResponse extends Model
{
    protected $fillable = [
        'feedback_id',
        'responder_id',
        'message',
        'response_date',
        'is_internal_note',
    ];

    protected $casts = [
        'response_date' => 'date',
        'is_internal_note' => 'boolean',
    ];

    /**
     * Get the feedback that owns the response.
     */
    public function feedback(): BelongsTo
    {
        return $this->belongsTo(StudentFeedback::class, 'feedback_id');
    }

    /**
     * Get the user who wrote the response.
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    /**
     * Scope to get only public responses.
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    /**
     * Scope to get only internal notes.
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }
}

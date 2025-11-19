<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'target_audience',
        'department_id',
        'created_by',
        'published_at',
        'expires_at',
        'is_published',
        'attachments',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_published' => 'boolean',
        'attachments' => 'array',
        'target_audience' => 'array',
    ];

    /**
     * Get the user who created the announcement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the department associated with the announcement.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

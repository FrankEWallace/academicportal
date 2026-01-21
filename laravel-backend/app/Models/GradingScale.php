<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    protected $fillable = [
        'grade',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description',
        'is_passing',
        'order',
        'is_active',
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_passing' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get grade for a given percentage
     */
    public static function getGradeForPercentage(float $percentage): ?self
    {
        return static::where('is_active', true)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderBy('order')
            ->first();
    }

    /**
     * Scope to get only active grades
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_grade',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description',
        'is_passing',
        'order'
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_passing' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get grade point for a specific percentage
     */
    public static function getGradeForPercentage(float $percentage): ?self
    {
        return self::where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();
    }

    /**
     * Get all passing grades
     */
    public function scopePassing($query)
    {
        return $query->where('is_passing', true);
    }

    /**
     * Get all failing grades
     */
    public function scopeFailing($query)
    {
        return $query->where('is_passing', false);
    }

    /**
     * Order by grade point descending
     */
    public function scopeOrderByGrade($query)
    {
        return $query->orderBy('grade_point', 'desc');
    }
}

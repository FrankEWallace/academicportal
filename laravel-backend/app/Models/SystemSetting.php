<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'category',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the typed value based on the type field
     */
    public function getTypedValue()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($this->value) ? (float) $this->value : null,
            'json' => json_decode($this->value, true),
            'date' => $this->value ? \Carbon\Carbon::parse($this->value) : null,
            default => $this->value,
        };
    }

    /**
     * Set the value with proper type conversion
     */
    public function setTypedValue($value): void
    {
        $this->value = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_array($value) ? json_encode($value) : $value,
            'date' => $value instanceof \Carbon\Carbon ? $value->toDateString() : $value,
            default => (string) $value,
        };
    }
}


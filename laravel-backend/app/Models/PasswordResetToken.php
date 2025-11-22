<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * Check if the token has expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Scope to get valid tokens
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Generate a secure token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Check if the token is valid (exists and not expired)
     */
    public static function isValidToken(string $email, string $token): bool
    {
        $resetToken = self::where('email', $email)
            ->where('token', $token)
            ->valid()
            ->first();

        return $resetToken !== null;
    }

    /**
     * Generate and store a new reset token for email
     */
    public static function createTokenForEmail(string $email): string
    {
        // Delete any existing tokens for this email
        self::where('email', $email)->delete();

        // Generate a new token
        $token = self::generateToken();

        // Store the new token
        self::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(1), // Token expires in 1 hour
        ]);

        return $token;
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanExpiredTokens(): void
    {
        self::where('expires_at', '<', Carbon::now())->delete();
    }
}

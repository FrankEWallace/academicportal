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
        // Use hash_equals for constant-time comparison to prevent timing attacks
        $resetToken = self::where('email', $email)
            ->valid()
            ->get();

        foreach ($resetToken as $tokenRecord) {
            if (hash_equals($tokenRecord->token, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate and store a new reset token for email
     */
    public static function createTokenForEmail(string $email): string
    {
        // Rate limiting - check if too many tokens requested recently
        $recentTokens = self::where('email', $email)
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->count();

        if ($recentTokens >= 3) {
            throw new \Exception('Too many password reset requests. Please wait before trying again.');
        }

        // Delete any existing tokens for this email
        self::where('email', $email)->delete();

        // Generate a new secure token
        $token = self::generateToken();

        // Store the new token with shorter expiration for security
        self::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30), // Token expires in 30 minutes
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

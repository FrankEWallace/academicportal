<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogins
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $ip = $request->ip();
        
        // Check if account is locked
        if ($this->isLocked($email)) {
            $remainingTime = $this->getRemainingLockTime($email);
            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked due to multiple failed login attempts. Please try again in {$remainingTime} minutes.",
                'locked_until' => Cache::get("login_lock:{$email}")
            ], 429);
        }

        // Check IP-based rate limiting
        if ($this->tooManyAttempts($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts from this IP. Please try again later.',
            ], 429);
        }

        $response = $next($request);

        // If login failed (401), increment fail count
        if ($response->getStatusCode() === 401 && $email) {
            $this->incrementFailedAttempts($email, $ip);
        }

        // If login succeeded (200), clear fail count
        if ($response->getStatusCode() === 200 && $email) {
            $this->clearFailedAttempts($email, $ip);
        }

        return $response;
    }

    /**
     * Check if account is locked
     */
    protected function isLocked(string $email): bool
    {
        return Cache::has("login_lock:{$email}");
    }

    /**
     * Get remaining lock time in minutes
     */
    protected function getRemainingLockTime(string $email): int
    {
        $lockedUntil = Cache::get("login_lock:{$email}");
        if (!$lockedUntil) {
            return 0;
        }
        
        $remaining = $lockedUntil - now()->timestamp;
        return max(0, ceil($remaining / 60));
    }

    /**
     * Check if too many attempts from IP
     */
    protected function tooManyAttempts(string $ip): bool
    {
        return RateLimiter::tooManyAttempts("login_ip:{$ip}", 10);
    }

    /**
     * Increment failed login attempts
     */
    protected function incrementFailedAttempts(string $email, string $ip): void
    {
        $attempts = Cache::get("login_attempts:{$email}", 0);
        $attempts++;
        
        Cache::put("login_attempts:{$email}", $attempts, now()->addMinutes(30));
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $lockDuration = 15; // minutes
            Cache::put("login_lock:{$email}", now()->addMinutes($lockDuration)->timestamp, now()->addMinutes($lockDuration));
            
            // Log security event
            \Log::warning('Account locked due to failed login attempts', [
                'email' => $email,
                'ip' => $ip,
                'attempts' => $attempts,
                'locked_until' => now()->addMinutes($lockDuration)
            ]);
        }

        // IP-based rate limiting
        RateLimiter::hit("login_ip:{$ip}", 3600); // 1 hour window
    }

    /**
     * Clear failed login attempts
     */
    protected function clearFailedAttempts(string $email, string $ip): void
    {
        Cache::forget("login_attempts:{$email}");
        Cache::forget("login_lock:{$email}");
        RateLimiter::clear("login_ip:{$ip}");
    }
}

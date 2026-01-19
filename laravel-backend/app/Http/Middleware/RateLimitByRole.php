<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByRole
{
    /**
     * Handle an incoming request with role-based rate limiting
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            // Stricter limits for unauthenticated requests
            $key = 'unauthenticated:' . $request->ip();
            $max = 10; // 10 requests per minute
        } else {
            // Role-based limits
            $key = 'user:' . $user->id;
            $max = match($user->role) {
                'admin' => 120,      // Admins get higher limits
                'lecturer' => 100,
                'student' => 60,
                default => 30
            };
        }
        
        $executed = RateLimiter::attempt(
            $key,
            $max,
            function() {},
            (int) $decayMinutes * 60
        );

        if (!$executed) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please slow down.',
            ], 429);
        }

        return $next($request);
    }
}

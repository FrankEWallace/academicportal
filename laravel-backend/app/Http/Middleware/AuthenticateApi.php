<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Authorization header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token required',
                'error_code' => 'TOKEN_MISSING'
            ], 401);
        }

        // Find the token in the database
        $personalAccessToken = PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication token',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
        }

        // Check if token has expired
        if ($personalAccessToken->created_at->addMinutes(config('sanctum.expiration', 525600))->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token has expired',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        }

        // Check if user is active
        $user = $personalAccessToken->tokenable;
        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User account is inactive',
                'error_code' => 'USER_INACTIVE'
            ], 401);
        }

        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Update last activity
        $personalAccessToken->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApiRequests extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        // Get the rate limit key based on IP and endpoint
        $key = $this->resolveRequestSignature($request);

        // Check if the request should be throttled
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts, $request);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Create a 'too many attempts' response.
     */
    protected function buildTooManyAttemptsResponse($key, $maxAttempts, $request)
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $retryAfter
        ], 429);
    }

    /**
     * Resolve the rate limiting key for the request.
     */
    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->route()->getDomain() .
            '|' . $request->ip() .
            '|' . $request->route()->getName()
        );
    }
}

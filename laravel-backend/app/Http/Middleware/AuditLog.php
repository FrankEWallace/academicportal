<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLog
{
    /**
     * Sensitive actions that should always be logged
     */
    protected array $sensitiveActions = [
        'POST:/api/admin/departments',
        'PUT:/api/admin/departments/*',
        'DELETE:/api/admin/departments/*',
        'POST:/api/admin/fee-structures',
        'PUT:/api/admin/fee-structures/*',
        'DELETE:/api/admin/fee-structures/*',
        'POST:/api/admin/results/approve',
        'POST:/api/admin/results/reject',
        'POST:/api/admin/results/publish',
        'PUT:/api/admin/users/*',
        'DELETE:/api/admin/users/*',
        'POST:/api/admin/enrollments/approve',
        'POST:/api/admin/enrollments/reject',
    ];

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Get response
        $response = $next($request);
        
        $user = $request->user();
        $route = $request->method() . ':' . $request->path();
        
        // Check if this is a sensitive action
        $isSensitive = $this->isSensitiveAction($route);
        
        // Log all admin actions and sensitive operations
        if ($user && ($user->role === 'admin' || $isSensitive)) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $logData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeData($request->except(['password', 'password_confirmation'])),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'timestamp' => now()->toIso8601String(),
            ];
            
            // Log to dedicated audit channel
            Log::channel('audit')->info('User Action', $logData);
            
            // Also store in database for sensitive actions
            if ($isSensitive) {
                $this->storeAuditLog($logData);
            }
        }
        
        return $response;
    }
    
    /**
     * Check if action is sensitive
     */
    protected function isSensitiveAction(string $route): bool
    {
        foreach ($this->sensitiveActions as $pattern) {
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('#^' . $pattern . '$#', $route)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sanitize sensitive data from logs
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        
        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });
        
        return $data;
    }
    
    /**
     * Store audit log in database
     */
    protected function storeAuditLog(array $data): void
    {
        try {
            \DB::table('audit_logs')->insert([
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'request_data' => json_encode($data['request_data']),
                'status_code' => $data['status_code'],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store audit log: ' . $e->getMessage());
        }
    }
}

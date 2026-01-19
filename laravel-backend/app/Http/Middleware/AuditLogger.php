<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    /**
     * Handle an incoming request and log critical actions
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log authenticated requests
        if ($request->user()) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Log the request details
     */
    protected function logRequest(Request $request, Response $response): void
    {
        // Critical actions to log
        $criticalActions = [
            'POST' => ['results', 'grades', 'users', 'departments', 'fees', 'invoices'],
            'PUT' => ['results', 'grades', 'users', 'departments', 'fees'],
            'PATCH' => ['results', 'grades', 'users'],
            'DELETE' => ['results', 'grades', 'users', 'departments'],
        ];

        $method = $request->method();
        $path = $request->path();

        // Check if this is a critical action
        $shouldLog = false;
        if (isset($criticalActions[$method])) {
            foreach ($criticalActions[$method] as $keyword) {
                if (str_contains($path, $keyword)) {
                    $shouldLog = true;
                    break;
                }
            }
        }

        // Always log admin actions
        if (str_contains($path, 'admin/')) {
            $shouldLog = true;
        }

        if ($shouldLog) {
            try {
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => $method . ' ' . $path,
                    'table_name' => $this->extractTableName($path),
                    'record_id' => $this->extractRecordId($request),
                    'old_values' => null,
                    'new_values' => $this->sanitizeData($request->all()),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => $response->getStatusCode(),
                ]);
            } catch (\Exception $e) {
                // Don't fail the request if logging fails
                Log::error('Audit logging failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Extract table name from path
     */
    protected function extractTableName(string $path): ?string
    {
        $segments = explode('/', $path);
        return end($segments);
    }

    /**
     * Extract record ID from request
     */
    protected function extractRecordId(Request $request): ?int
    {
        // Try to get ID from route parameters
        $route = $request->route();
        if ($route && $route->hasParameter('id')) {
            return (int) $route->parameter('id');
        }

        // Try to get from request data
        if ($request->has('id')) {
            return (int) $request->input('id');
        }

        return null;
    }

    /**
     * Remove sensitive data from logging
     */
    protected function sanitizeData(array $data): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];
        
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}

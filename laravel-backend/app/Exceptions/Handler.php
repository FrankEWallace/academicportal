<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Authorization\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Only return JSON responses for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions and return standardized JSON responses.
     */
    private function handleApiException($request, Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $message = 'Internal Server Error';
        $errors = null;

        switch (true) {
            case $exception instanceof ValidationException:
                $statusCode = 422;
                $message = 'Validation Error';
                $errors = $exception->errors();
                break;

            case $exception instanceof AuthenticationException:
                $statusCode = 401;
                $message = 'Unauthenticated';
                break;

            case $exception instanceof AuthorizationException:
                $statusCode = 403;
                $message = 'Unauthorized';
                break;

            case $exception instanceof ModelNotFoundException:
                $statusCode = 404;
                $message = 'Resource Not Found';
                break;

            case $exception instanceof NotFoundHttpException:
                $statusCode = 404;
                $message = 'Endpoint Not Found';
                break;

            case $exception instanceof MethodNotAllowedHttpException:
                $statusCode = 405;
                $message = 'Method Not Allowed';
                break;

            case $exception instanceof QueryException:
                $statusCode = 500;
                $message = 'Database Error';
                // In production, don't expose database details
                if (config('app.debug')) {
                    $errors = ['database' => $exception->getMessage()];
                }
                break;

            default:
                // Handle other exceptions
                if (method_exists($exception, 'getStatusCode')) {
                    $statusCode = $exception->getStatusCode();
                }
                
                if (!empty($exception->getMessage())) {
                    $message = $exception->getMessage();
                }
                
                // In debug mode, provide more details
                if (config('app.debug')) {
                    $errors = [
                        'exception' => get_class($exception),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTraceAsString(),
                    ];
                }
                break;
        }

        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // Add timestamp for debugging
        $response['timestamp'] = now()->toISOString();

        // Add request ID for tracking (if you have one)
        if ($request->header('X-Request-ID')) {
            $response['request_id'] = $request->header('X-Request-ID');
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'status_code' => 401,
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}

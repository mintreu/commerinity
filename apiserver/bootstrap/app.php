<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Token mode: No stateful API (no CSRF for API routes)
        // statefulApi() is only for cookie mode
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions - NEVER expose database errors
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                // Log the actual error for debugging
                report($e);

                // Return generic error to client
                return response()->json([
                    'message' => 'An error occurred while processing your request. Please try again.',
                    'error' => 'server_error',
                ], 500);
            }
        });

        // Handle 404 for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                    'error' => 'not_found',
                ], 404);
            }
        });

        // Handle all other exceptions for API routes
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                // Don't override validation errors - Laravel handles these properly
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Let Laravel handle it
                }

                // Don't override HttpResponseException - it already has a response (e.g., rate limiter custom response)
                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    return null; // Let Laravel handle it
                }

                // Authentication errors
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                        'error' => 'unauthenticated',
                    ], 401);
                }

                // Authorization errors
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'message' => 'You are not authorized to perform this action.',
                        'error' => 'forbidden',
                    ], 403);
                }

                // Model not found errors
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'message' => 'The requested resource was not found.',
                        'error' => 'not_found',
                    ], 404);
                }

                // Token mismatch (CSRF) errors
                if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                    return response()->json([
                        'message' => 'Your session has expired. Please refresh and try again.',
                        'error' => 'token_mismatch',
                    ], 419);
                }

                // Too many requests (rate limiting)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                    return response()->json([
                        'message' => $e->getMessage() ?: 'Too many requests. Please wait a moment and try again.',
                        'error' => 'too_many_requests',
                    ], 429);
                }

                // HTTP exceptions with specific status codes
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                    $message = match ($statusCode) {
                        400 => 'Bad request. Please check your input.',
                        401 => 'Unauthenticated.',
                        403 => 'You are not authorized to perform this action.',
                        404 => 'The requested resource was not found.',
                        405 => 'This action is not allowed.',
                        408 => 'Request timeout. Please try again.',
                        409 => 'A conflict occurred. Please refresh and try again.',
                        422 => 'The given data was invalid.',
                        429 => 'Too many requests. Please wait a moment and try again.',
                        500 => 'An unexpected error occurred. Please try again.',
                        502 => 'Service temporarily unavailable. Please try again.',
                        503 => 'Service temporarily unavailable. Please try again.',
                        default => 'An unexpected error occurred. Please try again.',
                    };

                    return response()->json([
                        'message' => $message,
                        'error' => 'server_error',
                    ], $statusCode);
                }

                // Log the error for debugging (internal only)
                report($e);

                // NEVER expose internal error messages - always return generic message
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again.',
                    'error' => 'server_error',
                ], 500);
            }
        });
    })->create();

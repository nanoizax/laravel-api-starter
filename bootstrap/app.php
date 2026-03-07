<?php

use App\Exceptions\Domain\AccountDisabledException;
use App\Exceptions\Domain\EmailAlreadyInUseException;
use App\Exceptions\Domain\InvalidCredentialsException;
use App\Exceptions\Domain\UserNotFoundException;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\RepositoryServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UserNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()],
                ], 404);
            }
        });

        $exceptions->render(function (EmailAlreadyInUseException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'CONFLICT', 'message' => $e->getMessage()],
                ], 409);
            }
        });

        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()],
                ], 401);
            }
        });

        $exceptions->render(function (AccountDisabledException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()],
                ], 403);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validation failed',
                        'details' => collect($e->errors())->map(fn ($msgs, $field) => [
                            'field' => $field,
                            'message' => $msgs[0],
                        ])->values(),
                    ],
                ], 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found'],
                ], 404);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() && !config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Internal server error'],
                ], 500);
            }
        });
    })->create();

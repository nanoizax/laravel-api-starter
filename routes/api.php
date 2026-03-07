<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/me', [UserController::class, 'showMe'] ?? [UserController::class, 'show']);

    Route::middleware('can:admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
    });

    Route::put('users/{id}', [UserController::class, 'update']);
});

Route::get('health', function () {
    try {
        DB::select('SELECT 1');
        return response()->json(['status' => 'healthy', 'timestamp' => now()->toISOString(), 'services' => ['database' => 'up']]);
    } catch (\Exception) {
        return response()->json(['status' => 'unhealthy', 'services' => ['database' => 'down']], 503);
    }
});

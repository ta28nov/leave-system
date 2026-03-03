<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
 * Route Authentication: /api/auth
 * Gồm route công khai (register, login) và route cần token (logout, refresh, me).
 */

Route::prefix('auth')->group(function () {
    
    // === Route công khai (không cần token) ===
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // === Route cần token (auth:api kiểm tra JWT token) ===
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;

// Rutas de autenticación (sin protección)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);

// Recuperación de contraseña
Route::post('/auth/request-password-reset', [AuthController::class, 'requestPasswordReset']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de autenticación
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/auth/user', [AuthController::class, 'getUser']);
    
    // Rutas del chat
    Route::prefix('chat')->group(function () {
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::get('/messages', [ChatController::class, 'getMessages']);
        Route::get('/stats', [ChatController::class, 'getStats']);
    });
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MensajeController;

// Rutas de autenticación (públicas)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Mensajes
    Route::prefix('mensajes')->group(function () {
        Route::post('/enviar', [MensajeController::class, 'enviar']);
        Route::get('/listar', [MensajeController::class, 'listar']);
        Route::get('/{id}', [MensajeController::class, 'obtener']);
        Route::put('/{id}/estado', [MensajeController::class, 'actualizarEstado']);
        Route::get('/estadisticas/general', [MensajeController::class, 'estadisticas']);
    });
});

// Ruta pública para obtener mensajes
Route::get('/mensajes/listar', [MensajeController::class, 'listar']);


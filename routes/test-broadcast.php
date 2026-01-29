<?php

use App\Events\MensajeEnviado;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Route;

Route::get('/test-broadcast', function () {
    // Obtener el último mensaje
    $mensaje = Mensaje::with('usuario')->latest('id_mensaje')->first();
    
    if (!$mensaje) {
        return response()->json(['error' => 'No messages found'], 404);
    }
    
    \Log::info('TEST: Disparando evento de broadcast para mensaje:', ['id' => $mensaje->id_mensaje]);
    
    // Disparar evento
    MensajeEnviado::dispatch($mensaje);
    
    \Log::info('TEST: Evento disparado');
    
    return response()->json([
        'success' => true,
        'message' => 'Test broadcast sent',
        'mensaje' => $mensaje
    ]);
});

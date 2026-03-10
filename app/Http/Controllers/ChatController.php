<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\ChatMessageSent;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    /**
     * Enviar mensaje de chat
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $usuario = $request->user();
        
        // Guardar en la base de datos
        $chatMessage = ChatMessage::create([
            'user' => $usuario->nombre_completo,
            'message' => $validated['message'],
        ]);

        // Disparar el evento para WebSocket
        broadcast(new ChatMessageSent(
            $usuario->nombre_completo,
            $validated['message']
        ));

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente',
            'data' => [
                'id' => $chatMessage->id,
                'user' => $chatMessage->user,
                'user_id' => $usuario->id_usuario,
                'message' => $chatMessage->message,
                'timestamp' => $chatMessage->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * Obtener historial de mensajes
     */
    public function getMessages(Request $request)
    {
        $limit = $request->input('limit', 50);
        
        $messages = ChatMessage::getRecent($limit);

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'user' => $message->user,
                    'content' => $message->message,
                    'timestamp' => $message->created_at->toISOString(),
                ];
            })
        ]);
    }

    /**
     * Obtener estadísticas del chat
     */
    public function getStats()
    {
        $totalMessages = ChatMessage::count();
        $uniqueUsers = ChatMessage::distinct('user')->count('user');
        $recentUsers = ChatMessage::select('user')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('user');

        return response()->json([
            'success' => true,
            'data' => [
                'total_messages' => $totalMessages,
                'unique_users' => $uniqueUsers,
                'recent_users' => $recentUsers,
            ]
        ]);
    }
}
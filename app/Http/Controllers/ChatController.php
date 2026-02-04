<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\ChatMessageSent;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    /**
     * Enviar mensaje de chat (requiere autenticación)
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        try {
            // Guardar en la base de datos con el usuario autenticado
            $chatMessage = ChatMessage::create([
                'user_id' => $request->user()->id,
                'content' => $validated['content'],
            ]);

            // Cargar la relación del usuario
            $chatMessage->load('user');

            // Disparar el evento para WebSocket (no bloquear si falla)
            try {
                broadcast(new ChatMessageSent(
                    $request->user()->name,
                    $validated['content']
                ));
            } catch (\Exception $e) {
                \Log::warning('Broadcast ChatMessageSent falló:', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'data' => [
                    'id' => $chatMessage->id,
                    'user_id' => $chatMessage->user_id,
                    'user' => [
                        'id' => $chatMessage->user->id,
                        'name' => $chatMessage->user->name,
                        'email' => $chatMessage->user->email,
                    ],
                    'content' => $chatMessage->content,
                    'timestamp' => $chatMessage->created_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
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
                    'user_id' => $message->user_id,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'email' => $message->user->email,
                    ],
                    'content' => $message->content,
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
        $uniqueUsers = ChatMessage::distinct('user_id')->count('user_id');
        $recentUsers = ChatMessage::select('user_id')
            ->with('user')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                ];
            });

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
<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Events\MensajeEnviado;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    /**
     * Enviar nuevo mensaje
     */
    public function enviar(Request $request)
    {
        $validated = $request->validate([
            'asunto' => 'nullable|string|max:200',
            'contenido' => 'required|string|max:5000',
            'tipo' => 'nullable|in:queja,sugerencia,consulta,aviso,emergencia',
            'prioridad' => 'nullable|in:baja,media,alta',
        ]);

        try {
            $mensaje = Mensaje::create([
                'id_usuario' => $request->user()->id_usuario,
                'asunto' => $validated['asunto'] ?? null,
                'contenido' => $validated['contenido'],
                'tipo' => $validated['tipo'] ?? 'consulta',
                'prioridad' => $validated['prioridad'] ?? 'media',
                'estado' => 'pendiente',
            ]);

            $mensaje->load('usuario');

            // Broadcastear el nuevo mensaje (no bloquear la respuesta si falla el broadcaster)
            \Log::info('Disparando evento MensajeEnviado para mensaje:', ['id' => $mensaje->id_mensaje]);
            try {
                MensajeEnviado::dispatch($mensaje);
            } catch (\Exception $e) {
                \Log::warning('No fue posible difundir MensajeEnviado (broadcast falló):', ['error' => $e->getMessage()]);
                // No interrumpimos la respuesta al cliente
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'data' => [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'id_usuario' => $mensaje->id_usuario,
                    'usuario' => [
                        'id_usuario' => $mensaje->usuario->id_usuario,
                        'nombre' => $mensaje->usuario->nombre,
                        'apellido' => $mensaje->usuario->apellido,
                    ],
                    'asunto' => $mensaje->asunto,
                    'contenido' => $mensaje->contenido,
                    'tipo' => $mensaje->tipo,
                    'prioridad' => $mensaje->prioridad,
                    'estado' => $mensaje->estado,
                    'fecha_envio' => $mensaje->fecha_envio,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar mensaje:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
    public function listar(Request $request)
    {
        $limit = $request->input('limit', 50);
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');
        $prioridad = $request->input('prioridad');

        $query = Mensaje::with('usuario')->orderBy('fecha_envio', 'asc');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($prioridad) {
            $query->where('prioridad', $prioridad);
        }

        $mensajes = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $mensajes->map(function ($mensaje) {
                return [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'id_usuario' => $mensaje->id_usuario,
                    'usuario' => [
                        'id_usuario' => $mensaje->usuario->id_usuario,
                        'nombre' => $mensaje->usuario->nombre,
                        'apellido' => $mensaje->usuario->apellido,
                        'email' => $mensaje->usuario->email,
                    ],
                    'asunto' => $mensaje->asunto,
                    'contenido' => $mensaje->contenido,
                    'tipo' => $mensaje->tipo,
                    'prioridad' => $mensaje->prioridad,
                    'estado' => $mensaje->estado,
                    'fecha_envio' => $mensaje->fecha_envio,
                    'fecha_respuesta' => $mensaje->fecha_respuesta,
                ];
            })
        ]);
    }

    /**
     * Obtener un mensaje específico
     */
    public function obtener($id)
    {
        $mensaje = Mensaje::with('usuario')->find($id);

        if (!$mensaje) {
            return response()->json([
                'success' => false,
                'message' => 'Mensaje no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_mensaje' => $mensaje->id_mensaje,
                'id_usuario' => $mensaje->id_usuario,
                'usuario' => [
                    'id_usuario' => $mensaje->usuario->id_usuario,
                    'nombre' => $mensaje->usuario->nombre,
                    'apellido' => $mensaje->usuario->apellido,
                    'email' => $mensaje->usuario->email,
                ],
                'asunto' => $mensaje->asunto,
                'contenido' => $mensaje->contenido,
                'tipo' => $mensaje->tipo,
                'prioridad' => $mensaje->prioridad,
                'estado' => $mensaje->estado,
                'fecha_envio' => $mensaje->fecha_envio,
                'fecha_respuesta' => $mensaje->fecha_respuesta,
            ]
        ]);
    }

    /**
     * Actualizar estado de mensaje
     */
    public function actualizarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,resuelto,cerrado',
        ]);

        $mensaje = Mensaje::find($id);

        if (!$mensaje) {
            return response()->json([
                'success' => false,
                'message' => 'Mensaje no encontrado'
            ], 404);
        }

        $mensaje->update([
            'estado' => $validated['estado'],
            'fecha_respuesta' => $validated['estado'] !== 'pendiente' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => [
                'id_mensaje' => $mensaje->id_mensaje,
                'estado' => $mensaje->estado,
                'fecha_respuesta' => $mensaje->fecha_respuesta,
            ]
        ]);
    }

    /**
     * Obtener estadísticas
     */
    public function estadisticas()
    {
        $total = Mensaje::count();
        $pendientes = Mensaje::where('estado', 'pendiente')->count();
        $en_proceso = Mensaje::where('estado', 'en_proceso')->count();
        $resueltos = Mensaje::where('estado', 'resuelto')->count();
        $cerrados = Mensaje::where('estado', 'cerrado')->count();

        $por_tipo = Mensaje::selectRaw('tipo, COUNT(*) as cantidad')
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo')
            ->map(function ($item) {
                return $item->cantidad;
            });

        $por_prioridad = Mensaje::selectRaw('prioridad, COUNT(*) as cantidad')
            ->groupBy('prioridad')
            ->get()
            ->keyBy('prioridad')
            ->map(function ($item) {
                return $item->cantidad;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'por_estado' => [
                    'pendiente' => $pendientes,
                    'en_proceso' => $en_proceso,
                    'resuelto' => $resueltos,
                    'cerrado' => $cerrados,
                ],
                'por_tipo' => $por_tipo,
                'por_prioridad' => $por_prioridad,
            ]
        ]);
    }
}

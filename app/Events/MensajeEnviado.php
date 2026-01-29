<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    public $broadcastQueue = null; // Ejecutar de forma síncrona

    /**
     * Create a new event instance.
     */
    public function __construct(Mensaje $mensaje)
    {
        $this->mensaje = $mensaje->load('usuario');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('mensajes'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id_mensaje' => $this->mensaje->id_mensaje,
            'id_usuario' => $this->mensaje->id_usuario,
            'usuario' => [
                'id_usuario' => $this->mensaje->usuario->id_usuario,
                'nombre' => $this->mensaje->usuario->nombre,
                'apellido' => $this->mensaje->usuario->apellido,
            ],
            'asunto' => $this->mensaje->asunto,
            'contenido' => $this->mensaje->contenido,
            'tipo' => $this->mensaje->tipo,
            'prioridad' => $this->mensaje->prioridad,
            'estado' => $this->mensaje->estado,
            'fecha_envio' => $this->mensaje->fecha_envio,
        ];
    }

    /**
     * Get the name of the event broadcast as.
     */
    public function broadcastAs(): string
    {
        return 'MensajeEnviado';
    }
}

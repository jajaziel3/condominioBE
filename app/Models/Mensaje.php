<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'asunto',
        'contenido',
        'tipo',
        'prioridad',
        'estado',
        'fecha_respuesta',
    ];

    /**
     * Relación: Un mensaje pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Obtener mensajes recientes
     */
    public static function getRecientes($limit = 50)
    {
        return static::with('usuario')
            ->orderBy('fecha_envio', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Obtener mensajes por estado
     */
    public static function porEstado($estado, $limit = 50)
    {
        return static::with('usuario')
            ->where('estado', $estado)
            ->orderBy('fecha_envio', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener mensajes con prioridad
     */
    public static function porPrioridad($prioridad, $limit = 50)
    {
        return static::with('usuario')
            ->where('prioridad', $prioridad)
            ->orderBy('fecha_envio', 'desc')
            ->limit($limit)
            ->get();
    }
}

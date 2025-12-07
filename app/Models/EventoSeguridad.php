<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoSeguridad extends Model
{
    use HasFactory;

    protected $table = 'eventos_seguridad';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null;

    protected $fillable = [
        'tipo_evento',
        'severidad',
        'ip_origen',
        'usuario_id',
        'ruta_solicitud',
        'metodo_http',
        'payload_muestra',
        'headers',
        'bloqueado',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'bloqueado' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Scopes
    public function scopeCriticos($query)
    {
        return $query->where('severidad', 'CRITICA');
    }

    public function scopeRecientes($query, int $horas = 24)
    {
        return $query->where('creado_en', '>=', now()->subHours($horas));
    }

    // Helper para registrar evento
    public static function registrar(
        string $tipoEvento,
        string $severidad = 'MEDIA',
        ?Usuario $usuario = null,
        bool $bloqueado = false
    ): self {
        return self::create([
            'tipo_evento' => $tipoEvento,
            'severidad' => $severidad,
            'ip_origen' => request()->ip(),
            'usuario_id' => $usuario?->id,
            'ruta_solicitud' => request()->path(),
            'metodo_http' => request()->method(),
            'headers' => request()->headers->all(),
            'bloqueado' => $bloqueado,
        ]);
    }
}

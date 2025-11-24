<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'denuncia_id',
        'tipo',
        'canal',
        'asunto',
        'mensaje',
        'datos_extra',
        'estado',
        'intentos',
        'max_intentos',
        'error_mensaje',
    ];

    protected function casts(): array
    {
        return [
            'datos_extra' => 'array',
            'intentos' => 'integer',
            'max_intentos' => 'integer',
            'enviada_en' => 'datetime',
            'leida_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeNoLeidas($query)
    {
        return $query->whereNull('leida_en');
    }

    // Helpers
    public function marcarComoLeida(): void
    {
        $this->update([
            'estado' => 'LEIDA',
            'leida_en' => now(),
        ]);
    }

    public function marcarComoEnviada(): void
    {
        $this->update([
            'estado' => 'ENVIADA',
            'enviada_en' => now(),
        ]);
    }
}

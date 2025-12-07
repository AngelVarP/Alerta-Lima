<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransicionEstado extends Model
{
    use HasFactory;

    protected $table = 'transiciones_estado';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'estado_origen_id',
        'estado_destino_id',
        'nombre',
        'requiere_motivo',
        'requiere_asignacion',
        'solo_admin',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_motivo' => 'boolean',
            'requiere_asignacion' => 'boolean',
            'solo_admin' => 'boolean',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function estadoOrigen()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_origen_id');
    }

    public function estadoDestino()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_destino_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}

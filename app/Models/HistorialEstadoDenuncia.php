<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoDenuncia extends Model
{
    use HasFactory;

    protected $table = 'historial_estados_denuncia';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null;

    protected $fillable = [
        'denuncia_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'cambiado_por_id',
        'motivo_cambio',
        'tiempo_en_estado',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    public function estadoAnterior()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_anterior_id');
    }

    public function estadoNuevo()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_nuevo_id');
    }

    public function cambiadoPor()
    {
        return $this->belongsTo(Usuario::class, 'cambiado_por_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrioridadDenuncia extends Model
{
    use HasFactory;

    protected $table = 'prioridades_denuncia';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'color',
        'sla_horas',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'sla_horas' => 'integer',
            'orden' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'prioridad_id');
    }

    // Scopes
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}

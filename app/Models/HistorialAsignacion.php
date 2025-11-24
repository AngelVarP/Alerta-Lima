<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialAsignacion extends Model
{
    use HasFactory;

    protected $table = 'historial_asignaciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'denuncia_id',
        'asignado_de_id',
        'asignado_a_id',
        'area_anterior_id',
        'area_nueva_id',
        'asignado_por_id',
        'motivo',
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

    public function asignadoDe()
    {
        return $this->belongsTo(Usuario::class, 'asignado_de_id');
    }

    public function asignadoA()
    {
        return $this->belongsTo(Usuario::class, 'asignado_a_id');
    }

    public function areaAnterior()
    {
        return $this->belongsTo(Area::class, 'area_anterior_id');
    }

    public function areaNueva()
    {
        return $this->belongsTo(Area::class, 'area_nueva_id');
    }

    public function asignadoPor()
    {
        return $this->belongsTo(Usuario::class, 'asignado_por_id');
    }
}

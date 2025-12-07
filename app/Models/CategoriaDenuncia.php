<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDenuncia extends Model
{
    use HasFactory;

    protected $table = 'categorias_denuncia';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color',
        'area_default_id',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function areaDefault()
    {
        return $this->belongsTo(Area::class, 'area_default_id');
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'categoria_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }
}

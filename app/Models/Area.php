<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'email_contacto',
        'telefono',
        'responsable_id',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'area_id');
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'area_id');
    }

    public function categorias()
    {
        return $this->hasMany(CategoriaDenuncia::class, 'area_default_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}

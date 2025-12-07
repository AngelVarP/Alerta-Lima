<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comentario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comentarios';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'denuncia_id',
        'usuario_id',
        'comentario_padre_id',
        'es_interno',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'es_interno' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function padre()
    {
        return $this->belongsTo(Comentario::class, 'comentario_padre_id');
    }

    public function respuestas()
    {
        return $this->hasMany(Comentario::class, 'comentario_padre_id');
    }

    // Scopes
    public function scopePublicos($query)
    {
        return $query->where('es_interno', false);
    }

    public function scopeInternos($query)
    {
        return $query->where('es_interno', true);
    }
}

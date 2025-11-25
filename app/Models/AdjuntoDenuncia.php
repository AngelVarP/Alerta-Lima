<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjuntoDenuncia extends Model
{
    use HasFactory;

    protected $table = 'adjuntos_denuncia';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'denuncia_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_mime',
        'tamano',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    // Accessor para URL pública del archivo
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->ruta_archivo);
    }
}

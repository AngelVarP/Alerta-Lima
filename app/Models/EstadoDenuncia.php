<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoDenuncia extends Model
{
    use HasFactory;

    protected $table = 'estados_denuncia';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'color',
        'es_inicial',
        'es_final',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'es_inicial' => 'boolean',
            'es_final' => 'boolean',
            'orden' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'estado_id');
    }

    public function transicionesOrigen()
    {
        return $this->hasMany(TransicionEstado::class, 'estado_origen_id');
    }

    public function transicionesDestino()
    {
        return $this->hasMany(TransicionEstado::class, 'estado_destino_id');
    }

    // Scopes
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }

    // Helpers
    public static function inicial()
    {
        return static::where('es_inicial', true)->first();
    }

    public function puedeTransicionarA(EstadoDenuncia $estadoDestino): bool
    {
        return $this->transicionesOrigen()
            ->where('estado_destino_id', $estadoDestino->id)
            ->where('activo', true)
            ->exists();
    }
}

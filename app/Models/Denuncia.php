<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Denuncia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'denuncias';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'codigo',
        'ciudadano_id',
        'asignado_a_id',
        'area_id',
        'categoria_id',
        'prioridad_id',
        'estado_id',
        'distrito_id',
        'titulo',
        'descripcion',
        'direccion',
        'referencia',
        'latitud',
        'longitud',
        'es_anonima',
        'ip_origen',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'es_anonima' => 'boolean',
            'registrada_en' => 'datetime',
            'fecha_limite_sla' => 'datetime',
            'cerrada_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function ciudadano()
    {
        return $this->belongsTo(Usuario::class, 'ciudadano_id');
    }

    public function asignadoA()
    {
        return $this->belongsTo(Usuario::class, 'asignado_a_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaDenuncia::class, 'categoria_id');
    }

    public function prioridad()
    {
        return $this->belongsTo(PrioridadDenuncia::class, 'prioridad_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_id');
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(AdjuntoDenuncia::class, 'denuncia_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'denuncia_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoDenuncia::class, 'denuncia_id');
    }

    public function historialAsignaciones()
    {
        return $this->hasMany(HistorialAsignacion::class, 'denuncia_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'denuncia_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->whereNull('eliminado_en');
    }

    public function scopePorEstado($query, string $codigoEstado)
    {
        return $query->whereHas('estado', fn($q) => $q->where('codigo', $codigoEstado));
    }

    public function scopeSlaPendiente($query)
    {
        return $query->whereNull('cerrada_en')
            ->where('fecha_limite_sla', '<', now());
    }

    // Helpers
    public function estaAbierta(): bool
    {
        return $this->cerrada_en === null;
    }

    public function slaVencido(): bool
    {
        return $this->estaAbierta() && $this->fecha_limite_sla < now();
    }

    public function cambiarEstado(EstadoDenuncia $nuevoEstado, Usuario $usuario, ?string $motivo = null): void
    {
        $estadoAnterior = $this->estado;

        HistorialEstadoDenuncia::create([
            'denuncia_id' => $this->id,
            'estado_anterior_id' => $estadoAnterior?->id,
            'estado_nuevo_id' => $nuevoEstado->id,
            'cambiado_por_id' => $usuario->id,
            'motivo_cambio' => $motivo,
        ]);

        $this->estado_id = $nuevoEstado->id;

        if ($nuevoEstado->es_final) {
            $this->cerrada_en = now();
        }

        $this->save();
    }
}

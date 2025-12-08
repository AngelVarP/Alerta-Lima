<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Denuncia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'denuncias';

    // CORRECCIÓN: Ya no necesitamos estas constantes
    // const CREATED_AT = 'creado_en';
    // const UPDATED_AT = 'actualizado_en';
    // const DELETED_AT = 'eliminado_en';

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
        'descripcion', // CORRECCIÓN: Sin cifrar para permitir búsquedas
        'direccion',
        'referencia',
        'latitud',
        'longitud',
        'es_anonima',
        'ip_origen',
        'user_agent',
        'registrada_en',
        'fecha_limite_sla',
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
        return $this->hasMany(Adjunto::class, 'denuncia_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'denuncia_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoDenuncia::class, 'denuncia_id')
            ->orderBy('created_at', 'desc');
    }

    public function historialAsignaciones()
    {
        return $this->hasMany(HistorialAsignacion::class, 'denuncia_id')
            ->orderBy('created_at', 'desc');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'denuncia_id');
    }

    // Scopes para consultas comunes
    public function scopeActivas($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePorEstado($query, string $codigoEstado)
    {
        return $query->whereHas('estado', fn ($q) => $q->where('codigo', $codigoEstado));
    }

    public function scopeSlaPendiente($query)
    {
        return $query->whereNull('cerrada_en')
            ->where('fecha_limite_sla', '<', now());
    }

    public function scopeSlaProximo($query, int $horas = 24)
    {
        return $query->whereNull('cerrada_en')
            ->whereBetween('fecha_limite_sla', [now(), now()->addHours($horas)]);
    }

    // Helpers
    public function estaAbierta(): bool
    {
        return $this->cerrada_en === null;
    }

    public function estaCerrada(): bool
    {
        return ! $this->estaAbierta();
    }

    public function slaVencido(): bool
    {
        return $this->estaAbierta() && $this->fecha_limite_sla < now();
    }

    public function porcentajeSlaConsumido(): int
    {
        if ($this->estaCerrada()) {
            return 100;
        }

        $inicio = $this->registrada_en;
        $fin = $this->fecha_limite_sla;
        $ahora = now();

        if ($ahora->greaterThan($fin)) {
            return 100;
        }

        $total = $inicio->diffInSeconds($fin);
        $transcurrido = $inicio->diffInSeconds($ahora);

        return (int) (($transcurrido / $total) * 100);
    }

    /**
     * Cambiar el estado de la denuncia
     * CORRECCIÓN: Lógica de negocio en el modelo, no en triggers
     */
    public function cambiarEstado(EstadoDenuncia $nuevoEstado, Usuario $usuario, ?string $motivo = null): void
    {
        $estadoAnterior = $this->estado;

        // Calcular tiempo en estado anterior
        $ultimoCambio = $this->historialEstados()->first();
        $tiempoEnEstado = $ultimoCambio
            ? $ultimoCambio->created_at->diff(now())->format('%d days %h hours %i minutes')
            : null;

        // Crear registro en historial
        HistorialEstadoDenuncia::create([
            'denuncia_id' => $this->id,
            'estado_anterior_id' => $estadoAnterior?->id,
            'estado_nuevo_id' => $nuevoEstado->id,
            'cambiado_por_id' => $usuario->id,
            'motivo_cambio' => $motivo,
            'tiempo_en_estado' => $tiempoEnEstado,
        ]);

        // Actualizar estado
        $this->estado_id = $nuevoEstado->id;

        // Si es estado final, marcar como cerrada (Observer también lo hace)
        if ($nuevoEstado->es_final && ! $this->cerrada_en) {
            $this->cerrada_en = now();
        }

        $this->save();
    }

    /**
     * Asignar la denuncia a un funcionario
     */
    public function asignarA(Usuario $funcionario, Usuario $asignadoPor, ?string $motivo = null): void
    {
        $asignadoAnterior = $this->asignadoA;
        $areaAnterior = $this->area;

        // Crear registro en historial de asignaciones
        HistorialAsignacion::create([
            'denuncia_id' => $this->id,
            'asignado_de_id' => $asignadoAnterior?->id,
            'asignado_a_id' => $funcionario->id,
            'area_anterior_id' => $areaAnterior?->id,
            'area_nueva_id' => $funcionario->area_id,
            'asignado_por_id' => $asignadoPor->id,
            'motivo' => $motivo,
        ]);

        // Actualizar asignación
        $this->asignado_a_id = $funcionario->id;
        $this->area_id = $funcionario->area_id;
        $this->save();
    }
}

<?php

namespace App\Observers;

use App\Models\Denuncia;
use App\Models\PrioridadDenuncia;
use App\Models\CategoriaDenuncia;
use App\Models\HistorialEstadoDenuncia;
use Carbon\Carbon;

/**
 * Observer de Denuncia
 *
 * CORRECCIÓN CRÍTICA: Mueve la lógica de negocio de los triggers de PostgreSQL a Laravel.
 * - Genera código único
 * - Calcula fecha SLA
 * - Asigna área por defecto según categoría
 * - Registra cambios de estado automáticamente
 */
class DenunciaObserver
{
    /**
     * Handle the Denuncia "creating" event.
     * Se ejecuta ANTES de insertar en la BD
     */
    public function creating(Denuncia $denuncia): void
    {
        // 1. Generar código único: DEN-YYYY-NNNNNN (reemplaza trigger)
        if (empty($denuncia->codigo)) {
            $denuncia->codigo = $this->generarCodigoUnico();
        }

        // 2. Asignar área por defecto según categoría (reemplaza trigger)
        if (empty($denuncia->area_id) && !empty($denuncia->categoria_id)) {
            $categoria = CategoriaDenuncia::find($denuncia->categoria_id);
            $denuncia->area_id = $categoria?->area_default_id;
        }

        // 3. Calcular fecha límite SLA (reemplaza trigger)
        if (empty($denuncia->fecha_limite_sla) && !empty($denuncia->prioridad_id)) {
            $prioridad = PrioridadDenuncia::find($denuncia->prioridad_id);
            $registradaEn = $denuncia->registrada_en ?? now();

            $horasSla = $prioridad?->sla_horas ?? 72; // Default 72h
            $denuncia->fecha_limite_sla = Carbon::parse($registradaEn)->addHours($horasSla);
        }

        // 4. Asegurar fecha de registro
        if (empty($denuncia->registrada_en)) {
            $denuncia->registrada_en = now();
        }
    }

    /**
     * Handle the Denuncia "created" event.
     * Se ejecuta DESPUÉS de insertar en la BD
     */
    public function created(Denuncia $denuncia): void
    {
        // Registrar el estado inicial en el historial
        if ($denuncia->estado_id) {
            HistorialEstadoDenuncia::create([
                'denuncia_id' => $denuncia->id,
                'estado_anterior_id' => null,
                'estado_nuevo_id' => $denuncia->estado_id,
                'cambiado_por_id' => $denuncia->ciudadano_id,
                'motivo_cambio' => 'Denuncia registrada inicialmente',
            ]);
        }
    }

    /**
     * Handle the Denuncia "updating" event.
     */
    public function updating(Denuncia $denuncia): void
    {
        // Si cambia a un estado final, marcar fecha de cierre (reemplaza trigger)
        if ($denuncia->isDirty('estado_id')) {
            $nuevoEstado = \App\Models\EstadoDenuncia::find($denuncia->estado_id);

            if ($nuevoEstado?->es_final && empty($denuncia->cerrada_en)) {
                $denuncia->cerrada_en = now();
            }
        }
    }

    /**
     * Genera un código único para la denuncia
     * Formato: DEN-YYYY-NNNNNN
     */
    private function generarCodigoUnico(): string
    {
        $anio = now()->year;

        // Obtener el último código del año actual
        $ultimaDenuncia = Denuncia::whereYear('created_at', $anio)
            ->orderBy('id', 'desc')
            ->first();

        $secuencia = 1;

        if ($ultimaDenuncia && preg_match('/DEN-\d{4}-(\d{6})/', $ultimaDenuncia->codigo, $matches)) {
            $secuencia = intval($matches[1]) + 1;
        }

        return sprintf('DEN-%d-%06d', $anio, $secuencia);
    }
}

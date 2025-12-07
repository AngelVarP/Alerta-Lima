<?php

namespace App\Services;

use App\Models\Denuncia;
use App\Models\PrioridadDenuncia;
use Carbon\Carbon;

class SlaService
{
    /**
     * Calcular fecha límite SLA para una denuncia
     */
    public function calcularFechaLimiteSLA(Denuncia $denuncia): ?Carbon
    {
        if (! $denuncia->prioridad || ! $denuncia->prioridad->sla_horas) {
            return null;
        }

        $fechaInicio = $denuncia->registrada_en ?? $denuncia->creado_en;

        return $fechaInicio->copy()->addHours($denuncia->prioridad->sla_horas);
    }

    /**
     * Verificar si el SLA está vencido
     */
    public function estaVencido(Denuncia $denuncia): bool
    {
        if (! $denuncia->fecha_limite_sla || $denuncia->cerrada_en) {
            return false;
        }

        return now()->greaterThan($denuncia->fecha_limite_sla);
    }

    /**
     * Verificar si el SLA está próximo a vencer (menos de 24 horas)
     */
    public function estaProximoVencer(Denuncia $denuncia): bool
    {
        if (! $denuncia->fecha_limite_sla || $denuncia->cerrada_en) {
            return false;
        }

        $horasRestantes = now()->diffInHours($denuncia->fecha_limite_sla, false);

        return $horasRestantes > 0 && $horasRestantes <= 24;
    }

    /**
     * Obtener horas restantes hasta el vencimiento del SLA
     */
    public function horasRestantes(Denuncia $denuncia): ?int
    {
        if (! $denuncia->fecha_limite_sla || $denuncia->cerrada_en) {
            return null;
        }

        $horasRestantes = now()->diffInHours($denuncia->fecha_limite_sla, false);

        return max(0, $horasRestantes);
    }

    /**
     * Obtener porcentaje de SLA transcurrido
     */
    public function porcentajeTranscurrido(Denuncia $denuncia): ?float
    {
        if (! $denuncia->fecha_limite_sla || ! $denuncia->prioridad) {
            return null;
        }

        $fechaInicio = $denuncia->registrada_en ?? $denuncia->creado_en;
        $horasTotales = $denuncia->prioridad->sla_horas;
        $horasTranscurridas = $fechaInicio->diffInHours(now());

        $porcentaje = ($horasTranscurridas / $horasTotales) * 100;

        return min(100, max(0, $porcentaje));
    }

    /**
     * Verificar si el SLA fue cumplido (denuncia cerrada)
     */
    public function fueCumplido(Denuncia $denuncia): ?bool
    {
        if (! $denuncia->cerrada_en || ! $denuncia->fecha_limite_sla) {
            return null;
        }

        return $denuncia->cerrada_en->lessThanOrEqualTo($denuncia->fecha_limite_sla);
    }

    /**
     * Obtener tiempo de resolución en horas
     */
    public function tiempoResolucion(Denuncia $denuncia): ?int
    {
        if (! $denuncia->cerrada_en) {
            return null;
        }

        $fechaInicio = $denuncia->registrada_en ?? $denuncia->creado_en;

        return $fechaInicio->diffInHours($denuncia->cerrada_en);
    }

    /**
     * Obtener denuncias con SLA vencido
     */
    public function obtenerDenunciasConSLAVencido(?int $areaId = null)
    {
        $query = Denuncia::whereNull('cerrada_en')
            ->whereNotNull('fecha_limite_sla')
            ->where('fecha_limite_sla', '<', now());

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        return $query->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'asignadoA'])
            ->orderBy('fecha_limite_sla', 'asc')
            ->get();
    }

    /**
     * Obtener denuncias con SLA próximo a vencer
     */
    public function obtenerDenunciasConSLAProximoVencer(?int $areaId = null)
    {
        $limiteInferior = now();
        $limiteSuperior = now()->addHours(24);

        $query = Denuncia::whereNull('cerrada_en')
            ->whereNotNull('fecha_limite_sla')
            ->whereBetween('fecha_limite_sla', [$limiteInferior, $limiteSuperior]);

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        return $query->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'asignadoA'])
            ->orderBy('fecha_limite_sla', 'asc')
            ->get();
    }

    /**
     * Calcular porcentaje de cumplimiento de SLA para un área
     */
    public function porcentajeCumplimientoArea(int $areaId, ?Carbon $fechaInicio = null, ?Carbon $fechaFin = null): float
    {
        $fechaInicio = $fechaInicio ?? now()->subMonth();
        $fechaFin = $fechaFin ?? now();

        $denunciasCerradas = Denuncia::where('area_id', $areaId)
            ->whereNotNull('cerrada_en')
            ->whereBetween('cerrada_en', [$fechaInicio, $fechaFin])
            ->count();

        if ($denunciasCerradas === 0) {
            return 0;
        }

        $slaCumplido = Denuncia::where('area_id', $areaId)
            ->whereNotNull('cerrada_en')
            ->whereBetween('cerrada_en', [$fechaInicio, $fechaFin])
            ->whereRaw('cerrada_en <= fecha_limite_sla')
            ->count();

        return round(($slaCumplido / $denunciasCerradas) * 100, 2);
    }

    /**
     * Actualizar fecha límite SLA al cambiar prioridad
     */
    public function actualizarSLAPorCambioPrioridad(Denuncia $denuncia, PrioridadDenuncia $nuevaPrioridad): void
    {
        if ($nuevaPrioridad->sla_horas) {
            $fechaInicio = $denuncia->registrada_en ?? $denuncia->creado_en;
            $denuncia->update([
                'fecha_limite_sla' => $fechaInicio->copy()->addHours($nuevaPrioridad->sla_horas),
            ]);
        }
    }
}

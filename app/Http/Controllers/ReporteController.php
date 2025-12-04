<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Area;
use App\Models\EstadoDenuncia;
use App\Models\CategoriaDenuncia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReporteController extends Controller
{
    /**
     * Vista principal de reportes
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Rango de fechas por defecto (último mes)
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Filtros
        $areaId = $usuario->esAdmin() ? $request->input('area_id') : $usuario->area_id;

        // Estadísticas generales
        $estadisticas = $this->obtenerEstadisticas($fechaInicio, $fechaFin, $areaId);

        // Áreas (solo para admin)
        $areas = $usuario->esAdmin() ? Area::activas()->get(['id', 'nombre']) : null;

        return Inertia::render('Admin/Reportes/Index', [
            'estadisticas' => $estadisticas,
            'areas' => $areas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'area_id' => $areaId,
            ],
        ]);
    }

    /**
     * Exportar reporte en formato CSV
     */
    public function exportarCSV(Request $request)
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'area_id' => 'nullable|exists:areas,id',
            'estado_id' => 'nullable|exists:estados_denuncia,id',
            'categoria_id' => 'nullable|exists:categorias_denuncia,id',
        ]);

        // Construir query
        $query = Denuncia::with(['ciudadano', 'estado', 'categoria', 'prioridad', 'distrito', 'area', 'asignadoA'])
            ->whereBetween('creado_en', [$validated['fecha_inicio'], $validated['fecha_fin']]);

        // Filtrar por área si no es admin
        if (!$usuario->esAdmin()) {
            $query->where('area_id', $usuario->area_id);
        } elseif (isset($validated['area_id'])) {
            $query->where('area_id', $validated['area_id']);
        }

        if (isset($validated['estado_id'])) {
            $query->where('estado_id', $validated['estado_id']);
        }

        if (isset($validated['categoria_id'])) {
            $query->where('categoria_id', $validated['categoria_id']);
        }

        $denuncias = $query->get();

        // Generar CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_denuncias_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($denuncias) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'Código',
                'Fecha Registro',
                'Ciudadano',
                'Email',
                'DNI',
                'Categoría',
                'Estado',
                'Prioridad',
                'Área',
                'Asignado A',
                'Distrito',
                'Dirección',
                'Título',
                'Descripción',
                'Fecha Cierre',
                'Tiempo Resolución (horas)',
                'SLA Cumplido',
            ]);

            // Datos
            foreach ($denuncias as $denuncia) {
                $tiempoResolucion = $denuncia->cerrada_en
                    ? $denuncia->creado_en->diffInHours($denuncia->cerrada_en)
                    : null;

                $slaCumplido = $denuncia->cerrada_en
                    ? ($denuncia->cerrada_en <= $denuncia->fecha_limite_sla ? 'Sí' : 'No')
                    : 'Pendiente';

                fputcsv($file, [
                    $denuncia->codigo,
                    $denuncia->creado_en->format('Y-m-d H:i:s'),
                    $denuncia->ciudadano ? $denuncia->ciudadano->nombre_completo : 'Anónimo',
                    $denuncia->ciudadano?->email ?? '',
                    $denuncia->ciudadano?->dni ?? '',
                    $denuncia->categoria->nombre,
                    $denuncia->estado->nombre,
                    $denuncia->prioridad?->nombre ?? '',
                    $denuncia->area?->nombre ?? 'Sin asignar',
                    $denuncia->asignadoA ? $denuncia->asignadoA->nombre_completo : 'Sin asignar',
                    $denuncia->distrito->nombre,
                    $denuncia->direccion,
                    $denuncia->titulo,
                    $denuncia->descripcion,
                    $denuncia->cerrada_en?->format('Y-m-d H:i:s') ?? '',
                    $tiempoResolucion ?? '',
                    $slaCumplido,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generar reporte PDF
     */
    public function exportarPDF(Request $request)
    {
        // Nota: Requiere instalar una librería como barryvdh/laravel-dompdf
        // composer require barryvdh/laravel-dompdf

        $usuario = $request->user();

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $areaId = $usuario->esAdmin() && isset($validated['area_id'])
            ? $validated['area_id']
            : $usuario->area_id;

        $estadisticas = $this->obtenerEstadisticas(
            $validated['fecha_inicio'],
            $validated['fecha_fin'],
            $areaId
        );

        $area = $areaId ? Area::find($areaId) : null;

        // Retornar vista para generar PDF
        // Esto requeriría configurar dompdf, por ahora retornamos JSON
        return response()->json([
            'mensaje' => 'Exportación a PDF disponible instalando barryvdh/laravel-dompdf',
            'estadisticas' => $estadisticas,
            'area' => $area,
            'rango' => [
                'inicio' => $validated['fecha_inicio'],
                'fin' => $validated['fecha_fin'],
            ],
        ]);
    }

    /**
     * Reporte de rendimiento por funcionario
     */
    public function rendimientoFuncionarios(Request $request)
    {
        $usuario = $request->user();

        if (!$usuario->esAdmin() && !$usuario->tieneRol('supervisor')) {
            abort(403, 'No tienes permiso para ver este reporte.');
        }

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $areaId = $usuario->esAdmin() && isset($validated['area_id'])
            ? $validated['area_id']
            : $usuario->area_id;

        $rendimiento = DB::table('usuarios')
            ->select(
                'usuarios.id',
                'usuarios.nombre',
                'usuarios.apellido',
                DB::raw('COUNT(DISTINCT denuncias.id) as total_asignadas'),
                DB::raw('COUNT(DISTINCT CASE WHEN denuncias.cerrada_en IS NOT NULL THEN denuncias.id END) as total_cerradas'),
                DB::raw('AVG(CASE
                    WHEN denuncias.cerrada_en IS NOT NULL
                    THEN EXTRACT(EPOCH FROM (denuncias.cerrada_en - denuncias.registrada_en)) / 3600
                    END) as promedio_horas_resolucion'),
                DB::raw('COUNT(DISTINCT CASE
                    WHEN denuncias.cerrada_en IS NOT NULL
                    AND denuncias.cerrada_en <= denuncias.fecha_limite_sla
                    THEN denuncias.id
                    END) as sla_cumplido')
            )
            ->join('denuncias', 'usuarios.id', '=', 'denuncias.asignado_a_id')
            ->whereBetween('denuncias.creado_en', [$validated['fecha_inicio'], $validated['fecha_fin']])
            ->where('usuarios.area_id', $areaId)
            ->groupBy('usuarios.id', 'usuarios.nombre', 'usuarios.apellido')
            ->orderBy('total_cerradas', 'desc')
            ->get();

        return response()->json([
            'rendimiento' => $rendimiento,
            'area_id' => $areaId,
            'rango' => [
                'inicio' => $validated['fecha_inicio'],
                'fin' => $validated['fecha_fin'],
            ],
        ]);
    }

    /**
     * Reporte de SLA
     */
    public function reporteSLA(Request $request)
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $areaId = $usuario->esAdmin() && isset($validated['area_id'])
            ? $validated['area_id']
            : $usuario->area_id;

        $query = Denuncia::whereBetween('creado_en', [$validated['fecha_inicio'], $validated['fecha_fin']]);

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        $total = $query->count();
        $cerradas = (clone $query)->whereNotNull('cerrada_en')->count();
        $slaCumplido = (clone $query)
            ->whereNotNull('cerrada_en')
            ->whereRaw('cerrada_en <= fecha_limite_sla')
            ->count();
        $slaVencido = (clone $query)
            ->whereNotNull('cerrada_en')
            ->whereRaw('cerrada_en > fecha_limite_sla')
            ->count();
        $slaEnRiesgo = (clone $query)
            ->whereNull('cerrada_en')
            ->slaPendiente()
            ->count();

        $porcentajeCumplimiento = $cerradas > 0 ? round(($slaCumplido / $cerradas) * 100, 2) : 0;

        return response()->json([
            'total' => $total,
            'cerradas' => $cerradas,
            'sla_cumplido' => $slaCumplido,
            'sla_vencido' => $slaVencido,
            'sla_en_riesgo' => $slaEnRiesgo,
            'porcentaje_cumplimiento' => $porcentajeCumplimiento,
        ]);
    }

    /**
     * Obtener estadísticas generales
     */
    private function obtenerEstadisticas($fechaInicio, $fechaFin, $areaId = null): array
    {
        $query = Denuncia::whereBetween('creado_en', [$fechaInicio, $fechaFin]);

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        $total = $query->count();
        $cerradas = (clone $query)->whereNotNull('cerrada_en')->count();
        $enProceso = (clone $query)->whereNull('cerrada_en')->count();

        // Por estado
        $porEstado = EstadoDenuncia::withCount([
            'denuncias' => function ($q) use ($fechaInicio, $fechaFin, $areaId) {
                $q->whereBetween('creado_en', [$fechaInicio, $fechaFin]);
                if ($areaId) {
                    $q->where('area_id', $areaId);
                }
            }
        ])->get();

        // Por categoría
        $porCategoria = CategoriaDenuncia::withCount([
            'denuncias' => function ($q) use ($fechaInicio, $fechaFin, $areaId) {
                $q->whereBetween('creado_en', [$fechaInicio, $fechaFin]);
                if ($areaId) {
                    $q->where('area_id', $areaId);
                }
            }
        ])->activas()->get();

        // Por área (solo si es admin y no filtró por área)
        $porArea = !$areaId ? Area::withCount([
            'denuncias' => fn($q) => $q->whereBetween('creado_en', [$fechaInicio, $fechaFin])
        ])->activas()->get() : null;

        return [
            'total' => $total,
            'cerradas' => $cerradas,
            'en_proceso' => $enProceso,
            'por_estado' => $porEstado,
            'por_categoria' => $porCategoria,
            'por_area' => $porArea,
        ];
    }
}

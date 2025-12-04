<?php

namespace App\Http\Controllers;

use App\Models\RegistroAuditoria;
use App\Models\EventoSeguridad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    /**
     * Lista de registros de auditoría
     */
    public function index(Request $request)
    {
        // Solo admin puede ver auditoría
        if (!$request->user()->esAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $query = RegistroAuditoria::with(['usuario']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('accion', 'like', "%{$search}%")
                    ->orWhere('tabla_afectada', 'like', "%{$search}%")
                    ->orWhere('registro_id', 'like', "%{$search}%")
                    ->orWhereHas('usuario', fn($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->input('tabla'));
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('creado_en', '>=', $request->input('fecha_inicio'));
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('creado_en', '<=', $request->input('fecha_fin'));
        }

        $registros = $query->orderBy('creado_en', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Datos para filtros
        $acciones = RegistroAuditoria::select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $tablas = RegistroAuditoria::select('tabla_afectada')
            ->distinct()
            ->orderBy('tabla_afectada')
            ->pluck('tabla_afectada');

        $usuarios = Usuario::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Admin/Auditoria/Index', [
            'registros' => $registros,
            'filtros' => $request->only(['search', 'accion', 'tabla', 'usuario_id', 'fecha_inicio', 'fecha_fin']),
            'acciones' => $acciones,
            'tablas' => $tablas,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Ver detalle de un registro de auditoría
     */
    public function show(RegistroAuditoria $registro)
    {
        if (!request()->user()->esAdmin()) {
            abort(403);
        }

        $registro->load(['usuario']);

        return Inertia::render('Admin/Auditoria/Show', [
            'registro' => $registro,
        ]);
    }

    /**
     * Lista de eventos de seguridad
     */
    public function eventosSeguridad(Request $request)
    {
        if (!$request->user()->esAdmin()) {
            abort(403);
        }

        $query = EventoSeguridad::with(['usuario']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('tipo_evento', 'like', "%{$search}%")
                    ->orWhere('ip_origen', 'like', "%{$search}%")
                    ->orWhereHas('usuario', fn($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('tipo_evento')) {
            $query->where('tipo_evento', $request->input('tipo_evento'));
        }

        if ($request->filled('severidad')) {
            $query->where('severidad', $request->input('severidad'));
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('creado_en', '>=', $request->input('fecha_inicio'));
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('creado_en', '<=', $request->input('fecha_fin'));
        }

        $eventos = $query->orderBy('creado_en', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Datos para filtros
        $tiposEvento = EventoSeguridad::select('tipo_evento')
            ->distinct()
            ->orderBy('tipo_evento')
            ->pluck('tipo_evento');

        $severidades = ['BAJA', 'MEDIA', 'ALTA', 'CRITICA'];

        return Inertia::render('Admin/Seguridad/Index', [
            'eventos' => $eventos,
            'filtros' => $request->only(['search', 'tipo_evento', 'severidad', 'fecha_inicio', 'fecha_fin']),
            'tiposEvento' => $tiposEvento,
            'severidades' => $severidades,
        ]);
    }

    /**
     * Ver detalle de un evento de seguridad
     */
    public function showEventoSeguridad(EventoSeguridad $evento)
    {
        if (!request()->user()->esAdmin()) {
            abort(403);
        }

        $evento->load(['usuario']);

        return Inertia::render('Admin/Seguridad/Show', [
            'evento' => $evento,
        ]);
    }

    /**
     * Estadísticas de auditoría
     */
    public function estadisticas(Request $request)
    {
        if (!$request->user()->esAdmin()) {
            abort(403);
        }

        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Acciones más comunes
        $accionesMasComunes = RegistroAuditoria::whereBetween('creado_en', [$fechaInicio, $fechaFin])
            ->select('accion', \DB::raw('count(*) as total'))
            ->groupBy('accion')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Usuarios más activos
        $usuariosMasActivos = RegistroAuditoria::whereBetween('creado_en', [$fechaInicio, $fechaFin])
            ->with('usuario')
            ->select('usuario_id', \DB::raw('count(*) as total'))
            ->groupBy('usuario_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Eventos de seguridad por severidad
        $eventosPorSeveridad = EventoSeguridad::whereBetween('creado_en', [$fechaInicio, $fechaFin])
            ->select('severidad', \DB::raw('count(*) as total'))
            ->groupBy('severidad')
            ->get();

        // Actividad por día
        $actividadPorDia = RegistroAuditoria::whereBetween('creado_en', [$fechaInicio, $fechaFin])
            ->select(\DB::raw('DATE(creado_en) as fecha'), \DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'acciones_mas_comunes' => $accionesMasComunes,
            'usuarios_mas_activos' => $usuariosMasActivos,
            'eventos_por_severidad' => $eventosPorSeveridad,
            'actividad_por_dia' => $actividadPorDia,
            'rango' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
        ]);
    }
}

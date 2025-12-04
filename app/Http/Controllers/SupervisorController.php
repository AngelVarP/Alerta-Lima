<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Area;
use App\Models\Usuario;
use App\Models\EstadoDenuncia;
use App\Models\CategoriaDenuncia;
use App\Models\PrioridadDenuncia;
use App\Models\HistorialAsignacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    /**
     * Dashboard del supervisor
     */
    public function dashboard(Request $request)
    {
        $usuario = $request->user();

        // Estadísticas generales del área
        $stats = [
            'total_area' => Denuncia::where('area_id', $usuario->area_id)->count(),
            'sin_asignar' => Denuncia::where('area_id', $usuario->area_id)
                ->whereNull('asignado_a_id')
                ->count(),
            'en_proceso' => Denuncia::where('area_id', $usuario->area_id)
                ->whereHas('estado', fn($q) => $q->where('codigo', 'PRO'))
                ->count(),
            'sla_vencido' => Denuncia::where('area_id', $usuario->area_id)
                ->slaPendiente()
                ->count(),
            'cerradas_mes' => Denuncia::where('area_id', $usuario->area_id)
                ->whereHas('estado', fn($q) => $q->where('es_final', true))
                ->whereMonth('cerrada_en', now()->month)
                ->count(),
        ];

        // Denuncias sin asignar (prioridad alta primero)
        $denunciasSinAsignar = Denuncia::where('area_id', $usuario->area_id)
            ->whereNull('asignado_a_id')
            ->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'distrito'])
            ->orderByRaw("
                CASE
                    WHEN prioridad_id IN (SELECT id FROM prioridades_denuncia WHERE codigo = 'ALT') THEN 1
                    WHEN prioridad_id IN (SELECT id FROM prioridades_denuncia WHERE codigo = 'MED') THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('creado_en', 'asc')
            ->limit(10)
            ->get();

        // Rendimiento del equipo
        $rendimientoEquipo = Usuario::where('area_id', $usuario->area_id)
            ->where('activo', true)
            ->whereHas('roles', fn($q) => $q->whereIn('nombre', ['funcionario', 'supervisor']))
            ->withCount([
                'denunciasAsignadas as asignadas_activas' => fn($q) => $q->whereNull('cerrada_en'),
                'denunciasAsignadas as cerradas_mes' => fn($q) => $q->whereMonth('cerrada_en', now()->month),
            ])
            ->get(['id', 'nombre', 'apellido']);

        // Denuncias con SLA crítico
        $denunciasSlaCritico = Denuncia::where('area_id', $usuario->area_id)
            ->slaPendiente()
            ->with(['asignadoA', 'estado', 'categoria', 'prioridad'])
            ->orderBy('fecha_limite_sla', 'asc')
            ->limit(5)
            ->get();

        return Inertia::render('Supervisor/Dashboard', [
            'stats' => $stats,
            'denunciasSinAsignar' => $denunciasSinAsignar,
            'rendimientoEquipo' => $rendimientoEquipo,
            'denunciasSlaCritico' => $denunciasSlaCritico,
        ]);
    }

    /**
     * Lista de denuncias del área (vista supervisor)
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        $query = Denuncia::where('area_id', $usuario->area_id)
            ->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'distrito', 'asignadoA']);

        // Filtros (similares a FuncionarioController pero con más opciones)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('titulo', 'like', "%{$search}%")
                    ->orWhereHas('ciudadano', fn($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->input('estado_id'));
        }

        if ($request->filled('asignado_a')) {
            if ($request->input('asignado_a') === 'sin_asignar') {
                $query->whereNull('asignado_a_id');
            } else {
                $query->where('asignado_a_id', $request->input('asignado_a'));
            }
        }

        $query->orderBy('creado_en', 'desc');
        $denuncias = $query->paginate(20)->withQueryString();

        // Datos para filtros
        $estados = EstadoDenuncia::all(['id', 'nombre', 'codigo', 'color']);
        $funcionarios = Usuario::where('area_id', $usuario->area_id)
            ->whereHas('roles', fn($q) => $q->whereIn('nombre', ['funcionario', 'supervisor']))
            ->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Supervisor/Denuncias/Index', [
            'denuncias' => $denuncias,
            'filtros' => $request->only(['search', 'estado_id', 'asignado_a']),
            'estados' => $estados,
            'funcionarios' => $funcionarios,
        ]);
    }

    /**
     * Ver detalle de denuncia (supervisor tiene acceso completo)
     */
    public function show(Denuncia $denuncia)
    {
        $this->authorize('verComoSupervisor', $denuncia);

        $denuncia->load([
            'ciudadano',
            'asignadoA',
            'area',
            'categoria',
            'estado',
            'prioridad',
            'distrito',
            'adjuntos',
            'comentarios.usuario',
            'historialEstados.estadoAnterior',
            'historialEstados.estadoNuevo',
            'historialEstados.cambiadoPor',
            'historialAsignaciones.asignadoA',
            'historialAsignaciones.asignadoPor',
        ]);

        // Funcionarios disponibles para asignación/reasignación
        $funcionariosArea = Usuario::where('area_id', $denuncia->area_id)
            ->where('activo', true)
            ->whereHas('roles', fn($q) => $q->whereIn('nombre', ['funcionario', 'supervisor']))
            ->get(['id', 'nombre', 'apellido']);

        // Estados disponibles
        $estados = EstadoDenuncia::where('es_final', false)->get(['id', 'nombre', 'codigo', 'color']);

        // Prioridades disponibles
        $prioridades = PrioridadDenuncia::all(['id', 'nombre', 'codigo', 'color']);

        return Inertia::render('Supervisor/Denuncias/Show', [
            'denuncia' => $denuncia,
            'funcionariosArea' => $funcionariosArea,
            'estados' => $estados,
            'prioridades' => $prioridades,
        ]);
    }

    /**
     * Asignar denuncia a un funcionario
     */
    public function asignar(Request $request, Denuncia $denuncia)
    {
        $this->authorize('asignar', $denuncia);

        $validated = $request->validate([
            'funcionario_id' => 'required|exists:usuarios,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        // Verificar que el funcionario pertenece al área
        $funcionario = Usuario::findOrFail($validated['funcionario_id']);
        if ($funcionario->area_id !== $denuncia->area_id) {
            return back()->with('error', 'El funcionario no pertenece al área de la denuncia.');
        }

        DB::transaction(function () use ($denuncia, $funcionario, $validated, $request) {
            $denuncia->update(['asignado_a_id' => $funcionario->id]);

            // Registrar historial
            HistorialAsignacion::create([
                'denuncia_id' => $denuncia->id,
                'asignado_a_id' => $funcionario->id,
                'asignado_por_id' => $request->user()->id,
                'motivo' => $validated['motivo'] ?? 'Asignación por supervisor',
            ]);

            // Cambiar a "En Proceso" si está en "Registrada"
            if ($denuncia->estado->codigo === 'REG') {
                $estadoEnProceso = EstadoDenuncia::where('codigo', 'PRO')->first();
                if ($estadoEnProceso) {
                    $denuncia->cambiarEstado($estadoEnProceso, $request->user(), 'Asignación inicial');
                }
            }

            // Notificar al funcionario asignado
            \App\Models\Notificacion::create([
                'usuario_id' => $funcionario->id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'ASIGNACION',
                'titulo' => 'Nueva denuncia asignada',
                'mensaje' => "Se te ha asignado la denuncia {$denuncia->codigo}",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        });

        return back()->with('success', 'Denuncia asignada correctamente.');
    }

    /**
     * Reasignar denuncia a otro funcionario
     */
    public function reasignar(Request $request, Denuncia $denuncia)
    {
        $this->authorize('asignar', $denuncia);

        $validated = $request->validate([
            'funcionario_id' => 'required|exists:usuarios,id',
            'motivo' => 'required|string|max:500',
        ]);

        $funcionario = Usuario::findOrFail($validated['funcionario_id']);

        if ($funcionario->area_id !== $denuncia->area_id) {
            return back()->with('error', 'El funcionario no pertenece al área de la denuncia.');
        }

        DB::transaction(function () use ($denuncia, $funcionario, $validated, $request) {
            $funcionarioAnterior = $denuncia->asignadoA;

            $denuncia->update(['asignado_a_id' => $funcionario->id]);

            // Registrar historial
            HistorialAsignacion::create([
                'denuncia_id' => $denuncia->id,
                'asignado_a_id' => $funcionario->id,
                'asignado_por_id' => $request->user()->id,
                'motivo' => $validated['motivo'],
            ]);

            // Notificar al nuevo funcionario
            \App\Models\Notificacion::create([
                'usuario_id' => $funcionario->id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'REASIGNACION',
                'titulo' => 'Denuncia reasignada',
                'mensaje' => "Se te ha reasignado la denuncia {$denuncia->codigo}",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);

            // Notificar al funcionario anterior si existe
            if ($funcionarioAnterior) {
                \App\Models\Notificacion::create([
                    'usuario_id' => $funcionarioAnterior->id,
                    'denuncia_id' => $denuncia->id,
                    'tipo' => 'REASIGNACION',
                    'titulo' => 'Denuncia reasignada',
                    'mensaje' => "La denuncia {$denuncia->codigo} ha sido reasignada a otro funcionario",
                    'url' => route('funcionario.denuncias.show', $denuncia->id),
                ]);
            }
        });

        return back()->with('success', 'Denuncia reasignada correctamente.');
    }

    /**
     * Cambiar prioridad de una denuncia
     */
    public function cambiarPrioridad(Request $request, Denuncia $denuncia)
    {
        $this->authorize('cambiarPrioridad', $denuncia);

        $validated = $request->validate([
            'prioridad_id' => 'required|exists:prioridades_denuncia,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        $prioridadAnterior = $denuncia->prioridad;
        $nuevaPrioridad = PrioridadDenuncia::findOrFail($validated['prioridad_id']);

        DB::transaction(function () use ($denuncia, $nuevaPrioridad, $prioridadAnterior, $validated, $request) {
            $denuncia->update(['prioridad_id' => $nuevaPrioridad->id]);

            // Recalcular SLA si cambió la prioridad
            if ($nuevaPrioridad->sla_horas && $denuncia->registrada_en) {
                $denuncia->update([
                    'fecha_limite_sla' => $denuncia->registrada_en->addHours($nuevaPrioridad->sla_horas),
                ]);
            }

            // Registrar auditoría
            \App\Models\RegistroAuditoria::registrar(
                'CAMBIO_PRIORIDAD',
                $request->user(),
                'Denuncia',
                $denuncia->id,
                ['prioridad' => $prioridadAnterior->nombre],
                ['prioridad' => $nuevaPrioridad->nombre, 'motivo' => $validated['motivo']]
            );

            // Notificar al funcionario asignado si existe
            if ($denuncia->asignado_a_id) {
                \App\Models\Notificacion::create([
                    'usuario_id' => $denuncia->asignado_a_id,
                    'denuncia_id' => $denuncia->id,
                    'tipo' => 'CAMBIO_PRIORIDAD',
                    'titulo' => 'Cambio de prioridad',
                    'mensaje' => "La denuncia {$denuncia->codigo} cambió a prioridad {$nuevaPrioridad->nombre}",
                    'url' => route('funcionario.denuncias.show', $denuncia->id),
                ]);
            }
        });

        return back()->with('success', 'Prioridad actualizada correctamente.');
    }

    /**
     * Reportes del área
     */
    public function reportes(Request $request)
    {
        $usuario = $request->user();

        // Estadísticas del mes actual
        $mesActual = now()->month;
        $añoActual = now()->year;

        $stats = [
            'total_mes' => Denuncia::where('area_id', $usuario->area_id)
                ->whereMonth('creado_en', $mesActual)
                ->whereYear('creado_en', $añoActual)
                ->count(),
            'cerradas_mes' => Denuncia::where('area_id', $usuario->area_id)
                ->whereMonth('cerrada_en', $mesActual)
                ->whereYear('cerrada_en', $añoActual)
                ->count(),
            'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucion($usuario->area_id),
            'sla_cumplido_porcentaje' => $this->calcularPorcentajeSLACumplido($usuario->area_id),
        ];

        // Denuncias por estado
        $porEstado = EstadoDenuncia::withCount([
            'denuncias' => fn($q) => $q->where('area_id', $usuario->area_id)
        ])->get();

        // Denuncias por categoría
        $porCategoria = CategoriaDenuncia::withCount([
            'denuncias' => fn($q) => $q->where('area_id', $usuario->area_id)
        ])->activas()->get();

        return Inertia::render('Supervisor/Reportes', [
            'stats' => $stats,
            'porEstado' => $porEstado,
            'porCategoria' => $porCategoria,
        ]);
    }

    /**
     * Calcular tiempo promedio de resolución (en horas)
     */
    private function calcularTiempoPromedioResolucion($areaId): float
    {
        $denunciasCerradas = Denuncia::where('area_id', $areaId)
            ->whereNotNull('cerrada_en')
            ->whereMonth('cerrada_en', now()->month)
            ->get(['registrada_en', 'cerrada_en']);

        if ($denunciasCerradas->isEmpty()) {
            return 0;
        }

        $totalHoras = 0;
        foreach ($denunciasCerradas as $denuncia) {
            $totalHoras += $denuncia->registrada_en->diffInHours($denuncia->cerrada_en);
        }

        return round($totalHoras / $denunciasCerradas->count(), 2);
    }

    /**
     * Calcular porcentaje de SLA cumplido
     */
    private function calcularPorcentajeSLACumplido($areaId): float
    {
        $denunciasCerradas = Denuncia::where('area_id', $areaId)
            ->whereNotNull('cerrada_en')
            ->whereMonth('cerrada_en', now()->month)
            ->count();

        if ($denunciasCerradas === 0) {
            return 0;
        }

        $slaCumplido = Denuncia::where('area_id', $areaId)
            ->whereNotNull('cerrada_en')
            ->whereMonth('cerrada_en', now()->month)
            ->whereRaw('cerrada_en <= fecha_limite_sla')
            ->count();

        return round(($slaCumplido / $denunciasCerradas) * 100, 2);
    }
}

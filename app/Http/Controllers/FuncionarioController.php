<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDenuncia;
use App\Models\Denuncia;
use App\Models\EstadoDenuncia;
use App\Models\PrioridadDenuncia;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FuncionarioController extends Controller
{
    /**
     * Dashboard del funcionario
     */
    public function dashboard(Request $request)
    {
        $usuario = $request->user();

        // Verificar que el funcionario tenga un área asignada
        if (! $usuario->area_id) {
            return Inertia::render('Funcionario/Dashboard', [
                'error' => 'No tienes un área asignada. Contacta al administrador para que te asigne a un área.',
                'stats' => [
                    'total' => 0,
                    'asignadas_a_mi' => 0,
                    'en_proceso' => 0,
                    'sla_vencido' => 0,
                ],
                'denunciasRecientes' => [],
                'denunciasSlaPendiente' => [],
                'porEstado' => [],
            ]);
        }

        // Estadísticas del área
        $stats = [
            'total' => Denuncia::where('area_id', $usuario->area_id)->count(),
            'asignadas_a_mi' => Denuncia::where('asignado_a_id', $usuario->id)
                ->whereNull('cerrada_en')
                ->count(),
            'en_proceso' => Denuncia::where('area_id', $usuario->area_id)
                ->whereHas('estado', fn ($q) => $q->where('codigo', 'PRO'))
                ->count(),
            'sla_vencido' => Denuncia::where('area_id', $usuario->area_id)
                ->slaPendiente()
                ->count(),
        ];

        // Denuncias recientes del área
        $denunciasRecientes = Denuncia::where('area_id', $usuario->area_id)
            ->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'asignadoA'])
            ->orderBy('creado_en', 'desc')
            ->limit(10)
            ->get();

        // Denuncias con SLA vencido
        $denunciasSlaPendiente = Denuncia::where('area_id', $usuario->area_id)
            ->slaPendiente()
            ->with(['ciudadano', 'estado', 'categoria', 'prioridad'])
            ->limit(5)
            ->get();

        // Estadísticas por estado
        $porEstado = EstadoDenuncia::withCount([
            'denuncias' => fn ($q) => $q->where('area_id', $usuario->area_id),
        ])->get();

        return Inertia::render('Funcionario/Dashboard', [
            'stats' => $stats,
            'denunciasRecientes' => $denunciasRecientes,
            'denunciasSlaPendiente' => $denunciasSlaPendiente,
            'porEstado' => $porEstado,
        ]);
    }

    /**
     * Lista de denuncias del área
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Verificar que el funcionario tenga un área asignada
        if (! $usuario->area_id) {
            return Inertia::render('Funcionario/Denuncias/Index', [
                'error' => 'No tienes un área asignada. Contacta al administrador.',
                'denuncias' => collect([]),
                'filtros' => [],
                'estados' => [],
                'categorias' => [],
                'prioridades' => [],
                'funcionarios' => [],
            ]);
        }

        $query = Denuncia::where('area_id', $usuario->area_id)
            ->with(['ciudadano', 'estado', 'categoria', 'prioridad', 'distrito', 'asignadoA']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->input('estado_id'));
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('prioridad_id')) {
            $query->where('prioridad_id', $request->input('prioridad_id'));
        }

        if ($request->filled('asignado_a')) {
            if ($request->input('asignado_a') === 'sin_asignar') {
                $query->whereNull('asignado_a_id');
            } elseif ($request->input('asignado_a') === 'mis_denuncias') {
                $query->where('asignado_a_id', $usuario->id);
            } else {
                $query->where('asignado_a_id', $request->input('asignado_a'));
            }
        }

        if ($request->filled('sla_vencido')) {
            $query->slaPendiente();
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'creado_en');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $denuncias = $query->paginate(15)->withQueryString();

        // Datos para filtros
        $estados = EstadoDenuncia::all(['id', 'nombre', 'codigo', 'color']);
        $categorias = CategoriaDenuncia::activas()->get(['id', 'nombre', 'icono']);
        $prioridades = PrioridadDenuncia::all(['id', 'nombre', 'codigo', 'color']);
        $funcionarios = Usuario::where('area_id', $usuario->area_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('nombre', ['funcionario', 'supervisor']))
            ->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Funcionario/Denuncias/Index', [
            'denuncias' => $denuncias,
            'filtros' => $request->only(['search', 'estado_id', 'categoria_id', 'prioridad_id', 'asignado_a', 'sla_vencido']),
            'estados' => $estados,
            'categorias' => $categorias,
            'prioridades' => $prioridades,
            'funcionarios' => $funcionarios,
        ]);
    }

    /**
     * Ver detalle de una denuncia
     */
    public function show(Denuncia $denuncia)
    {
        // Autorización en DenunciaPolicy
        $this->authorize('verComoFuncionario', $denuncia);

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

        // Estados disponibles para transición
        $estadosDisponibles = $this->obtenerEstadosDisponibles($denuncia);

        // Funcionarios del área para asignación
        $funcionariosArea = Usuario::where('area_id', $denuncia->area_id)
            ->where('activo', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('nombre', ['funcionario', 'supervisor']))
            ->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Funcionario/Denuncias/Show', [
            'denuncia' => $denuncia,
            'estadosDisponibles' => $estadosDisponibles,
            'funcionariosArea' => $funcionariosArea,
        ]);
    }

    /**
     * Cambiar estado de una denuncia
     */
    public function cambiarEstado(Request $request, Denuncia $denuncia)
    {
        $this->authorize('cambiarEstado', $denuncia);

        $validated = $request->validate([
            'estado_id' => 'required|exists:estados_denuncia,id',
            'motivo' => 'nullable|string|max:500',
            'comentario_interno' => 'nullable|string|max:1000',
        ]);

        $nuevoEstado = EstadoDenuncia::findOrFail($validated['estado_id']);

        DB::transaction(function () use ($denuncia, $nuevoEstado, $validated, $request) {
            // Cambiar estado y registrar historial
            $denuncia->cambiarEstado($nuevoEstado, $request->user(), $validated['motivo'] ?? null);

            // Agregar comentario interno si existe
            if (! empty($validated['comentario_interno'])) {
                $denuncia->comentarios()->create([
                    'usuario_id' => $request->user()->id,
                    'contenido' => $validated['comentario_interno'],
                    'es_interno' => true,
                ]);
            }

            // Notificar al ciudadano
            $this->notificarCambiEstado($denuncia, $nuevoEstado);
        });

        return back()->with('success', 'Estado de la denuncia actualizado correctamente.');
    }

    /**
     * Tomar asignación de una denuncia
     */
    public function tomarAsignacion(Request $request, Denuncia $denuncia)
    {
        $this->authorize('asignar', $denuncia);

        if ($denuncia->asignado_a_id !== null) {
            return back()->with('error', 'Esta denuncia ya está asignada.');
        }

        DB::transaction(function () use ($denuncia, $request) {
            $denuncia->update(['asignado_a_id' => $request->user()->id]);

            // Registrar historial de asignación
            \App\Models\HistorialAsignacion::create([
                'denuncia_id' => $denuncia->id,
                'asignado_a_id' => $request->user()->id,
                'asignado_por_id' => $request->user()->id,
                'motivo' => 'Auto-asignación',
            ]);

            // Cambiar a estado "En Proceso" si está en "Registrada"
            if ($denuncia->estado->codigo === 'REG') {
                $estadoEnProceso = EstadoDenuncia::where('codigo', 'PRO')->first();
                if ($estadoEnProceso) {
                    $denuncia->cambiarEstado($estadoEnProceso, $request->user(), 'Asignación inicial');
                }
            }
        });

        return back()->with('success', 'Denuncia asignada correctamente.');
    }

    /**
     * Agregar comentario a una denuncia
     */
    public function agregarComentario(Request $request, Denuncia $denuncia)
    {
        $this->authorize('comentar', $denuncia);

        $validated = $request->validate([
            'contenido' => 'required|string|max:1000',
            'es_interno' => 'boolean',
        ]);

        $comentario = $denuncia->comentarios()->create([
            'usuario_id' => $request->user()->id,
            'contenido' => $validated['contenido'],
            'es_interno' => $request->boolean('es_interno', true),
        ]);

        // Si el comentario es público, notificar al ciudadano
        if (! $comentario->es_interno) {
            $this->notificarNuevoComentario($denuncia, $comentario);
        }

        return back()->with('success', 'Comentario agregado correctamente.');
    }

    /**
     * Obtener estados disponibles según el estado actual
     */
    private function obtenerEstadosDisponibles(Denuncia $denuncia): array
    {
        $estadoActual = $denuncia->estado;

        // Lógica de transiciones de estado
        $transiciones = [
            'REG' => ['PRO', 'REC'], // Registrada -> En Proceso, Rechazada
            'PRO' => ['ATE', 'PEN', 'REC'], // En Proceso -> Atendida, Pendiente, Rechazada
            'PEN' => ['PRO', 'ATE'], // Pendiente -> En Proceso, Atendida
            'ATE' => ['CER'], // Atendida -> Cerrada
            'REC' => [], // Rechazada -> (ninguna)
            'CER' => [], // Cerrada -> (ninguna)
        ];

        $codigosDisponibles = $transiciones[$estadoActual->codigo] ?? [];

        return EstadoDenuncia::whereIn('codigo', $codigosDisponibles)->get(['id', 'nombre', 'codigo', 'color'])->toArray();
    }

    /**
     * Notificar cambio de estado al ciudadano
     */
    private function notificarCambiEstado(Denuncia $denuncia, EstadoDenuncia $nuevoEstado): void
    {
        \App\Models\Notificacion::create([
            'usuario_id' => $denuncia->ciudadano_id,
            'denuncia_id' => $denuncia->id,
            'tipo' => 'CAMBIO_ESTADO',
            'titulo' => 'Actualización de tu denuncia',
            'mensaje' => "Tu denuncia {$denuncia->codigo} cambió al estado: {$nuevoEstado->nombre}",
            'url' => route('denuncias.show', $denuncia->id),
        ]);
    }

    /**
     * Notificar nuevo comentario al ciudadano
     */
    private function notificarNuevoComentario(Denuncia $denuncia, $comentario): void
    {
        \App\Models\Notificacion::create([
            'usuario_id' => $denuncia->ciudadano_id,
            'denuncia_id' => $denuncia->id,
            'tipo' => 'NUEVO_COMENTARIO',
            'titulo' => 'Nuevo comentario en tu denuncia',
            'mensaje' => "Hay un nuevo comentario en tu denuncia {$denuncia->codigo}",
            'url' => route('denuncias.show', $denuncia->id),
        ]);
    }
}

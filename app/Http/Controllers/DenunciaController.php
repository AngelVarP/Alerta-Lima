<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use App\Models\Comentario;
use App\Models\Denuncia;
use App\Models\CategoriaDenuncia;
use App\Models\Distrito;
use App\Models\EstadoDenuncia;
use App\Models\HistorialAsignacion;
use App\Models\PrioridadDenuncia;
use App\Models\RegistroAuditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DenunciaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $query = Denuncia::with(['estado', 'categoria', 'prioridad', 'distrito', 'asignadoA']);

        // Filtrar según rol
        if ($usuario->tieneRol('ciudadano') && !$usuario->esFuncionario()) {
            $query->where('ciudadano_id', $usuario->id);
        } elseif ($usuario->tieneRol('funcionario') && !$usuario->esAdmin()) {
            $query->where('area_id', $usuario->area_id);
        }

        // Filtros opcionales
        if ($request->filled('estado')) {
            $query->where('estado_id', $request->estado);
        }
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad_id', $request->prioridad);
        }
        if ($request->filled('distrito')) {
            $query->where('distrito_id', $request->distrito);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->buscar}%")
                  ->orWhere('titulo', 'like', "%{$request->buscar}%");
            });
        }

        $denuncias = $query->orderBy('registrada_en', 'desc')->paginate(15);

        // Datos para filtros
        $estados = EstadoDenuncia::ordenado()->get();
        $categorias = CategoriaDenuncia::activas()->get();
        $prioridades = PrioridadDenuncia::ordenado()->get();
        $distritos = Distrito::activos()->get();

        return view('denuncias.index', compact(
            'denuncias', 'estados', 'categorias', 'prioridades', 'distritos'
        ));
    }

    public function create()
    {
        $categorias = CategoriaDenuncia::activas()->get();
        $distritos = Distrito::activos()->get();
        $prioridades = PrioridadDenuncia::ordenado()->get();

        return view('denuncias.create', compact('categorias', 'distritos', 'prioridades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria_id' => 'required|exists:categorias_denuncia,id',
            'distrito_id' => 'nullable|exists:distritos,id',
            'direccion' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'es_anonima' => 'boolean',
            'adjuntos.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,mp4',
        ]);

        // Obtener estado inicial y prioridad por defecto
        $estadoInicial = EstadoDenuncia::inicial();
        $prioridadDefault = PrioridadDenuncia::where('codigo', 'MED')->first();

        $denuncia = Denuncia::create([
            'ciudadano_id' => $request->user()->id,
            'categoria_id' => $validated['categoria_id'],
            'estado_id' => $estadoInicial->id,
            'prioridad_id' => $request->prioridad_id ?? $prioridadDefault->id,
            'distrito_id' => $validated['distrito_id'] ?? null,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'direccion' => $validated['direccion'] ?? null,
            'referencia' => $validated['referencia'] ?? null,
            'latitud' => $validated['latitud'] ?? null,
            'longitud' => $validated['longitud'] ?? null,
            'es_anonima' => $validated['es_anonima'] ?? false,
            'ip_origen' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Guardar adjuntos
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $archivo) {
                $ruta = $archivo->store('adjuntos/' . $denuncia->id, 'local');

                Adjunto::create([
                    'denuncia_id' => $denuncia->id,
                    'subido_por_id' => $request->user()->id,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'nombre_almacenado' => basename($ruta),
                    'ruta_almacenamiento' => $ruta,
                    'tipo_mime' => $archivo->getMimeType(),
                    'tamano_bytes' => $archivo->getSize(),
                    'hash_archivo' => hash_file('sha256', $archivo->path()),
                ]);
            }
        }

        // Registrar auditoría
        RegistroAuditoria::registrar(
            'CREAR_DENUNCIA',
            $request->user(),
            'Denuncia',
            $denuncia->id,
            null,
            $denuncia->toArray()
        );

        return redirect()->route('denuncias.show', $denuncia)
            ->with('success', "Denuncia registrada exitosamente. Código: {$denuncia->codigo}");
    }

    public function show(Denuncia $denuncia)
    {
        $this->authorize('ver', $denuncia);

        $denuncia->load([
            'ciudadano', 'asignadoA', 'area', 'categoria',
            'prioridad', 'estado', 'distrito', 'adjuntos',
            'comentarios' => fn($q) => $q->with('usuario')->orderBy('creado_en', 'desc'),
            'historialEstados' => fn($q) => $q->with(['estadoAnterior', 'estadoNuevo', 'cambiadoPor'])->orderBy('creado_en', 'desc'),
        ]);

        // Transiciones disponibles
        $transicionesDisponibles = $denuncia->estado->transicionesOrigen()
            ->where('activo', true)
            ->with('estadoDestino')
            ->get();

        return view('denuncias.show', compact('denuncia', 'transicionesDisponibles'));
    }

    public function cambiarEstado(Request $request, Denuncia $denuncia)
    {
        $this->authorize('cambiarEstado', $denuncia);

        $validated = $request->validate([
            'estado_id' => 'required|exists:estados_denuncia,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        $nuevoEstado = EstadoDenuncia::findOrFail($validated['estado_id']);

        // Verificar transición válida
        if (!$denuncia->estado->puedeTransicionarA($nuevoEstado)) {
            return back()->with('error', 'Transición de estado no permitida.');
        }

        $denuncia->cambiarEstado($nuevoEstado, $request->user(), $validated['motivo'] ?? null);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function asignar(Request $request, Denuncia $denuncia)
    {
        $this->authorize('asignar', $denuncia);

        $validated = $request->validate([
            'asignado_a_id' => 'required|exists:usuarios,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        $usuarioAnterior = $denuncia->asignadoA;
        $nuevoUsuario = Usuario::findOrFail($validated['asignado_a_id']);

        // Registrar historial
        HistorialAsignacion::create([
            'denuncia_id' => $denuncia->id,
            'asignado_de_id' => $usuarioAnterior?->id,
            'asignado_a_id' => $nuevoUsuario->id,
            'area_anterior_id' => $denuncia->area_id,
            'area_nueva_id' => $nuevoUsuario->area_id,
            'asignado_por_id' => $request->user()->id,
            'motivo' => $validated['motivo'] ?? null,
        ]);

        $denuncia->update([
            'asignado_a_id' => $nuevoUsuario->id,
            'area_id' => $nuevoUsuario->area_id,
        ]);

        return back()->with('success', 'Denuncia asignada correctamente.');
    }

    public function agregarComentario(Request $request, Denuncia $denuncia)
    {
        $validated = $request->validate([
            'comentario' => 'required|string',
            'es_interno' => 'boolean',
        ]);

        Comentario::create([
            'denuncia_id' => $denuncia->id,
            'usuario_id' => $request->user()->id,
            'comentario' => $validated['comentario'],
            'es_interno' => $validated['es_interno'] ?? false,
        ]);

        return back()->with('success', 'Comentario agregado.');
    }
}

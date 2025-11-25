<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\EstadoDenuncia;
use App\Models\CategoriaDenuncia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DenunciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // DEBUG: Log para verificar
        \Log::info('Usuario viendo denuncias:', [
            'user_id' => $user->id,
            'user_nombre' => $user->nombre,
        ]);

        // Construir la consulta base
        $query = Denuncia::where('ciudadano_id', $user->id)
            ->with(['estado', 'categoria', 'prioridad', 'distrito']);

        // Aplicar filtro de búsqueda si existe
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Aplicar filtro de estado si existe
        if ($request->filled('estado')) {
            $query->whereHas('estado', function ($q) use ($request) {
                $q->where('codigo', $request->input('estado'));
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->orderBy('creado_en', 'desc');

        // Paginar los resultados
        $denuncias = $query->paginate(10)->withQueryString();

        // DEBUG: Log para verificar resultados
        \Log::info('Denuncias encontradas:', [
            'total' => $denuncias->total(),
            'count' => $denuncias->count(),
        ]);

        // Obtener todos los estados para el filtro
        $estados = EstadoDenuncia::all(['id', 'nombre', 'codigo']);

        return Inertia::render('Ciudadano/Denuncias/Index', [
            'denuncias' => $denuncias,
            'filtros' => [
                'search' => $request->input('search', ''),
                'estado' => $request->input('estado', ''),
            ],
            'estados' => $estados,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ciudadano/Denuncias/Create', [
            'categorias' => CategoriaDenuncia::activas()->get(['id', 'nombre', 'descripcion', 'icono']),
            'distritos' => \App\Models\Distrito::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias_denuncia,id',
            'descripcion' => 'required|string|min:20',
            'direccion' => 'required|string|max:500',
            'distrito_id' => 'required|exists:distritos,id',
            'referencia' => 'nullable|string|max:500',
            'es_anonima' => 'boolean',
            'adjuntos.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4|max:10240', // 10MB max
        ]);

        // Generar código único para la denuncia
        $year = date('Y');
        $lastDenuncia = Denuncia::whereYear('creado_en', $year)->orderBy('id', 'desc')->first();
        $nextNumber = $lastDenuncia ? (intval(substr($lastDenuncia->codigo, -5)) + 1) : 1;
        $codigo = 'DEN-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Obtener el estado inicial "Registrada"
        $estadoRegistrada = EstadoDenuncia::where('codigo', 'REG')->first();

        // Obtener la prioridad por defecto (Media)
        $prioridadMedia = \App\Models\PrioridadDenuncia::where('codigo', 'MED')->first();

        // Crear la denuncia
        $denuncia = Denuncia::create([
            'codigo' => $codigo,
            'ciudadano_id' => Auth::id(),
            'categoria_id' => $validated['categoria_id'],
            'estado_id' => $estadoRegistrada->id,
            'prioridad_id' => $prioridadMedia ? $prioridadMedia->id : null,
            'distrito_id' => $validated['distrito_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'direccion' => $validated['direccion'],
            'referencia' => $validated['referencia'] ?? null,
            'es_anonima' => $request->boolean('es_anonima', false),
            'ip_origen' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Procesar archivos adjuntos si existen
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                // Guardar archivo en storage/app/public/denuncias
                $path = $file->store('denuncias/' . $denuncia->id, 'public');

                // Crear registro en la tabla adjuntos_denuncia
                \App\Models\AdjuntoDenuncia::create([
                    'denuncia_id' => $denuncia->id,
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'ruta_archivo' => $path,
                    'tipo_mime' => $file->getMimeType(),
                    'tamano' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('denuncias.index')
            ->with('success', '¡Denuncia registrada exitosamente! Código: ' . $codigo);
    }

    /**
     * Display the specified resource.
     */
    public function show(Denuncia $denuncia)
    {
        // Verificar que la denuncia pertenece al usuario actual
        if ($denuncia->ciudadano_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta denuncia.');
        }

        // Cargar todas las relaciones necesarias
        $denuncia->load([
            'categoria',
            'estado',
            'distrito',
            'prioridad',
            'adjuntos',
            'historialEstados.estadoNuevo',
            'historialEstados.cambiadoPor'
        ]);

        return Inertia::render('Ciudadano/Denuncias/Show', [
            'denuncia' => $denuncia,
        ]);
    }
}
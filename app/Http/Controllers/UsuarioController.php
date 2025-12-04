<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\RegistroAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with(['area', 'roles']);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%")
                  ->orWhere('dni', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('roles.id', $request->rol));
        }

        if ($request->filled('area')) {
            $query->where('area_id', $request->area);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        $usuarios = $query->orderBy('nombre')->paginate(15);
        $roles = Rol::all();
        $areas = Area::activas()->get();

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'areas' => $areas,
            'filtros' => $request->only(['buscar', 'rol', 'area', 'activo']),
        ]);
    }

    public function create()
    {
        $roles = Rol::all();
        $areas = Area::activas()->get();

        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => $roles,
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'apellido' => 'nullable|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'dni' => 'nullable|string|max:15|unique:usuarios,dni',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'area_id' => 'nullable|exists:areas,id',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'] ?? null,
            'email' => $validated['email'],
            'dni' => $validated['dni'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'activo' => true,
        ]);

        $usuario->roles()->sync($validated['roles']);

        RegistroAuditoria::registrar(
            'CREAR_USUARIO',
            $request->user(),
            'Usuario',
            $usuario->id,
            null,
            $usuario->toArray()
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(Usuario $usuario)
    {
        $roles = Rol::all();
        $areas = Area::activas()->get();
        $usuario->load('roles');

        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'areas' => $areas,
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'apellido' => 'nullable|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios')->ignore($usuario->id)],
            'dni' => ['nullable', 'string', 'max:15', Rule::unique('usuarios')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'area_id' => 'nullable|exists:areas,id',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'activo' => 'boolean',
        ]);

        $datosAnteriores = $usuario->toArray();

        $usuario->update([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'] ?? null,
            'email' => $validated['email'],
            'dni' => $validated['dni'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ]);

        if (!empty($validated['password'])) {
            $usuario->update(['password_hash' => Hash::make($validated['password'])]);
        }

        $usuario->roles()->sync($validated['roles']);

        RegistroAuditoria::registrar(
            'ACTUALIZAR_USUARIO',
            $request->user(),
            'Usuario',
            $usuario->id,
            $datosAnteriores,
            $usuario->fresh()->toArray()
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function toggleActivo(Request $request, Usuario $usuario)
    {
        $usuario->update(['activo' => !$usuario->activo]);

        $accion = $usuario->activo ? 'activado' : 'desactivado';

        RegistroAuditoria::registrar(
            'TOGGLE_USUARIO_ACTIVO',
            $request->user(),
            'Usuario',
            $usuario->id
        );

        return back()->with('success', "Usuario {$accion} correctamente.");
    }
}

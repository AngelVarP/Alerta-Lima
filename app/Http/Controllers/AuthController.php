<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', // Puede ser email o DNI
            'password' => 'required|string',
        ]);

        $loginField = $credentials['login'];
        $password = $credentials['password'];

        // Detectar si es email o DNI
        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'dni';

        // Buscar usuario por email o DNI
        $usuario = Usuario::where($fieldType, $loginField)->first();

        // Verificar si existe y la contraseña es correcta
        if (!$usuario || !Hash::check($password, $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'login' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Verificar si el usuario está activo
        if (!$usuario->activo) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta está desactivada. Contacta al administrador.'],
            ]);
        }

        // Verificar si está bloqueado
        if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta > now()) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta está bloqueada temporalmente. Intenta más tarde.'],
            ]);
        }

        // Login exitoso
        Auth::login($usuario, $request->boolean('remember'));

        // Actualizar último login
        $usuario->update([
            'ultimo_login' => now(),
            'intentos_fallidos' => 0,
        ]);

        $request->session()->regenerate();

        // Redirigir según el rol del usuario
        if ($usuario->tieneRol('admin')) {
            return redirect()->intended('/admin');
        } elseif ($usuario->tieneRol('supervisor')) {
            return redirect()->intended('/supervisor');
        } elseif ($usuario->tieneRol('funcionario')) {
            return redirect()->intended('/funcionario');
        } else {
            // Ciudadano por defecto
            return redirect()->intended('/dashboard');
        }
    }

    /**
     * Mostrar el formulario de registro
     */
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Procesar el registro
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'email' => 'required|email|unique:usuarios,email',
            'dni' => 'required|string|size:8|regex:/^[0-9]{8}$/|unique:usuarios,dni',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI debe contener solo números.',
            'dni.unique' => 'Este DNI ya está registrado.',
        ]);

        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'telefono' => $validated['telefono'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'activo' => true,
        ]);

        // Asignar rol de ciudadano
        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        DB::table('rol_usuario')->insert([
            'usuario_id' => $usuario->id,
            'rol_id' => $roleCiudadanoId,
            'model_type' => 'App\\Models\\Usuario'
        ]);

        // Login automático después del registro
        Auth::login($usuario);

        return redirect('/dashboard');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

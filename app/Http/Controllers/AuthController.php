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
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Buscar usuario por email
        $usuario = Usuario::where('email', $credentials['email'])->first();

        // Verificar si existe y la contraseña es correcta
        if (!$usuario || !Hash::check($credentials['password'], $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
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

        return redirect()->intended('/dashboard');
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
            'dni' => 'nullable|string|max:15',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'dni' => $validated['dni'] ?? null,
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

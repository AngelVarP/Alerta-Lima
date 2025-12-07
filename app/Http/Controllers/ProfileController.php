<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Ciudadano/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request)
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'apellido' => 'nullable|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique(Usuario::class)->ignore($usuario->id)],
            'dni' => ['nullable', 'string', 'max:15', Rule::unique(Usuario::class)->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        $usuario->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}

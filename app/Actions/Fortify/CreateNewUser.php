<?php

namespace App\Actions\Fortify;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Usuario
    {
        Validator::make($input, [
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email'),
            ],
            'dni' => ['nullable', 'string', 'max:15'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => $this->passwordRules(),
        ])->validate();

        $usuario = Usuario::create([
            'nombre' => $input['nombre'],
            'apellido' => $input['apellido'] ?? null,
            'email' => $input['email'],
            'dni' => $input['dni'] ?? null,
            'telefono' => $input['telefono'] ?? null,
            'password_hash' => Hash::make($input['password']),
            'activo' => true,
        ]);

        // Asignar rol de ciudadano por defecto
        $rolCiudadano = Rol::where('nombre', 'ciudadano')->first();
        if ($rolCiudadano) {
            $usuario->roles()->attach($rolCiudadano->id);
        }

        return $usuario;
    }
}

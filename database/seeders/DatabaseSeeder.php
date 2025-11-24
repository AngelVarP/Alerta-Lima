<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. PRIMERO: Configurar el entorno (Roles, Áreas, Catálogos)
        $this->call([
            RoleSeeder::class,
            CatalogSeeder::class,
        ]);

        // 2. SEGUNDO: Crear tu Super Admin (para que puedas entrar ya mismo)
        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@alerta.lima.gob.pe'],
            [
                'nombre' => 'Angel',
                'apellido' => 'Admin',
                'password_hash' => Hash::make('password'),
                'dni' => '12345678',
                'activo' => true,
            ]
        );

        // 3. TERCERO: Asignarle el rol de admin (tabla pivote rol_usuario)
        $roleAdminId = DB::table('roles')->where('nombre', 'admin')->value('id');

        // Solo asignar si no tiene el rol
        $tieneRol = DB::table('rol_usuario')
            ->where('usuario_id', $admin->id)
            ->where('rol_id', $roleAdminId)
            ->exists();

        if (!$tieneRol) {
            DB::table('rol_usuario')->insert([
                'usuario_id' => $admin->id,
                'rol_id' => $roleAdminId,
                'model_type' => 'App\\Models\\Usuario'
            ]);
        }

        // Opcional: Crear un ciudadano de prueba
        $ciudadano = Usuario::firstOrCreate(
            ['email' => 'vecino@gmail.com'],
            [
                'nombre' => 'Juan',
                'apellido' => 'Vecino',
                'password_hash' => Hash::make('password'),
                'dni' => '87654321',
                'activo' => true,
            ]
        );

        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');

        // Solo asignar si no tiene el rol
        $tieneRolCiudadano = DB::table('rol_usuario')
            ->where('usuario_id', $ciudadano->id)
            ->where('rol_id', $roleCiudadanoId)
            ->exists();

        if (!$tieneRolCiudadano) {
            DB::table('rol_usuario')->insert([
                'usuario_id' => $ciudadano->id,
                'rol_id' => $roleCiudadanoId,
                'model_type' => 'App\\Models\\Usuario'
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            EstadoDenunciaSeeder::class,
            PrioridadDenunciaSeeder::class,
            DistritoSeeder::class,
            AreaYCategoriaSeeder::class,
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

        if (! $tieneRol) {
            DB::table('rol_usuario')->insert([
                'usuario_id' => $admin->id,
                'rol_id' => $roleAdminId,
                'model_type' => 'App\\Models\\Usuario',
            ]);
        }

        // Obtener áreas (creadas en AreaYCategoriaSeeder)
        $areaLimpieza = DB::table('areas')->where('codigo', 'LIM')->value('id');
        $areaSeguridad = DB::table('areas')->where('codigo', 'SEG')->value('id');

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
        $this->asignarRol($ciudadano->id, $roleCiudadanoId);

        // Crear funcionario de prueba
        $funcionario = Usuario::firstOrCreate(
            ['email' => 'funcionario@alerta.lima.gob.pe'],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Funcionario',
                'password_hash' => Hash::make('password'),
                'dni' => '11223344',
                'area_id' => $areaLimpieza,
                'activo' => true,
            ]
        );

        $roleFuncionarioId = DB::table('roles')->where('nombre', 'funcionario')->value('id');
        $this->asignarRol($funcionario->id, $roleFuncionarioId);

        // Crear supervisor de prueba
        $supervisor = Usuario::firstOrCreate(
            ['email' => 'supervisor@alerta.lima.gob.pe'],
            [
                'nombre' => 'María',
                'apellido' => 'Supervisor',
                'password_hash' => Hash::make('password'),
                'dni' => '55667788',
                'area_id' => $areaSeguridad,
                'activo' => true,
            ]
        );

        $roleSupervisorId = DB::table('roles')->where('nombre', 'supervisor')->value('id');
        $this->asignarRol($supervisor->id, $roleSupervisorId);

        $this->command->info('✓ Usuarios de prueba creados:');
        $this->command->info('  Admin:       admin@alerta.lima.gob.pe / password');
        $this->command->info('  Ciudadano:   vecino@gmail.com / password');
        $this->command->info('  Funcionario: funcionario@alerta.lima.gob.pe / password (Área: Limpieza)');
        $this->command->info('  Supervisor:  supervisor@alerta.lima.gob.pe / password (Área: Seguridad)');
    }

    /**
     * Helper para asignar rol a usuario
     */
    private function asignarRol($usuarioId, $rolId): void
    {
        $existe = DB::table('rol_usuario')
            ->where('usuario_id', $usuarioId)
            ->where('rol_id', $rolId)
            ->exists();

        if (! $existe) {
            DB::table('rol_usuario')->insert([
                'usuario_id' => $usuarioId,
                'rol_id' => $rolId,
                'model_type' => 'App\\Models\\Usuario',
            ]);
        }
    }
}

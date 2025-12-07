<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear o actualizar Roles
        DB::table('roles')->updateOrInsert(['nombre' => 'ciudadano'], ['guard_name' => 'web']);
        DB::table('roles')->updateOrInsert(['nombre' => 'funcionario'], ['guard_name' => 'web']);
        DB::table('roles')->updateOrInsert(['nombre' => 'supervisor'], ['guard_name' => 'web']);
        DB::table('roles')->updateOrInsert(['nombre' => 'admin'], ['guard_name' => 'web']);

        // Obtener IDs de roles
        $roleCiudadano = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        $roleFuncionario = DB::table('roles')->where('nombre', 'funcionario')->value('id');
        $roleSupervisor = DB::table('roles')->where('nombre', 'supervisor')->value('id');
        $roleAdmin = DB::table('roles')->where('nombre', 'admin')->value('id');

        // 2. Crear o actualizar Permisos
        $permisos = [
            'crear_denuncia',
            'ver_mis_denuncias',
            'agregar_comentario_publico',
            'editar_mi_denuncia',
            'ver_denuncias_area',
            'atender_denuncia',
            'agregar_comentario_interno',
            'ver_todas_denuncias',
            'asignar_denuncia',
            'reasignar_denuncia',
            'cambiar_prioridad',
            'ver_dashboard',
            'ver_reportes',
            'gestionar_usuarios',
            'gestionar_roles',
            'gestionar_catalogos',
            'gestionar_areas',
            'ver_auditoria',
            'ver_eventos_seguridad',
            'configurar_sistema',
            'eliminar_denuncia',
        ];

        $permisosIds = [];
        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(['nombre' => $permiso], ['guard_name' => 'web']);
            $permisosIds[$permiso] = DB::table('permisos')->where('nombre', $permiso)->value('id');
        }

        // 3. Asignar Permisos a Roles (tabla pivote rol_permiso)
        // Limpiar asignaciones existentes para evitar duplicados
        DB::table('rol_permiso')->whereIn('rol_id', [$roleCiudadano, $roleFuncionario, $roleSupervisor, $roleAdmin])->delete();

        // A. Ciudadano
        $permisosCiudadano = ['crear_denuncia', 'ver_mis_denuncias', 'agregar_comentario_publico', 'editar_mi_denuncia'];
        foreach ($permisosCiudadano as $permiso) {
            DB::table('rol_permiso')->updateOrInsert(
                ['rol_id' => $roleCiudadano, 'permiso_id' => $permisosIds[$permiso]],
                []
            );
        }

        // B. Funcionario
        $permisosFuncionario = ['ver_denuncias_area', 'atender_denuncia', 'agregar_comentario_interno', 'agregar_comentario_publico'];
        foreach ($permisosFuncionario as $permiso) {
            DB::table('rol_permiso')->updateOrInsert(
                ['rol_id' => $roleFuncionario, 'permiso_id' => $permisosIds[$permiso]],
                []
            );
        }

        // C. Supervisor (hereda de funcionario + propios)
        $permisosSupervisor = [
            'ver_denuncias_area',
            'atender_denuncia',
            'agregar_comentario_interno',
            'agregar_comentario_publico',
            'ver_todas_denuncias',
            'asignar_denuncia',
            'reasignar_denuncia',
            'cambiar_prioridad',
            'ver_dashboard',
            'ver_reportes',
        ];
        foreach ($permisosSupervisor as $permiso) {
            DB::table('rol_permiso')->updateOrInsert(
                ['rol_id' => $roleSupervisor, 'permiso_id' => $permisosIds[$permiso]],
                []
            );
        }

        // D. Admin (todos los permisos)
        foreach ($permisosIds as $permisoId) {
            DB::table('rol_permiso')->insert([
                'rol_id' => $roleAdmin,
                'permiso_id' => $permisoId,
            ]);
        }
    }
}

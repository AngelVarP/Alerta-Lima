<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ÁREAS MUNICIPALES
        // -------------------------------------------------------
        $areas = [
            ['nombre' => 'Limpieza Pública', 'codigo' => 'LIMP', 'descripcion' => 'Área encargada de la recolección de residuos y limpieza de calles'],
            ['nombre' => 'Alumbrado Público', 'codigo' => 'ALUM', 'descripcion' => 'Área encargada del mantenimiento del alumbrado público'],
            ['nombre' => 'Seguridad Ciudadana', 'codigo' => 'SEGU', 'descripcion' => 'Serenazgo y seguridad ciudadana'],
            ['nombre' => 'Obras Públicas', 'codigo' => 'OBRA', 'descripcion' => 'Mantenimiento de pistas, veredas e infraestructura'],
            ['nombre' => 'Parques y Jardines', 'codigo' => 'PARQ', 'descripcion' => 'Mantenimiento de áreas verdes'],
            ['nombre' => 'Fiscalización', 'codigo' => 'FISC', 'descripcion' => 'Control de ruidos y fiscalización municipal'],
            ['nombre' => 'Atención al Ciudadano', 'codigo' => 'ATEN', 'descripcion' => 'Mesa de partes y atención general'],
        ];

        foreach ($areas as $area) {
            DB::table('areas')->updateOrInsert(
                ['codigo' => $area['codigo']], // Busca por código
                $area // Si no existe, inserta todo esto
            );
        }

        // 2. ESTADOS DE DENUNCIA
        // -------------------------------------------------------
        $estados = [
            ['nombre' => 'REGISTRADA', 'codigo' => 'REG', 'descripcion' => 'Denuncia recién registrada, pendiente de revisión', 'color' => '#3B82F6', 'es_inicial' => true, 'es_final' => false, 'orden' => 1],
            ['nombre' => 'EN_REVISION', 'codigo' => 'REV', 'descripcion' => 'Denuncia en proceso de revisión inicial', 'color' => '#F59E0B', 'es_inicial' => false, 'es_final' => false, 'orden' => 2],
            ['nombre' => 'EN_PROCESO', 'codigo' => 'PRO', 'descripcion' => 'Denuncia asignada y en proceso de atención', 'color' => '#8B5CF6', 'es_inicial' => false, 'es_final' => false, 'orden' => 3],
            ['nombre' => 'ATENDIDA', 'codigo' => 'ATE', 'descripcion' => 'Denuncia resuelta satisfactoriamente', 'color' => '#10B981', 'es_inicial' => false, 'es_final' => true, 'orden' => 4],
            ['nombre' => 'RECHAZADA', 'codigo' => 'REC', 'descripcion' => 'Denuncia rechazada por no cumplir requisitos', 'color' => '#EF4444', 'es_inicial' => false, 'es_final' => true, 'orden' => 5],
            ['nombre' => 'ARCHIVADA', 'codigo' => 'ARC', 'descripcion' => 'Denuncia archivada sin resolución', 'color' => '#6B7280', 'es_inicial' => false, 'es_final' => true, 'orden' => 6],
        ];

        foreach ($estados as $estado) {
            DB::table('estados_denuncia')->updateOrInsert(['codigo' => $estado['codigo']], $estado);
        }

        // 3. PRIORIDADES
        // -------------------------------------------------------
        $prioridades = [
            ['nombre' => 'BAJA', 'codigo' => 'LOW', 'descripcion' => 'Atención en horario regular', 'color' => '#6B7280', 'sla_horas' => 168, 'orden' => 1],
            ['nombre' => 'MEDIA', 'codigo' => 'MED', 'descripcion' => 'Atención prioritaria', 'color' => '#F59E0B', 'sla_horas' => 72, 'orden' => 2],
            ['nombre' => 'ALTA', 'codigo' => 'HIGH', 'descripcion' => 'Atención urgente', 'color' => '#F97316', 'sla_horas' => 24, 'orden' => 3],
            ['nombre' => 'CRITICA', 'codigo' => 'CRIT', 'descripcion' => 'Atención inmediata', 'color' => '#EF4444', 'sla_horas' => 4, 'orden' => 4],
        ];

        foreach ($prioridades as $prioridad) {
            DB::table('prioridades_denuncia')->updateOrInsert(['codigo' => $prioridad['codigo']], $prioridad);
        }

        // 4. CATEGORÍAS (Requiere IDs de Áreas)
        // -------------------------------------------------------
        // Helper para obtener ID de área rápido
        $getAreaId = fn ($codigo) => DB::table('areas')->where('codigo', $codigo)->value('id');

        $categorias = [
            ['nombre' => 'Basura', 'descripcion' => 'Acumulación de residuos sólidos en vía pública', 'icono' => 'trash', 'color' => '#84CC16', 'area_code' => 'LIMP', 'orden' => 1],
            ['nombre' => 'Alumbrado', 'descripcion' => 'Problemas con el alumbrado público', 'icono' => 'lightbulb', 'color' => '#FBBF24', 'area_code' => 'ALUM', 'orden' => 2],
            ['nombre' => 'Inseguridad', 'descripcion' => 'Situaciones de inseguridad ciudadana', 'icono' => 'shield-alert', 'color' => '#EF4444', 'area_code' => 'SEGU', 'orden' => 3],
            ['nombre' => 'Baches', 'descripcion' => 'Huecos o deterioro en pistas y veredas', 'icono' => 'construction', 'color' => '#F97316', 'area_code' => 'OBRA', 'orden' => 4],
            ['nombre' => 'Parques', 'descripcion' => 'Mantenimiento de áreas verdes y parques', 'icono' => 'trees', 'color' => '#22C55E', 'area_code' => 'PARQ', 'orden' => 5],
            ['nombre' => 'Ruido', 'descripcion' => 'Contaminación sonora excesiva', 'icono' => 'volume-x', 'color' => '#A855F7', 'area_code' => 'FISC', 'orden' => 6],
            ['nombre' => 'Otros', 'descripcion' => 'Otros problemas no categorizados', 'icono' => 'help-circle', 'color' => '#6B7280', 'area_code' => 'ATEN', 'orden' => 99],
        ];

        foreach ($categorias as $cat) {
            $areaId = $getAreaId($cat['area_code']);
            unset($cat['area_code']); // Quitamos el código auxiliar para no intentar insertarlo
            $cat['area_default_id'] = $areaId; // Asignamos el ID real

            DB::table('categorias_denuncia')->updateOrInsert(['nombre' => $cat['nombre']], $cat);
        }

        // 5. DISTRITOS
        // -------------------------------------------------------
        $distritos = [
            ['nombre' => 'Lima Cercado', 'codigo' => 'LIM01'],
            ['nombre' => 'Miraflores', 'codigo' => 'LIM02'],
            ['nombre' => 'San Isidro', 'codigo' => 'LIM03'],
            ['nombre' => 'Santiago de Surco', 'codigo' => 'LIM04'],
            ['nombre' => 'La Molina', 'codigo' => 'LIM05'],
            ['nombre' => 'San Borja', 'codigo' => 'LIM06'],
            ['nombre' => 'Barranco', 'codigo' => 'LIM07'],
            ['nombre' => 'Chorrillos', 'codigo' => 'LIM08'],
            ['nombre' => 'San Juan de Lurigancho', 'codigo' => 'LIM09'],
            ['nombre' => 'Ate', 'codigo' => 'LIM10'],
            ['nombre' => 'San Juan de Miraflores', 'codigo' => 'LIM11'],
            ['nombre' => 'Villa El Salvador', 'codigo' => 'LIM12'],
            ['nombre' => 'Comas', 'codigo' => 'LIM13'],
            ['nombre' => 'Los Olivos', 'codigo' => 'LIM14'],
            ['nombre' => 'San Martín de Porres', 'codigo' => 'LIM15'],
        ];

        foreach ($distritos as $distrito) {
            $distrito['provincia'] = 'Lima';
            $distrito['departamento'] = 'Lima';
            DB::table('distritos')->updateOrInsert(['codigo' => $distrito['codigo']], $distrito);
        }

        // 6. CONFIGURACIÓN DEL SISTEMA
        // -------------------------------------------------------
        $configs = [
            ['clave' => 'app_nombre', 'valor' => 'Alerta Lima - Sistema de Gestión de Denuncias Ciudadanas', 'tipo' => 'string', 'categoria' => 'general'],
            ['clave' => 'session_timeout_minutos', 'valor' => '30', 'tipo' => 'integer', 'categoria' => 'seguridad'],
            ['clave' => 'max_intentos_login', 'valor' => '5', 'tipo' => 'integer', 'categoria' => 'seguridad'],
            ['clave' => '2fa_obligatorio_funcionarios', 'valor' => 'true', 'tipo' => 'boolean', 'categoria' => 'seguridad'],
            ['clave' => 'max_adjuntos_denuncia', 'valor' => '5', 'tipo' => 'integer', 'categoria' => 'archivos'],
        ];

        foreach ($configs as $conf) {
            DB::table('configuracion_sistema')->updateOrInsert(['clave' => $conf['clave']], $conf);
        }
    }
}

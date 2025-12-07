<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioridadDenunciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prioridades = [
            [
                'nombre' => 'Baja',
                'codigo' => 'BAJ',
                'descripcion' => 'Prioridad baja - Tiempo de atención: 7 días',
                'color' => '#10B981', // green-500
                'sla_horas' => 168, // 7 días
                'orden' => 3,
            ],
            [
                'nombre' => 'Media',
                'codigo' => 'MED',
                'descripcion' => 'Prioridad media - Tiempo de atención: 3 días',
                'color' => '#F59E0B', // amber-500
                'sla_horas' => 72, // 3 días
                'orden' => 2,
            ],
            [
                'nombre' => 'Alta',
                'codigo' => 'ALT',
                'descripcion' => 'Prioridad alta - Tiempo de atención: 24 horas',
                'color' => '#EF4444', // red-500
                'sla_horas' => 24, // 1 día
                'orden' => 1,
            ],
            [
                'nombre' => 'Crítica',
                'codigo' => 'CRI',
                'descripcion' => 'Prioridad crítica - Tiempo de atención: 4 horas',
                'color' => '#DC2626', // red-600
                'sla_horas' => 4,
                'orden' => 0,
            ],
        ];

        foreach ($prioridades as $prioridad) {
            DB::table('prioridades_denuncia')->insertOrIgnore(array_merge($prioridad, [
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]));
        }
    }
}

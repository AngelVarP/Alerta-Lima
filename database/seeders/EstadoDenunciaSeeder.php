<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoDenunciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            [
                'nombre' => 'Registrada',
                'codigo' => 'REG',
                'descripcion' => 'Denuncia recién registrada, pendiente de asignación',
                'color' => '#3B82F6', // blue-500
                'es_inicial' => true,
                'es_final' => false,
                'orden' => 1,
            ],
            [
                'nombre' => 'En Proceso',
                'codigo' => 'PRO',
                'descripcion' => 'Denuncia asignada y en proceso de atención',
                'color' => '#F59E0B', // amber-500
                'es_inicial' => false,
                'es_final' => false,
                'orden' => 2,
            ],
            [
                'nombre' => 'Pendiente',
                'codigo' => 'PEN',
                'descripcion' => 'Denuncia requiere información adicional o está en espera',
                'color' => '#8B5CF6', // violet-500
                'es_inicial' => false,
                'es_final' => false,
                'orden' => 3,
            ],
            [
                'nombre' => 'Atendida',
                'codigo' => 'ATE',
                'descripcion' => 'Denuncia atendida satisfactoriamente',
                'color' => '#10B981', // green-500
                'es_inicial' => false,
                'es_final' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Rechazada',
                'codigo' => 'REC',
                'descripcion' => 'Denuncia rechazada por no cumplir requisitos',
                'color' => '#EF4444', // red-500
                'es_inicial' => false,
                'es_final' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'Cerrada',
                'codigo' => 'CER',
                'descripcion' => 'Denuncia cerrada administrativamente',
                'color' => '#6B7280', // gray-500
                'es_inicial' => false,
                'es_final' => true,
                'orden' => 6,
            ],
        ];

        foreach ($estados as $estado) {
            DB::table('estados_denuncia')->insertOrIgnore(array_merge($estado, [
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]));
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaYCategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Map area names to get their IDs (areas already created by schema SQL)
        $areaMapping = [
            'Limpieza Pública' => 'LIM',
            'Seguridad Ciudadana' => 'SEG',
            'Obras Públicas' => 'OBR',
            'Parques y Jardines' => 'PAR',
            'Alumbrado Público' => 'SER',  // Map to Servicios Públicos
        ];

        $areaIds = [];
        foreach ($areaMapping as $nombre => $codigo) {
            $area = DB::table('areas')->where('nombre', $nombre)->first();
            if ($area) {
                $areaIds[$codigo] = $area->id;
            }
        }

        // Create "Tránsito y Transporte" if it doesn't exist
        $tránsito = DB::table('areas')->where('nombre', 'Tránsito y Transporte')->first();
        if (! $tránsito) {
            $traId = DB::table('areas')->insertGetId([
                'nombre' => 'Tránsito y Transporte',
                'codigo' => 'TRAN',
                'descripcion' => 'Gestión de tránsito y transporte público',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
            $areaIds['TRA'] = $traId;
        } else {
            $areaIds['TRA'] = $tránsito->id;
        }

        // Crear categorías vinculadas a áreas
        $categorias = [
            // Limpieza Pública
            [
                'nombre' => 'Basura Acumulada',
                'descripcion' => 'Acumulación de residuos sólidos en vía pública',
                'icono' => '🗑️',
                'color' => '#10B981',
                'area_default_id' => $areaIds['LIM'],
                'orden' => 1,
            ],
            [
                'nombre' => 'Contenedores Dañados',
                'descripcion' => 'Contenedores de basura rotos o en mal estado',
                'icono' => '♻️',
                'color' => '#059669',
                'area_default_id' => $areaIds['LIM'],
                'orden' => 2,
            ],
            [
                'nombre' => 'Limpieza de Calles',
                'descripcion' => 'Falta de limpieza en calles y avenidas',
                'icono' => '🧹',
                'color' => '#34D399',
                'area_default_id' => $areaIds['LIM'],
                'orden' => 3,
            ],
            // Seguridad Ciudadana
            [
                'nombre' => 'Falta de Iluminación',
                'descripcion' => 'Postes de luz apagados o dañados',
                'icono' => '💡',
                'color' => '#FBBF24',
                'area_default_id' => $areaIds['SER'],
                'orden' => 4,
            ],
            [
                'nombre' => 'Pandillaje',
                'descripcion' => 'Presencia de pandillas o grupos delictivos',
                'icono' => '👮',
                'color' => '#EF4444',
                'area_default_id' => $areaIds['SEG'],
                'orden' => 5,
            ],
            [
                'nombre' => 'Ruidos Molestos',
                'descripcion' => 'Contaminación sonora o ruidos excesivos',
                'icono' => '🔊',
                'color' => '#F59E0B',
                'area_default_id' => $areaIds['SEG'],
                'orden' => 6,
            ],
            // Obras Públicas
            [
                'nombre' => 'Baches en Pistas',
                'descripcion' => 'Huecos o deterioro en pistas y veredas',
                'icono' => '🚧',
                'color' => '#F97316',
                'area_default_id' => $areaIds['OBR'],
                'orden' => 7,
            ],
            [
                'nombre' => 'Semáforos Dañados',
                'descripcion' => 'Semáforos que no funcionan correctamente',
                'icono' => '🚦',
                'color' => '#EC4899',
                'area_default_id' => $areaIds['TRA'],
                'orden' => 8,
            ],
            [
                'nombre' => 'Veredas Rotas',
                'descripcion' => 'Veredas en mal estado o rotas',
                'icono' => '🚶',
                'color' => '#8B5CF6',
                'area_default_id' => $areaIds['OBR'],
                'orden' => 9,
            ],
            // Parques y Jardines
            [
                'nombre' => 'Áreas Verdes Descuidadas',
                'descripcion' => 'Parques y jardines sin mantenimiento',
                'icono' => '🌳',
                'color' => '#22C55E',
                'area_default_id' => $areaIds['PAR'],
                'orden' => 10,
            ],
            [
                'nombre' => 'Juegos Infantiles Dañados',
                'descripcion' => 'Juegos en parques rotos o peligrosos',
                'icono' => '🎪',
                'color' => '#06B6D4',
                'area_default_id' => $areaIds['PAR'],
                'orden' => 11,
            ],
            // Servicios Públicos
            [
                'nombre' => 'Fugas de Agua',
                'descripcion' => 'Fugas o roturas en tuberías de agua',
                'icono' => '💧',
                'color' => '#3B82F6',
                'area_default_id' => $areaIds['SER'],
                'orden' => 12,
            ],
            // Tránsito
            [
                'nombre' => 'Señalización Deficiente',
                'descripcion' => 'Falta de señales de tránsito o en mal estado',
                'icono' => '⚠️',
                'color' => '#DC2626',
                'area_default_id' => $areaIds['TRA'],
                'orden' => 13,
            ],
            [
                'nombre' => 'Estacionamiento Indebido',
                'descripcion' => 'Vehículos estacionados en zonas prohibidas',
                'icono' => '🚗',
                'color' => '#9333EA',
                'area_default_id' => $areaIds['TRA'],
                'orden' => 14,
            ],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias_denuncia')->insertOrIgnore(array_merge($categoria, [
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]));
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistritoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distritos = [
            'Ate', 'Barranco', 'Breña', 'Carabayllo', 'Chaclacayo',
            'Chorrillos', 'Cieneguilla', 'Comas', 'El Agustino', 'Independencia',
            'Jesús María', 'La Molina', 'La Victoria', 'Lince', 'Los Olivos',
            'Lurigancho', 'Lurín', 'Magdalena del Mar', 'Miraflores', 'Pachacámac',
            'Pucusana', 'Pueblo Libre', 'Puente Piedra', 'Punta Hermosa', 'Punta Negra',
            'Rímac', 'San Bartolo', 'San Borja', 'San Isidro', 'San Juan de Lurigancho',
            'San Juan de Miraflores', 'San Luis', 'San Martín de Porres', 'San Miguel',
            'Santa Anita', 'Santa María del Mar', 'Santa Rosa', 'Santiago de Surco',
            'Surquillo', 'Villa El Salvador', 'Villa María del Triunfo', 'Lima Cercado',
        ];

        foreach ($distritos as $index => $nombre) {
            DB::table('distritos')->insertOrIgnore([
                'nombre' => $nombre,
                'codigo' => strtoupper(substr(str_replace(' ', '', $nombre), 0, 3)).str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        }
    }
}

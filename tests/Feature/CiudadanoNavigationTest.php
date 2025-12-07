<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CiudadanoNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $ciudadano;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles
        DB::table('roles')->insert([
            ['nombre' => 'ciudadano', 'descripcion' => 'Ciudadano', 'creado_en' => now(), 'actualizado_en' => now()],
        ]);

        // Crear usuario ciudadano
        $this->ciudadano = Usuario::create([
            'nombre' => 'Juan',
            'apellido' => 'Ciudadano',
            'email' => 'ciudadano@test.com',
            'dni' => '11111111',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        DB::table('rol_usuario')->insert([
            'usuario_id' => $this->ciudadano->id,
            'rol_id' => $roleCiudadanoId,
            'model_type' => 'App\\Models\\Usuario',
        ]);
    }

    /** @test */
    public function test_ciudadano_can_access_dashboard()
    {
        $response = $this->actingAs($this->ciudadano)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ciudadano/Dashboard'));
    }

    /** @test */
    public function test_ciudadano_can_access_mis_denuncias()
    {
        $response = $this->actingAs($this->ciudadano)->get('/mis-denuncias');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ciudadano/Denuncias/Index'));
    }

    /** @test */
    public function test_ciudadano_can_access_nueva_denuncia()
    {
        // Crear datos necesarios
        $this->createRequiredData();

        $response = $this->actingAs($this->ciudadano)->get('/denuncias/nueva');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ciudadano/Denuncias/Create'));
    }

    /** @test */
    public function test_ciudadano_can_access_notificaciones()
    {
        $response = $this->actingAs($this->ciudadano)->get('/notificaciones');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ciudadano/Notificaciones/Index'));
    }

    /** @test */
    public function test_ciudadano_can_access_perfil()
    {
        $response = $this->actingAs($this->ciudadano)->get('/perfil');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ciudadano/Profile/Edit'));
    }

    /** @test */
    public function test_ciudadano_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->ciudadano)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_ciudadano_cannot_access_funcionario_routes()
    {
        $response = $this->actingAs($this->ciudadano)->get('/funcionario/dashboard');

        $response->assertStatus(403);
    }

    private function createRequiredData()
    {
        // Crear área
        DB::table('areas')->insert([
            'nombre' => 'Área Test',
            'codigo' => 'TEST',
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        $areaId = DB::table('areas')->where('codigo', 'TEST')->value('id');

        // Crear categoría
        DB::table('categorias_denuncia')->insert([
            'nombre' => 'Categoría Test',
            'area_default_id' => $areaId,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        // Crear distrito
        DB::table('distritos')->insert([
            'nombre' => 'Distrito Test',
            'codigo' => 'DT01',
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        // Crear prioridad
        DB::table('prioridades_denuncia')->insert([
            'nombre' => 'Media',
            'codigo' => 'MED',
            'nivel' => 2,
            'sla_horas' => 72,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        // Crear estado
        DB::table('estados_denuncia')->insert([
            'nombre' => 'Registrada',
            'codigo' => 'REG',
            'es_inicial' => true,
            'es_final' => false,
            'orden' => 1,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    }
}

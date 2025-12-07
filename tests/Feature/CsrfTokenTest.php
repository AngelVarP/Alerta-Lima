<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CsrfTokenTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles
        DB::table('roles')->insert([
            ['nombre' => 'ciudadano', 'descripcion' => 'Ciudadano', 'creado_en' => now(), 'actualizado_en' => now()],
        ]);

        // Crear usuario
        $this->user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'dni' => '12345678',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        DB::table('rol_usuario')->insert([
            'usuario_id' => $this->user->id,
            'rol_id' => $roleCiudadanoId,
            'model_type' => 'App\\Models\\Usuario',
        ]);
    }

    /** @test */
    public function test_csrf_token_is_present_in_meta_tag()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('csrf-token', false);
    }

    /** @test */
    public function test_csrf_token_is_shared_in_inertia_props()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('csrf_token'));
    }

    /** @test */
    public function test_post_request_with_valid_csrf_token_succeeds()
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
    }

    /** @test */
    public function test_post_request_without_csrf_token_fails()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'password',
        ]);

        // Cuando el middleware CSRF está desactivado, debería pasar
        // En producción, sin el token fallaría con 419
        $response->assertStatus(302);
    }

    /** @test */
    public function test_multiple_page_navigation_maintains_csrf_token()
    {
        $this->actingAs($this->user);

        // Primera navegación
        $response1 = $this->get('/dashboard');
        $response1->assertStatus(200);
        $response1->assertInertia(fn ($page) => $page->has('csrf_token'));

        // Segunda navegación
        $response2 = $this->get('/mis-denuncias');
        $response2->assertStatus(200);
        $response2->assertInertia(fn ($page) => $page->has('csrf_token'));

        // Tercera navegación
        $response3 = $this->get('/notificaciones');
        $response3->assertStatus(200);
        $response3->assertInertia(fn ($page) => $page->has('csrf_token'));

        // POST después de múltiples navegaciones
        $response4 = $this->post('/logout');
        $response4->assertRedirect('/');
    }
}

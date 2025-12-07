<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles
        DB::table('roles')->insert([
            ['nombre' => 'ciudadano', 'descripcion' => 'Ciudadano', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'funcionario', 'descripcion' => 'Funcionario', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'supervisor', 'descripcion' => 'Supervisor', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'admin', 'descripcion' => 'Administrador', 'creado_en' => now(), 'actualizado_en' => now()],
        ]);
    }

    /** @test */
    public function test_login_page_can_be_rendered()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_can_login_with_email()
    {
        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'dni' => '12345678',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        DB::table('rol_usuario')->insert([
            'usuario_id' => $user->id,
            'rol_id' => $roleCiudadanoId,
            'model_type' => 'App\\Models\\Usuario',
        ]);

        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_user_can_login_with_dni()
    {
        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'dni' => '87654321',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $roleCiudadanoId = DB::table('roles')->where('nombre', 'ciudadano')->value('id');
        DB::table('rol_usuario')->insert([
            'usuario_id' => $user->id,
            'rol_id' => $roleCiudadanoId,
            'model_type' => 'App\\Models\\Usuario',
        ]);

        $response = $this->post('/login', [
            'login' => '87654321',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'dni' => '12345678',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function test_user_can_logout()
    {
        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'dni' => '12345678',
            'password_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

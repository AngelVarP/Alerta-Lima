<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('apellido', 150)->nullable();
            $table->string('email', 150)->unique();
            $table->string('dni', 15)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();

            // Relación con área (para funcionarios)
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();

            // Autenticación estándar de Laravel
            $table->string('password'); // CORRECCIÓN: Usar nombre estándar
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();

            // Campos para 2FA (Laravel Fortify compatible)
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // Control de acceso y seguridad
            $table->smallInteger('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->timestamp('ultimo_login')->nullable();

            $table->boolean('activo')->default(true);
            $table->softDeletes(); // CORRECCIÓN: Usar softDeletes() estándar
            $table->timestamps(); // CORRECCIÓN: Usar timestamps() estándar

            // Índices para rendimiento
            $table->index('email');
            $table->index('dni');
            $table->index('activo');
            $table->index('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

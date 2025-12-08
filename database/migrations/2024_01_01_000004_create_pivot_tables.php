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
        // Tabla pivote: usuario <-> rol
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->string('model_type')->default('App\\Models\\Usuario');
            $table->timestamp('asignado_en')->useCurrent();

            $table->primary(['usuario_id', 'rol_id']);
        });

        // Tabla pivote: rol <-> permiso
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();

            $table->primary(['rol_id', 'permiso_id']);
        });

        // Tabla pivote: usuario <-> permiso (permisos directos)
        Schema::create('permiso_usuario', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->string('model_type')->default('App\\Models\\Usuario');

            $table->primary(['usuario_id', 'permiso_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permiso_usuario');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('rol_usuario');
    }
};

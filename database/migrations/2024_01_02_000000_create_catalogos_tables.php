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
        // Categorías de denuncia
        Schema::create('categorias_denuncia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion')->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->foreignId('area_default_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['activo', 'orden']);
        });

        // Estados de denuncia
        Schema::create('estados_denuncia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('codigo', 20)->unique();
            $table->string('descripcion')->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('es_inicial')->default(false);
            $table->boolean('es_final')->default(false);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Prioridades de denuncia
        Schema::create('prioridades_denuncia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('codigo', 10)->unique();
            $table->string('descripcion')->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sla_horas')->default(72);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Distritos
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('codigo', 10)->nullable();
            $table->string('provincia', 100)->default('Lima');
            $table->string('departamento', 100)->default('Lima');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });

        // Transiciones de estado válidas
        Schema::create('transiciones_estado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_origen_id')->constrained('estados_denuncia')->cascadeOnDelete();
            $table->foreignId('estado_destino_id')->constrained('estados_denuncia')->cascadeOnDelete();
            $table->string('nombre', 100)->nullable();
            $table->boolean('requiere_motivo')->default(false);
            $table->boolean('requiere_asignacion')->default(false);
            $table->boolean('solo_admin')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['estado_origen_id', 'estado_destino_id']);
            $table->index(['estado_origen_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transiciones_estado');
        Schema::dropIfExists('distritos');
        Schema::dropIfExists('prioridades_denuncia');
        Schema::dropIfExists('estados_denuncia');
        Schema::dropIfExists('categorias_denuncia');
    }
};

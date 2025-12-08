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
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();

            // Código único (generado por Observer, NO por trigger)
            $table->string('codigo', 50)->unique();

            // Relaciones
            $table->foreignId('ciudadano_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('asignado_a_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_denuncia')->restrictOnDelete();
            $table->foreignId('prioridad_id')->constrained('prioridades_denuncia')->restrictOnDelete();
            $table->foreignId('estado_id')->constrained('estados_denuncia')->restrictOnDelete();
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->nullOnDelete();

            // Datos de la denuncia
            $table->string('titulo', 200);
            $table->text('descripcion'); // CORRECCIÓN: SIN cifrar para permitir búsquedas

            // Ubicación
            $table->string('direccion')->nullable();
            $table->string('referencia')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            // Metadata
            $table->boolean('es_anonima')->default(false);
            $table->ipAddress('ip_origen')->nullable();
            $table->string('user_agent', 500)->nullable();

            // Fechas
            $table->timestamp('registrada_en')->useCurrent();
            $table->timestamp('fecha_limite_sla')->nullable(); // Calculado por Observer
            $table->timestamp('cerrada_en')->nullable();

            $table->softDeletes(); // CORRECCIÓN: Usar softDeletes estándar
            $table->timestamps(); // CORRECCIÓN: Usar timestamps estándar

            // Índices optimizados para dashboards
            $table->index('codigo');
            $table->index('estado_id');
            $table->index('categoria_id');
            $table->index('prioridad_id');
            $table->index('distrito_id');
            $table->index('area_id');
            $table->index('ciudadano_id');
            $table->index('asignado_a_id');
            $table->index('registrada_en');
            $table->index(['fecha_limite_sla', 'cerrada_en']);
            $table->index(['estado_id', 'registrada_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};

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
        // Historial de cambios de estado
        Schema::create('historial_estados_denuncia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->cascadeOnDelete();
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estados_denuncia')->restrictOnDelete();
            $table->foreignId('estado_nuevo_id')->constrained('estados_denuncia')->restrictOnDelete();
            $table->foreignId('cambiado_por_id')->constrained('usuarios')->restrictOnDelete();
            $table->text('motivo_cambio')->nullable();
            $table->string('tiempo_en_estado')->nullable(); // Interval como string (ej: "2 days 3 hours")
            $table->timestamps();

            $table->index('denuncia_id');
            $table->index('created_at');
        });

        // Historial de asignaciones
        Schema::create('historial_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->cascadeOnDelete();
            $table->foreignId('asignado_de_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('asignado_a_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('area_anterior_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('area_nueva_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('asignado_por_id')->constrained('usuarios')->restrictOnDelete();
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index('denuncia_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_asignaciones');
        Schema::dropIfExists('historial_estados_denuncia');
    }
};

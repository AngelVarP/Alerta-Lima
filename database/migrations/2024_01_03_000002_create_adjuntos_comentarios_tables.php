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
        // Adjuntos (evidencias)
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->cascadeOnDelete();
            $table->foreignId('subido_por_id')->constrained('usuarios')->restrictOnDelete();

            $table->string('nombre_original');
            $table->string('nombre_almacenado');
            $table->string('ruta_almacenamiento', 500);
            $table->string('tipo_mime', 100);
            $table->bigInteger('tamano_bytes');

            // CORRECCIÓN: Cifrado solo para archivos críticos (implementar según necesidad)
            $table->boolean('cifrado')->default(false);
            $table->string('hash_archivo', 64)->nullable(); // SHA-256 para integridad

            $table->softDeletes();
            $table->timestamps();

            $table->index('denuncia_id');
        });

        // Comentarios
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('comentario_padre_id')->nullable()->constrained('comentarios')->nullOnDelete();

            $table->boolean('es_interno')->default(false);
            $table->text('comentario');

            $table->softDeletes();
            $table->timestamps();

            $table->index('denuncia_id');
            $table->index('comentario_padre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('adjuntos');
    }
};

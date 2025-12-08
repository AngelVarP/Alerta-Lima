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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('denuncia_id')->nullable()->constrained('denuncias')->nullOnDelete();

            $table->string('tipo', 50);
            $table->enum('canal', ['email', 'sms', 'web', 'push']);
            $table->string('asunto', 200);
            $table->text('mensaje');
            $table->json('datos_extra')->nullable();

            $table->enum('estado', ['PENDIENTE', 'ENVIADA', 'FALLIDA', 'LEIDA'])->default('PENDIENTE');
            $table->smallInteger('intentos')->default(0);
            $table->smallInteger('max_intentos')->default(3);
            $table->timestamp('enviada_en')->nullable();
            $table->timestamp('leida_en')->nullable();
            $table->text('error_mensaje')->nullable();

            $table->timestamps();

            $table->index('usuario_id');
            $table->index('denuncia_id');
            $table->index('estado');
            $table->index(['estado', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};

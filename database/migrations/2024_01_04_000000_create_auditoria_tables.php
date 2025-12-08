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
        // Logs de auditoría inmutables
        Schema::create('registros_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->string('accion', 100);
            $table->string('tipo_entidad', 100)->nullable();
            $table->bigInteger('id_entidad')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();

            $table->ipAddress('ip_origen')->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index('usuario_id');
            $table->index('accion');
            $table->index(['tipo_entidad', 'id_entidad']);
            $table->index('created_at');
        });

        // Eventos de seguridad (WAF, intentos de ataque)
        Schema::create('eventos_seguridad', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_evento', 100);
            $table->enum('severidad', ['BAJA', 'MEDIA', 'ALTA', 'CRITICA']);
            $table->ipAddress('ip_origen')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->string('ruta_solicitud', 500)->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->text('payload_muestra')->nullable();
            $table->json('headers')->nullable();

            $table->boolean('bloqueado')->default(false);

            $table->timestamps();

            $table->index('tipo_evento');
            $table->index('severidad');
            $table->index('ip_origen');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_seguridad');
        Schema::dropIfExists('registros_auditoria');
    }
};

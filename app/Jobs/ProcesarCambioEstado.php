<?php

namespace App\Jobs;

use App\Models\Denuncia;
use App\Models\EstadoDenuncia;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcesarCambioEstado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Denuncia $denuncia,
        public EstadoDenuncia $estadoAnterior,
        public EstadoDenuncia $estadoNuevo,
        public Usuario $usuario
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Notificar al ciudadano sobre el cambio de estado
        $notificacion = Notificacion::crearNotificacion(
            $this->denuncia->ciudadano_id,
            'CAMBIO_ESTADO',
            'Actualización de tu denuncia',
            "Tu denuncia {$this->denuncia->codigo} cambió de {$this->estadoAnterior->nombre} a {$this->estadoNuevo->nombre}",
            $this->denuncia->id
        );

        // Encolar el envío de la notificación
        EnviarNotificacion::dispatch($notificacion);

        // Si el estado es final, registrar métricas adicionales
        if ($this->estadoNuevo->es_final) {
            // Aquí puedes agregar lógica para métricas, reportes, etc.
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnviarNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Notificacion $notificacion
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Marcar como enviada
        $this->notificacion->marcarComoEnviada();

        // Aquí puedes agregar lógica adicional para enviar emails, SMS, push notifications, etc.
        // Por ejemplo:
        // if ($this->notificacion->canal === 'EMAIL') {
        //     Mail::to($this->notificacion->usuario->email)->send(new NotificacionMail($this->notificacion));
        // }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->notificacion->update([
            'estado' => 'FALLIDA',
            'error_mensaje' => $exception->getMessage(),
        ]);
    }
}

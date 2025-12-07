<?php

namespace App\Jobs;

use App\Models\Denuncia;
use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarSLAVencido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Buscar denuncias con SLA vencido
        $denunciasVencidas = Denuncia::slaPendiente()
            ->with(['asignadoA', 'area'])
            ->get();

        foreach ($denunciasVencidas as $denuncia) {
            // Notificar al funcionario asignado
            if ($denuncia->asignado_a_id) {
                $notificacion = Notificacion::crearNotificacion(
                    $denuncia->asignado_a_id,
                    'SLA_VENCIDO',
                    'SLA vencido',
                    "La denuncia {$denuncia->codigo} ha excedido su tiempo límite de SLA",
                    $denuncia->id
                );

                EnviarNotificacion::dispatch($notificacion);
            }

            // Notificar al supervisor del área
            $supervisor = \App\Models\Usuario::where('area_id', $denuncia->area_id)
                ->where('activo', true)
                ->whereHas('roles', fn ($q) => $q->where('nombre', 'supervisor'))
                ->first();

            if ($supervisor) {
                $notificacion = Notificacion::crearNotificacion(
                    $supervisor->id,
                    'SLA_VENCIDO',
                    'SLA vencido en tu área',
                    "La denuncia {$denuncia->codigo} ha excedido su tiempo límite de SLA",
                    $denuncia->id
                );

                EnviarNotificacion::dispatch($notificacion);
            }
        }
    }
}

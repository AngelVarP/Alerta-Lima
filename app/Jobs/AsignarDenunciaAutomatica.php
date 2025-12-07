<?php

namespace App\Jobs;

use App\Models\Denuncia;
use App\Models\EstadoDenuncia;
use App\Models\HistorialAsignacion;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AsignarDenunciaAutomatica implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Denuncia $denuncia
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Si ya está asignada, no hacer nada
        if ($this->denuncia->asignado_a_id) {
            return;
        }

        // Buscar funcionario con menos denuncias activas en el área
        $funcionario = Usuario::where('area_id', $this->denuncia->area_id)
            ->where('activo', true)
            ->whereHas('roles', fn ($q) => $q->where('nombre', 'funcionario'))
            ->withCount([
                'denunciasAsignadas' => fn ($q) => $q->whereNull('cerrada_en'),
            ])
            ->orderBy('denuncias_asignadas_count', 'asc')
            ->first();

        if (! $funcionario) {
            // No hay funcionarios disponibles en el área
            return;
        }

        // Asignar la denuncia
        $this->denuncia->update(['asignado_a_id' => $funcionario->id]);

        // Registrar historial
        HistorialAsignacion::create([
            'denuncia_id' => $this->denuncia->id,
            'asignado_a_id' => $funcionario->id,
            'asignado_por_id' => null, // Sistema
            'motivo' => 'Asignación automática del sistema',
        ]);

        // Cambiar a "En Proceso"
        $estadoEnProceso = EstadoDenuncia::where('codigo', 'PRO')->first();
        if ($estadoEnProceso && $this->denuncia->estado->codigo === 'REG') {
            $this->denuncia->cambiarEstado($estadoEnProceso, $funcionario, 'Asignación automática');
        }

        // Notificar al funcionario
        $notificacion = Notificacion::crearNotificacion(
            $funcionario->id,
            'ASIGNACION',
            'Nueva denuncia asignada',
            "Se te ha asignado automáticamente la denuncia {$this->denuncia->codigo}",
            $this->denuncia->id
        );

        EnviarNotificacion::dispatch($notificacion);
    }
}

<?php

namespace App\Services;

use App\Models\Denuncia;
use App\Models\Notificacion;
use App\Models\Usuario;

class NotificacionService
{
    /**
     * Notificar cambio de estado al ciudadano
     */
    public function notificarCambioEstado(Denuncia $denuncia, string $nuevoEstado): void
    {
        Notificacion::create([
            'usuario_id' => $denuncia->ciudadano_id,
            'denuncia_id' => $denuncia->id,
            'tipo' => 'CAMBIO_ESTADO',
            'titulo' => 'Actualización de tu denuncia',
            'mensaje' => "Tu denuncia {$denuncia->codigo} cambió al estado: {$nuevoEstado}",
            'url' => route('denuncias.show', $denuncia->id),
        ]);
    }

    /**
     * Notificar nueva asignación al funcionario
     */
    public function notificarAsignacion(Denuncia $denuncia, Usuario $funcionario): void
    {
        Notificacion::create([
            'usuario_id' => $funcionario->id,
            'denuncia_id' => $denuncia->id,
            'tipo' => 'ASIGNACION',
            'titulo' => 'Nueva denuncia asignada',
            'mensaje' => "Se te ha asignado la denuncia {$denuncia->codigo}",
            'url' => route('funcionario.denuncias.show', $denuncia->id),
        ]);
    }

    /**
     * Notificar reasignación
     */
    public function notificarReasignacion(Denuncia $denuncia, Usuario $funcionarioNuevo, ?Usuario $funcionarioAnterior = null): void
    {
        // Notificar al nuevo funcionario
        Notificacion::create([
            'usuario_id' => $funcionarioNuevo->id,
            'denuncia_id' => $denuncia->id,
            'tipo' => 'REASIGNACION',
            'titulo' => 'Denuncia reasignada',
            'mensaje' => "Se te ha reasignado la denuncia {$denuncia->codigo}",
            'url' => route('funcionario.denuncias.show', $denuncia->id),
        ]);

        // Notificar al funcionario anterior si existe
        if ($funcionarioAnterior) {
            Notificacion::create([
                'usuario_id' => $funcionarioAnterior->id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'REASIGNACION',
                'titulo' => 'Denuncia reasignada',
                'mensaje' => "La denuncia {$denuncia->codigo} ha sido reasignada a otro funcionario",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        }
    }

    /**
     * Notificar nuevo comentario
     */
    public function notificarNuevoComentario(Denuncia $denuncia, Usuario $autor): void
    {
        // Si el comentario es de un funcionario, notificar al ciudadano
        if ($denuncia->ciudadano_id !== $autor->id) {
            Notificacion::create([
                'usuario_id' => $denuncia->ciudadano_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'NUEVO_COMENTARIO',
                'titulo' => 'Nuevo comentario en tu denuncia',
                'mensaje' => "Hay un nuevo comentario en tu denuncia {$denuncia->codigo}",
                'url' => route('denuncias.show', $denuncia->id),
            ]);
        }

        // Si el comentario es del ciudadano, notificar al funcionario asignado
        if ($denuncia->ciudadano_id === $autor->id && $denuncia->asignado_a_id) {
            Notificacion::create([
                'usuario_id' => $denuncia->asignado_a_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'NUEVO_COMENTARIO',
                'titulo' => 'Nuevo comentario del ciudadano',
                'mensaje' => "El ciudadano agregó un comentario en la denuncia {$denuncia->codigo}",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        }
    }

    /**
     * Notificar cambio de prioridad
     */
    public function notificarCambioPrioridad(Denuncia $denuncia, string $nuevaPrioridad): void
    {
        // Notificar al funcionario asignado si existe
        if ($denuncia->asignado_a_id) {
            Notificacion::create([
                'usuario_id' => $denuncia->asignado_a_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'CAMBIO_PRIORIDAD',
                'titulo' => 'Cambio de prioridad',
                'mensaje' => "La denuncia {$denuncia->codigo} cambió a prioridad {$nuevaPrioridad}",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        }
    }

    /**
     * Notificar SLA vencido
     */
    public function notificarSLAVencido(Denuncia $denuncia): void
    {
        // Notificar al funcionario asignado
        if ($denuncia->asignado_a_id) {
            Notificacion::create([
                'usuario_id' => $denuncia->asignado_a_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'SLA_VENCIDO',
                'titulo' => '⚠️ SLA Vencido',
                'mensaje' => "El SLA de la denuncia {$denuncia->codigo} ha vencido",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        }

        // Notificar al supervisor del área
        if ($denuncia->area) {
            $supervisores = Usuario::where('area_id', $denuncia->area_id)
                ->whereHas('roles', fn ($q) => $q->where('nombre', 'supervisor'))
                ->get();

            foreach ($supervisores as $supervisor) {
                Notificacion::create([
                    'usuario_id' => $supervisor->id,
                    'denuncia_id' => $denuncia->id,
                    'tipo' => 'SLA_VENCIDO',
                    'titulo' => '⚠️ SLA Vencido en tu área',
                    'mensaje' => "El SLA de la denuncia {$denuncia->codigo} ha vencido",
                    'url' => route('supervisor.denuncias.show', $denuncia->id),
                ]);
            }
        }
    }

    /**
     * Notificar SLA próximo a vencer (24 horas antes)
     */
    public function notificarSLAProximoVencer(Denuncia $denuncia): void
    {
        if ($denuncia->asignado_a_id) {
            Notificacion::create([
                'usuario_id' => $denuncia->asignado_a_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'SLA_PROXIMO_VENCER',
                'titulo' => '⏰ SLA próximo a vencer',
                'mensaje' => "El SLA de la denuncia {$denuncia->codigo} vence pronto",
                'url' => route('funcionario.denuncias.show', $denuncia->id),
            ]);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida(Notificacion $notificacion): void
    {
        $notificacion->update(['leida_en' => now()]);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasComoLeidas(Usuario $usuario): void
    {
        Notificacion::where('usuario_id', $usuario->id)
            ->whereNull('leida_en')
            ->update(['leida_en' => now()]);
    }
}

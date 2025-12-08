<?php

namespace App\Observers;

use App\Models\Usuario;
use App\Models\EventoSeguridad;

/**
 * Observer de Usuario
 *
 * Registra eventos de seguridad importantes
 */
class UsuarioObserver
{
    /**
     * Handle the Usuario "created" event.
     */
    public function created(Usuario $usuario): void
    {
        // Registrar evento de seguridad: Nuevo usuario creado
        EventoSeguridad::create([
            'tipo_evento' => 'USUARIO_CREADO',
            'severidad' => 'BAJA',
            'ip_origen' => request()->ip(),
            'usuario_id' => $usuario->id,
            'ruta_solicitud' => request()->fullUrl(),
            'metodo_http' => request()->method(),
            'bloqueado' => false,
        ]);
    }

    /**
     * Handle the Usuario "updated" event.
     */
    public function updated(Usuario $usuario): void
    {
        // Si se cambió el email o se desactivó, registrar
        if ($usuario->isDirty('email') || $usuario->isDirty('activo')) {
            $severidad = $usuario->activo ? 'BAJA' : 'MEDIA';

            EventoSeguridad::create([
                'tipo_evento' => $usuario->activo ? 'USUARIO_ACTUALIZADO' : 'USUARIO_DESACTIVADO',
                'severidad' => $severidad,
                'ip_origen' => request()->ip(),
                'usuario_id' => $usuario->id,
                'ruta_solicitud' => request()->fullUrl(),
                'metodo_http' => request()->method(),
                'bloqueado' => false,
            ]);
        }
    }

    /**
     * Handle the Usuario "deleting" event.
     */
    public function deleting(Usuario $usuario): void
    {
        // Registrar eliminación (soft delete)
        EventoSeguridad::create([
            'tipo_evento' => 'USUARIO_ELIMINADO',
            'severidad' => 'ALTA',
            'ip_origen' => request()->ip(),
            'usuario_id' => $usuario->id,
            'ruta_solicitud' => request()->fullUrl(),
            'metodo_http' => request()->method(),
            'bloqueado' => false,
        ]);
    }
}

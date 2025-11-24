<?php

namespace App\Policies;

use App\Models\Denuncia;
use App\Models\Usuario;

class DenunciaPolicy
{
    /**
     * Determina si el usuario puede ver la denuncia.
     */
    public function ver(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede ver todo
        if ($usuario->esAdmin()) {
            return true;
        }

        // Ciudadano puede ver sus propias denuncias
        if ($denuncia->ciudadano_id === $usuario->id) {
            return true;
        }

        // Funcionario puede ver denuncias de su área
        if ($usuario->esFuncionario() && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        // Supervisor puede ver todas las denuncias
        if ($usuario->tieneRol('supervisor')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede editar la denuncia.
     */
    public function editar(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede editar todo
        if ($usuario->esAdmin()) {
            return true;
        }

        // Ciudadano puede editar su denuncia solo si está en estado inicial
        if ($denuncia->ciudadano_id === $usuario->id && $denuncia->estado->es_inicial) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede cambiar el estado.
     */
    public function cambiarEstado(Usuario $usuario, Denuncia $denuncia): bool
    {
        if ($usuario->esAdmin()) {
            return true;
        }

        // Funcionario asignado puede cambiar estado
        if ($denuncia->asignado_a_id === $usuario->id) {
            return true;
        }

        // Supervisor puede cambiar estado
        if ($usuario->tieneRol('supervisor')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede asignar la denuncia.
     */
    public function asignar(Usuario $usuario, Denuncia $denuncia): bool
    {
        if ($usuario->esAdmin()) {
            return true;
        }

        if ($usuario->tienePermiso('asignar_denuncia') || $usuario->tienePermiso('reasignar_denuncia')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar la denuncia.
     */
    public function eliminar(Usuario $usuario, Denuncia $denuncia): bool
    {
        return $usuario->esAdmin() || $usuario->tienePermiso('eliminar_denuncia');
    }
}

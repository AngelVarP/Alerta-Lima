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

    /**
     * Determina si el funcionario puede ver la denuncia
     */
    public function verComoFuncionario(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede ver todo
        if ($usuario->esAdmin()) {
            return true;
        }

        // Funcionario puede ver denuncias de su área
        if ($usuario->esFuncionario() && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el supervisor puede ver la denuncia
     */
    public function verComoSupervisor(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede ver todo
        if ($usuario->esAdmin()) {
            return true;
        }

        // Supervisor puede ver denuncias de su área
        if ($usuario->tieneRol('supervisor') && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede comentar en la denuncia
     */
    public function comentar(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede comentar en cualquier denuncia
        if ($usuario->esAdmin()) {
            return true;
        }

        // Ciudadano puede comentar en sus propias denuncias
        if ($denuncia->ciudadano_id === $usuario->id) {
            return true;
        }

        // Funcionario puede comentar en denuncias de su área
        if ($usuario->esFuncionario() && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede cambiar la prioridad
     */
    public function cambiarPrioridad(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Solo admin y supervisores pueden cambiar prioridad
        if ($usuario->esAdmin()) {
            return true;
        }

        if ($usuario->tieneRol('supervisor') && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede reasignar la denuncia
     */
    public function reasignar(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede reasignar cualquier denuncia
        if ($usuario->esAdmin()) {
            return true;
        }

        // Supervisor puede reasignar denuncias de su área
        if ($usuario->tieneRol('supervisor') && $denuncia->area_id === $usuario->area_id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver adjuntos de la denuncia
     */
    public function verAdjuntos(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Reutilizar la lógica de ver
        return $this->ver($usuario, $denuncia);
    }

    /**
     * Determina si el usuario puede agregar adjuntos
     */
    public function agregarAdjuntos(Usuario $usuario, Denuncia $denuncia): bool
    {
        // Admin puede agregar adjuntos
        if ($usuario->esAdmin()) {
            return true;
        }

        // Ciudadano puede agregar adjuntos a su propia denuncia si no está cerrada
        if ($denuncia->ciudadano_id === $usuario->id && !$denuncia->estado->es_final) {
            return true;
        }

        // Funcionario asignado puede agregar adjuntos
        if ($denuncia->asignado_a_id === $usuario->id) {
            return true;
        }

        return false;
    }
}

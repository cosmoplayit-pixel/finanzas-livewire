<?php

namespace App\Policies;

use App\Models\Factura;
use App\Models\User;

/**
 * Policy de autorización para Facturas.
 *
 * Responsabilidades:
 * - Controlar acceso a listados y creación
 * - Autorizar acciones sensibles como el registro de pagos
 * - Reforzar seguridad multi-empresa a nivel de autorización
 *
 * Nota:
 * - La Policy NO reemplaza las validaciones del Service
 * - Actúa como una primera capa de control
 */
class FacturaPolicy
{
    /**
     * Permite ver el listado de facturas.
     * Se basa únicamente en permisos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('facturas.view');
    }

    /**
     * Permite crear nuevas facturas.
     * Se basa únicamente en permisos.
     */
    public function create(User $user): bool
    {
        return $user->can('facturas.create');
    }

    /**
     * Autoriza el registro de pagos sobre una factura.
     *
     * Reglas:
     * - El usuario debe tener el permiso correspondiente
     * - La factura debe pertenecer a la empresa del usuario
     */
    public function pay(User $user, Factura $factura): bool
    {
        /**
         * Verificación de permiso explícito.
         */
        if (!$user->can('facturas.pay')) {
            return false;
        }

        /**
         * Seguridad multi-empresa:
         * el usuario solo puede operar sobre facturas
         * pertenecientes a su empresa.
         */
        $empresaId = $user->empresa_id;

        if (!$empresaId) {
            return false;
        }

        /**
         * Se carga el proyecto para validar la empresa asociada.
         */
        $factura->loadMissing(['proyecto']);

        return (int) ($factura->proyecto?->empresa_id ?? 0) === (int) $empresaId;
    }
}

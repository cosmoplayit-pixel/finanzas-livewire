<?php

namespace App\Policies;

use App\Models\FacturaPago;
use App\Models\User;

/**
 * Policy de autorización para Pagos de Facturas.
 *
 * Responsabilidades:
 * - Autorizar la eliminación de pagos
 * - Reforzar el aislamiento multi-empresa
 *
 * Nota:
 * - Esta Policy solo controla autorización
 * - Las reglas financieras viven en el Service
 */
class FacturaPagoPolicy
{
    /**
     * Autoriza la eliminación de un pago.
     *
     * Reglas:
     * - El usuario debe tener el permiso correspondiente
     * - El pago debe pertenecer a la empresa del usuario
     */
    public function delete(User $user, FacturaPago $pago): bool
    {
        /**
         * Verificación de permiso explícito.
         */
        if (!$user->can('facturas.pay')) {
            return false;
        }

        /**
         * Seguridad multi-empresa:
         * el usuario solo puede eliminar pagos
         * pertenecientes a su empresa.
         */
        $empresaId = $user->empresa_id;

        if (!$empresaId) {
            return false;
        }

        /**
         * Se cargan las relaciones necesarias para
         * validar la empresa del proyecto asociado.
         */
        $pago->loadMissing(['factura.proyecto']);

        return (int) ($pago->factura?->proyecto?->empresa_id ?? 0) === (int) $empresaId;
    }
}

<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Proyecto;
use App\Models\User;
use DomainException;

/**
 * Servicio de dominio para la creación de Facturas.
 *
 * Responsabilidades:
 * - Validar coherencia Proyecto ↔ Entidad
 * - Validar pertenencia a Empresa (multi-tenant)
 * - Calcular retención según configuración del Proyecto
 * - Garantizar reglas financieras básicas antes de persistir
 *
 * Nota:
 * - No maneja UI
 * - No maneja permisos de vista
 * - Solo reglas de negocio + persistencia
 */
class FacturaService
{
    /**
     * Crea una factura aplicando reglas de negocio y seguridad.
     *
     * @param  array $data  Datos validados desde la capa UI (Livewire / Controller)
     * @param  User  $user  Usuario autenticado que intenta crear la factura
     * @return Factura
     *
     * @throws DomainException Si se viola alguna regla de negocio
     */
    public function crearFactura(array $data, User $user): Factura
    {
        /**
         * Se obtiene el proyecto con los campos mínimos necesarios.
         * Esto evita cargar datos innecesarios y mantiene el servicio eficiente.
         */
        $proyecto = Proyecto::query()
            ->select(['id', 'empresa_id', 'entidad_id', 'retencion'])
            ->findOrFail($data['proyecto_id']);

        /**
         * Regla: el proyecto debe pertenecer a la entidad seleccionada.
         * Evita inconsistencias cruzadas entre Entidades y Proyectos.
         */
        if ((int) $proyecto->entidad_id !== (int) $data['entidad_id']) {
            throw new DomainException('El proyecto no pertenece a la entidad seleccionada.');
        }

        /**
         * Control multi-empresa:
         * El usuario solo puede crear facturas en proyectos
         * que pertenezcan a su empresa.
         *
         * Si por error se expone un proyecto de otra empresa,
         * el sistema debe bloquear la operación.
         */
        $empresaId = $user->empresa_id;

        if ($empresaId === null || (int) $proyecto->empresa_id !== (int) $empresaId) {
            throw new DomainException(
                'No tienes permiso para crear facturas en proyectos de otra empresa.',
            );
        }

        /**
         * Normalización del monto facturado.
         * Se redondea a 2 decimales para evitar inconsistencias de coma flotante.
         */
        $facturado = round((float) $data['monto_facturado'], 2);

        /**
         * Cálculo de retención según el porcentaje definido en el proyecto.
         * Si el proyecto no tiene retención, el valor es 0.
         */
        $porcentajeRetencion = (float) ($proyecto->retencion ?? 0);
        $retencionMonto = 0.0;

        if ($porcentajeRetencion > 0) {
            $retencionMonto = round($facturado * ($porcentajeRetencion / 100), 2);
        }

        /**
         * Regla financiera crítica:
         * La retención nunca puede ser igual o mayor al monto facturado.
         */
        if ($retencionMonto >= $facturado) {
            throw new DomainException(
                'La retención no puede ser igual o mayor al monto facturado.',
            );
        }

        /**
         * Persistencia final de la factura.
         * La factura siempre inicia activa.
         */
        return Factura::create([
            'proyecto_id' => $data['proyecto_id'],
            'numero' => $data['numero'] ?? null,
            'fecha_emision' => $data['fecha_emision'] ?? null,
            'monto_facturado' => $facturado,
            'retencion' => $retencionMonto,
            'observacion' => $data['observacion_factura'] ?? null,
            'foto_comprobante' => $data['foto_comprobante'] ?? null,
            'active' => true,
        ]);
    }
}

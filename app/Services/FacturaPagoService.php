<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\Factura;
use App\Models\FacturaPago;
use App\Models\User;
use App\Services\FacturaFinance;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de dominio para la gestión de Pagos de Facturas.
 *
 * Responsabilidades:
 * - Validar pertenencia multi-empresa (Factura y Banco)
 * - Validar reglas financieras (saldos, retenciones)
 * - Registrar pagos normales y de retención
 * - Eliminar pagos respetando reglas de seguridad
 *
 * Nota:
 * - No maneja UI
 * - No decide visibilidad
 * - Aplica reglas duras de negocio
 */
class FacturaPagoService
{
    /**
     * Registra un pago (normal o de retención) para una factura
     * y suma el monto al banco destino (si existe banco).
     */
    public function registrarPago(Factura $factura, array $data, User $user): FacturaPago
    {
        return DB::transaction(function () use ($factura, $data, $user) {
            /**
             * Contexto de seguridad multi-empresa.
             */
            $empresaId = $user->empresa_id;

            /**
             * Lock de factura + relaciones para evitar pagos simultáneos inconsistentes.
             */
            $factura = Factura::query()
                ->with(['proyecto', 'pagos'])
                ->lockForUpdate()
                ->findOrFail($factura->id);

            /**
             * Regla: el usuario solo puede registrar pagos en facturas de su empresa.
             */
            if (
                $empresaId === null ||
                (int) ($factura->proyecto?->empresa_id ?? 0) !== (int) $empresaId
            ) {
                throw new DomainException(
                    'No tienes permiso para registrar pagos en facturas de otra empresa.',
                );
            }

            /**
             * Tipo de pago:
             * - normal
             * - retencion
             */
            $tipo = (string) ($data['tipo'] ?? 'normal');

            /**
             * Regla: la retención solo puede pagarse cuando el pago normal está completo.
             */
            if ($tipo === 'retencion' && !FacturaFinance::puedePagarRetencion($factura)) {
                throw new DomainException(
                    'No puedes pagar la retención hasta completar el pago normal de la factura.',
                );
            }

            /**
             * Validación del banco destino (y lock para actualizar saldo).
             */
            $banco = null;
            if (!empty($data['banco_id'])) {
                $banco = Banco::query()->lockForUpdate()->findOrFail((int) $data['banco_id']);

                if ($empresaId && (int) $banco->empresa_id !== (int) $empresaId) {
                    throw new DomainException('No puedes usar un banco de otra empresa.');
                }
            }

            /**
             * Normalización del monto ingresado.
             */
            $montoIngresado = round((float) ($data['monto'] ?? 0), 2);

            if ($montoIngresado <= 0) {
                throw new DomainException('El monto debe ser mayor a 0.');
            }

            /**
             * Cálculo financiero base:
             * - Neto = facturado - retención
             * - Saldo normal pendiente
             * - Retención pendiente
             */
            $facturado = (float) $factura->monto_facturado;
            $retTotal = (float) ($factura->retencion ?? 0);
            $neto = max(0, round($facturado - $retTotal, 2));

            $pagadoNormal = (float) $factura->pagos->where('tipo', 'normal')->sum('monto');
            $pagadoRet = (float) $factura->pagos->where('tipo', 'retencion')->sum('monto');

            $saldoNormal = max(0, round($neto - $pagadoNormal, 2));
            $retPendiente = max(0, round($retTotal - $pagadoRet, 2));

            /**
             * Validaciones por tipo de pago.
             */
            if ($tipo === 'normal') {
                if ($saldoNormal <= 0) {
                    throw new DomainException(
                        'El pago normal ya está completo. No existe saldo normal pendiente.',
                    );
                }

                if ($montoIngresado > $saldoNormal) {
                    throw new DomainException(
                        'El monto excede el saldo normal pendiente. Máximo permitido: Bs ' .
                            number_format($saldoNormal, 2, ',', '.'),
                    );
                }
            } else {
                if ($retPendiente <= 0) {
                    throw new DomainException('No existe retención pendiente para pagar.');
                }

                if ($montoIngresado > $retPendiente) {
                    throw new DomainException(
                        'El monto excede la retención pendiente. Máximo permitido: Bs ' .
                            number_format($retPendiente, 2, ',', '.'),
                    );
                }
            }

            /**
             * Persistencia del pago (snapshots del banco destino).
             */
            $pago = FacturaPago::create([
                'factura_id' => $factura->id,
                'banco_id' => $banco?->id,
                'fecha_pago' => $data['fecha_pago'],
                'tipo' => $tipo,
                'monto' => $montoIngresado,
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'nro_operacion' => $data['nro_operacion'] ?? null,
                'observacion' => $data['observacion'] ?? null,
                'foto_comprobante' => $data['foto_comprobante'] ?? null,

                // Snapshots de destino
                'destino_banco_nombre_snapshot' => $banco?->nombre,
                'destino_numero_cuenta_snapshot' => $banco?->numero_cuenta,
                'destino_moneda_snapshot' => $banco?->moneda,
                'destino_titular_snapshot' => $banco?->titular,
                'destino_tipo_cuenta_snapshot' => $banco?->tipo_cuenta,
            ]);

            /**
             * ✅ Aumenta el saldo del banco destino.
             */
            if ($banco) {
                $banco->increment('monto', $montoIngresado);
            }

            return $pago;
        });
    }

    /**
     * Elimina un pago existente y resta el monto del banco destino (si existe banco).
     */
    public function eliminarPago(FacturaPago $pago, User $user): void
    {
        DB::transaction(function () use ($pago, $user) {
            /**
             * Seguridad multi-empresa.
             */
            $empresaId = $user->empresa_id;

            /**
             * Lock del pago + factura para evitar inconsistencias concurrentes.
             */
            $pago = FacturaPago::query()
                ->with(['factura.proyecto'])
                ->lockForUpdate()
                ->findOrFail($pago->id);

            if (
                $empresaId === null ||
                (int) ($pago->factura?->proyecto?->empresa_id ?? 0) !== (int) $empresaId
            ) {
                throw new DomainException('No tienes permiso para eliminar pagos de otra empresa.');
            }

            /**
             * Si hay banco destino, lo bloqueamos y restamos el saldo.
             */
            $banco = null;
            if (!empty($pago->banco_id)) {
                $banco = Banco::query()->lockForUpdate()->find($pago->banco_id);

                // Si existe, validamos empresa por seguridad adicional
                if ($banco && $empresaId && (int) $banco->empresa_id !== (int) $empresaId) {
                    throw new DomainException('No puedes afectar un banco de otra empresa.');
                }
            }

            $monto = round((float) $pago->monto, 2);

            /**
             * ✅ Primero revertimos el banco, luego borramos el pago.
             */
            if ($banco) {
                // Evita saldo negativo por errores de data
                if ((float) $banco->monto < $monto) {
                    throw new DomainException(
                        'No se puede eliminar el pago porque el saldo del banco quedaría negativo.',
                    );
                }

                $banco->decrement('monto', $monto);
            }

            $pago->delete();
        });
    }
}

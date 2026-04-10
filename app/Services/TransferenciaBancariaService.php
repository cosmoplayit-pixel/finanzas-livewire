<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\TransferenciaBancaria;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransferenciaBancariaService
{
    /**
     * Ejecuta una transferencia entre dos cuentas bancarias de la misma empresa.
     * Soporta transferencias en la misma moneda y entre monedas distintas (con tipo de cambio).
     *
     * @param  array{
     *   banco_origen_id: int,
     *   banco_destino_id: int,
     *   monto_origen: float,
     *   tipo_cambio: float|null,
     *   nro_transaccion: string|null,
     *   fecha: string,
     *   observacion: string|null,
     *   foto_comprobante: string|null,
     * } $data
     */
    public function transferir(array $data, int $empresaId, int $createdBy): TransferenciaBancaria
    {
        return DB::transaction(function () use ($data, $empresaId, $createdBy) {

            // Cargamos ambos bancos con lock para evitar condiciones de carrera
            $origen = Banco::lockForUpdate()->findOrFail($data['banco_origen_id']);
            $destino = Banco::lockForUpdate()->findOrFail($data['banco_destino_id']);

            // --- Validaciones de negocio ---
            if ((int) $origen->empresa_id !== $empresaId) {
                throw new DomainException('El banco origen no pertenece a su empresa.');
            }
            if ((int) $destino->empresa_id !== $empresaId) {
                throw new DomainException('El banco destino no pertenece a su empresa.');
            }
            if ($origen->id === $destino->id) {
                throw new DomainException('El banco origen y el banco destino no pueden ser el mismo.');
            }
            if (! $origen->active) {
                throw new DomainException('El banco origen está inactivo.');
            }
            if (! $destino->active) {
                throw new DomainException('El banco destino está inactivo.');
            }

            $montoOrigen = round((float) $data['monto_origen'], 2);

            if ($montoOrigen <= 0) {
                throw new DomainException('El monto a transferir debe ser mayor a 0.');
            }

            // Validación de saldo disponible
            $saldoOrigenAntes = round((float) ($origen->monto ?? 0), 2);
            if ($montoOrigen > $saldoOrigenAntes) {
                throw new DomainException(
                    'Saldo insuficiente en banco origen. Disponible: '
                    .$origen->moneda.' '
                    .number_format($saldoOrigenAntes, 2, ',', '.')
                );
            }

            // --- Cálculo del monto destino con conversión de moneda ---
            $monedaOrigen = $origen->moneda;
            $monedaDestino = $destino->moneda;
            $tipoCambio = null;

            if ($monedaOrigen === $monedaDestino) {
                $montoDestino = $montoOrigen;
            } else {
                $tipoCambio = round((float) ($data['tipo_cambio'] ?? 0), 6);

                if ($tipoCambio <= 0) {
                    throw new DomainException(
                        'Se requiere un tipo de cambio válido al transferir entre monedas distintas.'
                    );
                }

                if ($monedaOrigen === 'BOB' && $monedaDestino === 'USD') {
                    $montoDestino = round($montoOrigen / $tipoCambio, 2);
                } else {
                    // USD → BOB
                    $montoDestino = round($montoOrigen * $tipoCambio, 2);
                }
            }

            // --- Actualización de saldos ---
            $saldoOrigenDespues = round($saldoOrigenAntes - $montoOrigen, 2);
            $saldoDestinoAntes = round((float) ($destino->monto ?? 0), 2);
            $saldoDestinoDespues = round($saldoDestinoAntes + $montoDestino, 2);

            $origen->monto = $saldoOrigenDespues;
            $origen->save();

            $destino->monto = $saldoDestinoDespues;
            $destino->save();

            // --- Registro de la transferencia ---
            return TransferenciaBancaria::create([
                'empresa_id' => $empresaId,
                'banco_origen_id' => $origen->id,
                'banco_destino_id' => $destino->id,
                'moneda_origen' => $monedaOrigen,
                'moneda_destino' => $monedaDestino,
                'monto_origen' => $montoOrigen,
                'monto_destino' => $montoDestino,
                'tipo_cambio' => $tipoCambio,
                'saldo_origen_antes' => $saldoOrigenAntes,
                'saldo_origen_despues' => $saldoOrigenDespues,
                'saldo_destino_antes' => $saldoDestinoAntes,
                'saldo_destino_despues' => $saldoDestinoDespues,
                'nro_transaccion' => trim($data['nro_transaccion'] ?? '') ?: null,
                'fecha' => $data['fecha'],
                'observacion' => trim($data['observacion'] ?? '') ?: null,
                'foto_comprobante' => $data['foto_comprobante'] ?? null,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Elimina una transferencia bancaria revirtiendo los saldos de los bancos.
     * Solo permite eliminar si los saldos tras la reversión no son negativos.
     */
    public function eliminarTransferencia(int $transferenciaId, int $empresaId, bool $isAdmin): void
    {
        DB::transaction(function () use ($transferenciaId, $empresaId, $isAdmin) {
            $transferencia = TransferenciaBancaria::lockForUpdate()->findOrFail($transferenciaId);

            if (! $isAdmin && (int) $transferencia->empresa_id !== $empresaId) {
                throw new DomainException('No tiene permisos para eliminar esta transferencia.');
            }

            $origen = Banco::lockForUpdate()->find($transferencia->banco_origen_id);
            $destino = Banco::lockForUpdate()->find($transferencia->banco_destino_id);

            if (! $origen || ! $destino) {
                throw new DomainException('No se encontraron los bancos vinculados a la transferencia.');
            }

            $montoOrigen = (float) $transferencia->monto_origen;
            $montoDestino = (float) $transferencia->monto_destino;

            $saldoOrigenActual = (float) ($origen->monto ?? 0);
            $saldoDestinoActual = (float) ($destino->monto ?? 0);

            // Revertir: Al origen se le devuelve lo que envió, al destino se le resta lo que recibió
            $nuevoSaldoOrigen = round($saldoOrigenActual + $montoOrigen, 2);
            $nuevoSaldoDestino = round($saldoDestinoActual - $montoDestino, 2);

            if ($nuevoSaldoDestino < 0) {
                // Código estructurado para que el componente pueda renderizar SweetAlert con detalle
                throw new DomainException(
                    'SALDO_DESTINO_INSUFICIENTE'
                    . ':' . round($saldoDestinoActual, 2)
                    . ':' . round($montoDestino, 2)
                    . ':' . $destino->nombre
                    . ':' . $destino->moneda
                );
            }

            $origen->monto = $nuevoSaldoOrigen;
            $origen->save();

            $destino->monto = $nuevoSaldoDestino;
            $destino->save();

            // Si hay foto comprobante, se podría eliminar del storage si se desea,
            // aunque a veces se conserva por histórico o acá la borramos:
            if ($transferencia->foto_comprobante) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($transferencia->foto_comprobante);
            }

            $transferencia->delete();
        });
    }
}

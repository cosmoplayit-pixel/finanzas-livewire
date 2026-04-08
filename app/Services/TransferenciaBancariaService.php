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
     * @param  int $empresaId
     * @param  int $createdBy
     */
    public function transferir(array $data, int $empresaId, int $createdBy): TransferenciaBancaria
    {
        return DB::transaction(function () use ($data, $empresaId, $createdBy) {

            // Cargamos ambos bancos con lock para evitar condiciones de carrera
            $origen  = Banco::lockForUpdate()->findOrFail($data['banco_origen_id']);
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
                    . $origen->moneda . ' '
                    . number_format($saldoOrigenAntes, 2, ',', '.')
                );
            }

            // --- Cálculo del monto destino con conversión de moneda ---
            $monedaOrigen  = $origen->moneda;
            $monedaDestino = $destino->moneda;
            $tipoCambio    = null;

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
            $saldoOrigenDespues  = round($saldoOrigenAntes - $montoOrigen, 2);
            $saldoDestinoAntes   = round((float) ($destino->monto ?? 0), 2);
            $saldoDestinoDespues = round($saldoDestinoAntes + $montoDestino, 2);

            $origen->monto  = $saldoOrigenDespues;
            $origen->save();

            $destino->monto = $saldoDestinoDespues;
            $destino->save();

            // --- Registro de la transferencia ---
            return TransferenciaBancaria::create([
                'empresa_id'            => $empresaId,
                'banco_origen_id'       => $origen->id,
                'banco_destino_id'      => $destino->id,
                'moneda_origen'         => $monedaOrigen,
                'moneda_destino'        => $monedaDestino,
                'monto_origen'          => $montoOrigen,
                'monto_destino'         => $montoDestino,
                'tipo_cambio'           => $tipoCambio,
                'saldo_origen_antes'    => $saldoOrigenAntes,
                'saldo_origen_despues'  => $saldoOrigenDespues,
                'saldo_destino_antes'   => $saldoDestinoAntes,
                'saldo_destino_despues' => $saldoDestinoDespues,
                'nro_transaccion'       => trim($data['nro_transaccion'] ?? '') ?: null,
                'fecha'                 => $data['fecha'],
                'observacion'           => trim($data['observacion'] ?? '') ?: null,
                'foto_comprobante'      => $data['foto_comprobante'] ?? null,
                'created_by'            => $createdBy,
            ]);
        });
    }
}

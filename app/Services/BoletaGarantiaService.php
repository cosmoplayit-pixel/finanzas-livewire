<?php

namespace App\Services;

use App\Models\Banco;
use App\Models\BoletaGarantia;
use App\Models\BoletaGarantiaDevolucion;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class BoletaGarantiaService
{
    public const TIPO_SERIEDAD = 'SERIEDAD';
    public const TIPO_CUMPLIMIENTO = 'CUMPLIMIENTO';

    public function crear(array $data, int $userId): BoletaGarantia
    {
        try {
            return DB::transaction(function () use ($data) {
                $retencion = (float) ($data['retencion'] ?? 0);

                if ($retencion <= 0) {
                    throw new DomainException('La retención debe ser mayor a 0.');
                }

                /** @var Banco $banco */
                $banco = Banco::query()
                    ->lockForUpdate()
                    ->where('empresa_id', $data['empresa_id'])
                    ->where('id', $data['banco_egreso_id'])
                    ->firstOrFail();

                // ✅ Moneda se define AUTOMÁTICAMENTE por el banco egreso
                $moneda = (string) $banco->moneda;

                $saldo = (float) $banco->monto;
                if ($retencion > $saldo) {
                    throw new DomainException('La retención excede el saldo actual del banco.');
                }

                // ✅ Egreso = RETENCIÓN
                $banco->monto = $saldo - $retencion;
                $banco->save();

                return BoletaGarantia::create([
                    ...$data,
                    'moneda' => $moneda, // ✅ guardado automático
                    'retencion' => $retencion,
                    'estado' => 'abierta',
                    'active' => true,
                ]);
            });
        } catch (QueryException $e) {
            // SQLSTATE[23000] duplicate key
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('Ya existe una boleta con ese número en tu empresa.');
            }
            throw $e;
        }
    }

    public function devolver(
        BoletaGarantia $boleta,
        array $data,
        int $userId,
    ): BoletaGarantiaDevolucion {
        return DB::transaction(function () use ($boleta, $data) {
            /** @var BoletaGarantia $bg */
            $bg = BoletaGarantia::query()->lockForUpdate()->findOrFail($boleta->id);

            if (!$bg->active) {
                throw new DomainException('La boleta está inactiva.');
            }

            $monto = (float) ($data['monto'] ?? 0);
            if ($monto <= 0) {
                throw new DomainException('El monto a devolver debe ser mayor a 0.');
            }

            $totalDevuelto = (float) BoletaGarantiaDevolucion::query()
                ->where('boleta_garantia_id', $bg->id)
                ->lockForUpdate()
                ->sum('monto');

            $restante = (float) $bg->retencion - $totalDevuelto;

            if ($monto > $restante) {
                throw new DomainException('El monto excede el restante de retención por devolver.');
            }

            /** @var Banco $bancoDestino */
            $bancoDestino = Banco::query()
                ->lockForUpdate()
                ->where('empresa_id', $bg->empresa_id)
                ->where('id', $data['banco_id'])
                ->firstOrFail();

            // ✅ Se sigue validando contra moneda guardada en boleta (ya viene del banco egreso)
            if ((string) $bancoDestino->moneda !== (string) $bg->moneda) {
                throw new DomainException('La moneda del banco destino no coincide con la boleta.');
            }

            $antes = (float) $bancoDestino->monto;
            $despues = $antes + $monto;

            $bancoDestino->monto = $despues;
            $bancoDestino->save();

            $devol = BoletaGarantiaDevolucion::create([
                'boleta_garantia_id' => $bg->id,
                'banco_id' => (int) $data['banco_id'],
                'fecha_devolucion' => $data['fecha_devolucion'],
                'monto' => $monto,
                'nro_transaccion' => $data['nro_transaccion'] ?? null,
                'observacion' => $data['observacion'] ?? null,
                'saldo_banco_antes' => $antes,
                'saldo_banco_despues' => $despues,
            ]);

            $nuevoTotalDevuelto = $totalDevuelto + $monto;
            $bg->estado = $nuevoTotalDevuelto >= (float) $bg->retencion ? 'devuelta' : 'abierta';
            $bg->save();

            return $devol;
        });
    }

    public function eliminarDevolucion(BoletaGarantia $boleta, int $devolucionId, int $userId): void
    {
        DB::transaction(function () use ($boleta, $devolucionId) {
            /** @var BoletaGarantia $bg */
            $bg = BoletaGarantia::query()->lockForUpdate()->findOrFail($boleta->id);

            if (!$bg->active) {
                throw new DomainException('La boleta está inactiva.');
            }

            /** @var BoletaGarantiaDevolucion $dev */
            $dev = BoletaGarantiaDevolucion::query()
                ->lockForUpdate()
                ->where('boleta_garantia_id', $bg->id)
                ->where('id', $devolucionId)
                ->firstOrFail();

            /** @var Banco $bancoDestino */
            $bancoDestino = Banco::query()
                ->lockForUpdate()
                ->where('empresa_id', $bg->empresa_id)
                ->where('id', $dev->banco_id)
                ->firstOrFail();

            $monto = (float) $dev->monto;
            $saldoActual = (float) $bancoDestino->monto;

            if ($saldoActual < $monto) {
                throw new DomainException(
                    'No se puede eliminar: el banco destino no tiene saldo suficiente para revertir.',
                );
            }

            $bancoDestino->monto = $saldoActual - $monto;
            $bancoDestino->save();

            $dev->delete();

            $totalDevuelto = (float) BoletaGarantiaDevolucion::query()
                ->where('boleta_garantia_id', $bg->id)
                ->sum('monto');

            $bg->estado = $totalDevuelto >= (float) $bg->retencion ? 'devuelta' : 'abierta';
            $bg->save();
        });
    }
}

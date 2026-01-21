<?php

namespace App\Services;

use App\Models\AgentePresupuesto;
use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AgentePresupuestoService
{
    public function crear(
        AgenteServicio $agente,
        Banco $banco,
        float $monto,
        string $moneda,
        string $fecha, // Y-m-d
        string $nro_transaccion,
        ?string $observacion,
        User $user,
    ): AgentePresupuesto {
        return DB::transaction(function () use (
            $agente,
            $banco,
            $monto,
            $moneda,
            $fecha,
            $nro_transaccion,
            $observacion,
            $user,
        ) {
            $empresaId = (int) ($user->empresa_id ?? 0);

            $banco = Banco::query()->lockForUpdate()->findOrFail($banco->id);
            $agente = AgenteServicio::query()->lockForUpdate()->findOrFail($agente->id);

            if ($empresaId <= 0) {
                throw new DomainException('Usuario sin empresa asignada.');
            }
            if ((int) $banco->empresa_id !== $empresaId) {
                throw new DomainException('No puedes usar un banco de otra empresa.');
            }
            if ((int) $agente->empresa_id !== $empresaId) {
                throw new DomainException('No puedes usar un agente de otra empresa.');
            }

            $monto = round((float) $monto, 2);
            if ($monto <= 0) {
                throw new DomainException('El monto debe ser mayor a 0.');
            }

            $moneda = strtoupper(trim((string) $moneda));
            if (!in_array($moneda, ['BOB', 'USD'], true)) {
                throw new DomainException('Moneda inválida. Debe ser BOB o USD.');
            }

            if (strtoupper((string) $banco->moneda) !== $moneda) {
                throw new DomainException(
                    'La moneda del presupuesto debe coincidir con la moneda del banco.',
                );
            }

            $nro_transaccion = trim((string) $nro_transaccion);
            if ($nro_transaccion === '') {
                throw new DomainException('El Nro. de transacción es obligatorio.');
            }

            $saldoBancoAntes = round((float) ($banco->monto ?? 0), 2);
            if ($monto > $saldoBancoAntes) {
                throw new DomainException('No puede ser mayor al saldo actual del banco.');
            }

            $saldoBancoDespues = round($saldoBancoAntes - $monto, 2);

            $banco->update(['monto' => $saldoBancoDespues]);

            if ($moneda === 'USD') {
                $agente->update([
                    'saldo_usd' => round((float) ($agente->saldo_usd ?? 0) + $monto, 2),
                ]);
            } else {
                $agente->update([
                    'saldo_bob' => round((float) ($agente->saldo_bob ?? 0) + $monto, 2),
                ]);
            }

            return AgentePresupuesto::create([
                'empresa_id' => $empresaId,
                'banco_id' => $banco->id,
                'agente_servicio_id' => $agente->id,

                'moneda' => $moneda,
                'monto' => $monto,

                'saldo_banco_antes' => $saldoBancoAntes,
                'saldo_banco_despues' => $saldoBancoDespues,

                'rendido_total' => 0,
                'saldo_por_rendir' => $monto,

                'fecha_presupuesto' => $fecha,
                'nro_transaccion' => $nro_transaccion,
                'observacion' => $observacion,

                'estado' => 'abierto',
                'active' => true,
                'created_by' => $user->id,
            ]);
        });
    }
}

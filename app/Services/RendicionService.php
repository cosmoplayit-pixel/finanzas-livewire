<?php

namespace App\Services;

use App\Models\AgentePresupuesto;
use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\Rendicion;
use App\Models\RendicionMovimiento;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RendicionService
{
    public function crearDesdePresupuesto(AgentePresupuesto $presupuesto, User $user): Rendicion
    {
        return DB::transaction(function () use ($presupuesto, $user) {
            /** @var AgentePresupuesto $p */
            $p = AgentePresupuesto::query()->lockForUpdate()->findOrFail($presupuesto->id);

            if (!$p->active) {
                throw new DomainException('El presupuesto está inactivo.');
            }

            $montoPresupuesto = round((float) ($p->monto ?? 0), 2);
            if ($montoPresupuesto <= 0) {
                throw new DomainException('Este presupuesto no tiene un monto válido.');
            }

            // Si ya tiene rendición asociada, devolverla (y asegurar totales coherentes)
            if (!empty($p->rendicion_id)) {
                /** @var Rendicion $r */
                $r = Rendicion::query()->lockForUpdate()->findOrFail((int) $p->rendicion_id);

                if (!$r->active) {
                    throw new DomainException('La rendición asociada está inactiva.');
                }

                // Si por alguna razón presupuesto_total quedó 0, lo restituimos
                if (round((float) ($r->presupuesto_total ?? 0), 2) <= 0) {
                    $r->presupuesto_total = $montoPresupuesto;
                    $r->save();
                }

                // Recalcular totales (con regla vigente)
                $this->recalcularTotales($r);

                // NOTA: no cambies estado de ap por saldo si quieres “cerrar manual”
                // Deja ap.estado como está y que se marque 'cerrado' solo en cerrarRendicion().
                $p->saldo_por_rendir = round((float) ($r->saldo ?? 0), 2);
                $p->rendido_total = round((float) ($r->rendido_total ?? 0), 2);
                $p->save();

                return $r;
            }

            // Crear rendición nueva
            /** @var Rendicion $r */
            $r = Rendicion::query()->create([
                'empresa_id' => (int) $p->empresa_id,
                'agente_servicio_id' => (int) $p->agente_servicio_id,
                'moneda' => (string) $p->moneda,
                'nro_rendicion' => $this->generarNroRendicion((int) $p->empresa_id),
                'presupuesto_total' => $montoPresupuesto,
                'rendido_total' => 0,
                'saldo' => $montoPresupuesto,
                'fecha_rendicion' => now(),
                'fecha_cierre' => null,
                'estado' => 'abierto',
                'active' => true,
                'created_by' => (int) $user->id,
            ]);

            // Vincular presupuesto -> rendición
            $p->rendicion_id = (int) $r->id;
            $p->rendido_total = 0;
            $p->saldo_por_rendir = $montoPresupuesto;
            $p->estado = $p->estado ?: 'abierto';
            $p->save();

            return $r;
        });
    }

    public function registrarMovimiento(
        Rendicion $rendicion,
        string $tipo,
        array $data,
        User $user,
        $foto = null,
    ): RendicionMovimiento {
        return DB::transaction(function () use ($rendicion, $tipo, $data, $user, $foto) {
            /** @var Rendicion $r */
            $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

            // Reglas duras
            if (!$r->active) {
                throw new DomainException('La rendición está inactiva.');
            }

            if (!in_array($tipo, ['COMPRA', 'DEVOLUCION'], true)) {
                throw new DomainException('Tipo de movimiento inválido.');
            }

            // Monedas
            $baseMoneda = (string) $r->moneda;
            $movMoneda = (string) ($data['mov_moneda'] ?? '');

            if ($movMoneda === '') {
                throw new DomainException('Moneda inválida.');
            }

            // Monto
            $monto = round((float) ($data['mov_monto'] ?? 0), 2);
            if ($monto <= 0) {
                throw new DomainException('Monto inválido.');
            }

            // Convertir a moneda base
            $montoBase = $this->convertirAMonedaBase(
                monto: $monto,
                monedaMovimiento: $movMoneda,
                monedaBase: $baseMoneda,
                tipoCambio: $data['mov_tipo_cambio'] ?? null,
            );

            if ($montoBase <= 0) {
                throw new DomainException('Monto inválido.');
            }

            // Totales actuales (en base)
            $sumComprasBase = round(
                (float) RendicionMovimiento::query()
                    ->where('rendicion_id', $r->id)
                    ->where('tipo', 'COMPRA')
                    ->sum('monto_base'),
                2,
            );

            $sumDevolBase = round(
                (float) RendicionMovimiento::query()
                    ->where('rendicion_id', $r->id)
                    ->where('tipo', 'DEVOLUCION')
                    ->sum('monto_base'),
                2,
            );

            $presupuesto = round((float) ($r->presupuesto_total ?? 0), 2);

            // ✅ Regla vigente en tu flujo: rendición = compras + devoluciones
            $rendidoActual = round($sumComprasBase + $sumDevolBase, 2);
            $saldoActual = round(max(0, $presupuesto - $rendidoActual), 2);

            // Validaciones por tipo
            if ($tipo === 'COMPRA') {
                $nuevoRendido = round($rendidoActual + $montoBase, 2);
                if ($nuevoRendido > $presupuesto) {
                    $disponible = round(max(0, $presupuesto - $rendidoActual), 2);
                    throw new DomainException(
                        "El monto excede el presupuesto disponible. Disponible: {$disponible} {$baseMoneda}.",
                    );
                }

                $entidadId = (int) ($data['mov_entidad_id'] ?? 0);
                $proyectoId = (int) ($data['mov_proyecto_id'] ?? 0);

                if ($entidadId <= 0) {
                    throw new DomainException('Debe seleccionar entidad.');
                }
                if ($proyectoId <= 0) {
                    throw new DomainException('Debe seleccionar proyecto.');
                }
            }

            if ($tipo === 'DEVOLUCION') {
                if ($montoBase > $saldoActual) {
                    $disp = number_format($saldoActual, 2, '.', ',');
                    throw new DomainException(
                        "La devolución excede el saldo disponible. Disponible: {$disp} {$baseMoneda}.",
                    );
                }

                $bancoId = (int) ($data['mov_banco_id'] ?? 0);
                if ($bancoId <= 0) {
                    throw new DomainException('Debe seleccionar banco.');
                }

                /** @var Banco $b */
                $b = Banco::query()->lockForUpdate()->findOrFail($bancoId);

                if ((int) $b->empresa_id !== (int) $r->empresa_id) {
                    throw new DomainException('El banco no pertenece a la empresa.');
                }

                if ((string) $b->moneda !== $movMoneda) {
                    throw new DomainException(
                        'La moneda del banco no coincide con la moneda de la devolución.',
                    );
                }

                // Abonar al banco (en moneda del movimiento)
                $b->monto = round(((float) ($b->monto ?? 0)) + $monto, 2);
                $b->save();
            }

            // ✅ Ajustar saldo del agente SIEMPRE (COMPRA y DEVOLUCION consumen saldo del agente)
            /** @var AgenteServicio $ag */
            $ag = AgenteServicio::query()
                ->lockForUpdate()
                ->findOrFail((int) $r->agente_servicio_id);

            if ((int) $ag->empresa_id !== (int) $r->empresa_id) {
                throw new DomainException('El agente no pertenece a la empresa.');
            }

            // Sale dinero del agente -> delta negativo en moneda base de la rendición
            $this->aplicarDeltaAgente($ag, $baseMoneda, -$montoBase);

            // Foto
            $path = null;
            if ($foto) {
                $path = $foto->store('rendiciones', 'public');
            }

            // Crear movimiento
            $mov = RendicionMovimiento::query()->create([
                'empresa_id' => (int) $r->empresa_id,
                'rendicion_id' => (int) $r->id,
                'tipo' => $tipo,
                'fecha' => $data['mov_fecha'] ?? null,

                'entidad_id' => $tipo === 'COMPRA' ? (int) ($data['mov_entidad_id'] ?? 0) : null,
                'proyecto_id' => $tipo === 'COMPRA' ? (int) ($data['mov_proyecto_id'] ?? 0) : null,

                'tipo_comprobante' =>
                    $tipo === 'COMPRA' ? $data['mov_tipo_comprobante'] ?? null : null,
                'nro_comprobante' =>
                    $tipo === 'COMPRA' ? $data['mov_nro_comprobante'] ?? null : null,

                'banco_id' => $tipo === 'DEVOLUCION' ? (int) ($data['mov_banco_id'] ?? 0) : null,
                'nro_transaccion' =>
                    $tipo === 'DEVOLUCION' ? $data['mov_nro_transaccion'] ?? null : null,

                'moneda' => $movMoneda,
                'tipo_cambio' =>
                    $movMoneda !== $baseMoneda ? (float) ($data['mov_tipo_cambio'] ?? null) : null,

                'monto' => $monto,
                'monto_base' => $montoBase,

                'foto_path' => $path,
                'observacion' => $data['mov_observacion'] ?? null,

                'created_by' => (int) $user->id,
            ]);

            // Recalcular totales
            $this->recalcularTotales($r);

            return $mov->fresh();
        });
    }

    public function eliminarMovimiento(Rendicion $rendicion, int $movimientoId, User $user): void
    {
        DB::transaction(function () use ($rendicion, $movimientoId) {
            /** @var Rendicion $r */
            $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

            if (!$r->active) {
                throw new DomainException('La rendición está inactiva.');
            }

            /** @var RendicionMovimiento $m */
            $m = RendicionMovimiento::query()
                ->lockForUpdate()
                ->where('rendicion_id', $r->id)
                ->where('id', $movimientoId)
                ->firstOrFail();

            // Revertir banco si era devolución
            if ($m->tipo === 'DEVOLUCION') {
                if (empty($m->banco_id)) {
                    throw new DomainException('La devolución no tiene banco asociado.');
                }

                /** @var Banco $b */
                $b = Banco::query()->lockForUpdate()->findOrFail((int) $m->banco_id);

                if ((int) $b->empresa_id !== (int) $r->empresa_id) {
                    throw new DomainException('El banco no pertenece a la empresa.');
                }

                if ((string) $b->moneda !== (string) $m->moneda) {
                    throw new DomainException('La moneda del banco no coincide.');
                }

                $monto = round((float) ($m->monto ?? 0), 2);
                if ($monto <= 0) {
                    throw new DomainException('Monto inválido.');
                }

                $b->monto = round(((float) ($b->monto ?? 0)) - $monto, 2);
                $b->save();
            }

            // ✅ Revertir saldo del agente (al borrar, devolvemos monto_base)
            $montoBase = round((float) ($m->monto_base ?? 0), 2);
            if ($montoBase <= 0) {
                throw new DomainException('Monto base inválido.');
            }

            /** @var AgenteServicio $ag */
            $ag = AgenteServicio::query()
                ->lockForUpdate()
                ->findOrFail((int) $r->agente_servicio_id);

            if ((int) $ag->empresa_id !== (int) $r->empresa_id) {
                throw new DomainException('El agente no pertenece a la empresa.');
            }

            $this->aplicarDeltaAgente($ag, (string) $r->moneda, +$montoBase);

            // Eliminar movimiento
            $m->delete();

            // Recalcular
            $this->recalcularTotales($r);
        });
    }

    public function cerrarRendicion(Rendicion $rendicion, User $user): void
    {
        DB::transaction(function () use ($rendicion, $user) {
            /** @var Rendicion $r */
            $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

            if (!$r->active) {
                throw new DomainException('La rendición está inactiva.');
            }

            if ($r->estado === 'cerrado') {
                return;
            }

            $this->recalcularTotales($r);

            if (round((float) ($r->saldo ?? 0), 2) > 0) {
                throw new DomainException('No se puede cerrar: todavía hay saldo abierto.');
            }

            $r->estado = 'cerrado';
            $r->fecha_cierre = now()->toDateString();
            $r->save();

            // Marcar presupuesto como cerrado SOLO aquí (cierre manual)
            $p = AgentePresupuesto::query()
                ->lockForUpdate()
                ->where('rendicion_id', $r->id)
                ->first();

            if ($p) {
                $p->saldo_por_rendir = 0;
                $p->estado = 'cerrado';
                $p->save();
            }
        });
    }

    public function recalcularTotales(Rendicion $rendicion): void
    {
        /** @var Rendicion $r */
        $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

        $sumComprasBase = round(
            (float) RendicionMovimiento::query()
                ->where('rendicion_id', $r->id)
                ->where('tipo', 'COMPRA')
                ->sum('monto_base'),
            2,
        );

        $sumDevolBase = round(
            (float) RendicionMovimiento::query()
                ->where('rendicion_id', $r->id)
                ->where('tipo', 'DEVOLUCION')
                ->sum('monto_base'),
            2,
        );

        // ✅ Regla vigente: rendición = compras + devoluciones
        $rendidoTotal = round($sumComprasBase + $sumDevolBase, 2);

        $pres = round((float) ($r->presupuesto_total ?? 0), 2);
        $saldo = round(max(0, $pres - $rendidoTotal), 2);

        $r->rendido_total = $rendidoTotal;
        $r->saldo = $saldo;
        $r->save();

        // Sin “cerrar automático” por saldo: solo sincronizamos montos
        $p = AgentePresupuesto::query()->lockForUpdate()->where('rendicion_id', $r->id)->first();
        if ($p) {
            $p->rendido_total = $rendidoTotal;
            $p->saldo_por_rendir = $saldo;
            // No forzar $p->estado aquí si quieres cierre manual
            $p->save();
        }
    }

    private function aplicarDeltaAgente(
        AgenteServicio $agente,
        string $monedaBase,
        float $deltaBase,
    ): void {
        $monedaBase = strtoupper(trim($monedaBase));
        $deltaBase = round($deltaBase, 2);

        if ($deltaBase === 0.0) {
            return;
        }

        if ($monedaBase === 'USD') {
            $actual = round((float) ($agente->saldo_usd ?? 0), 2);
            $nuevo = round($actual + $deltaBase, 2);

            if ($nuevo < 0) {
                throw new DomainException(
                    "Saldo insuficiente del agente. Disponible: {$actual} USD.",
                );
            }

            $agente->saldo_usd = $nuevo;
            $agente->save();
            return;
        }

        if ($monedaBase === 'BOB') {
            $actual = round((float) ($agente->saldo_bob ?? 0), 2);
            $nuevo = round($actual + $deltaBase, 2);

            if ($nuevo < 0) {
                throw new DomainException(
                    "Saldo insuficiente del agente. Disponible: {$actual} BOB.",
                );
            }

            $agente->saldo_bob = $nuevo;
            $agente->save();
            return;
        }

        throw new DomainException('Moneda base inválida para saldo del agente.');
    }

    private function convertirAMonedaBase(
        float $monto,
        string $monedaMovimiento,
        string $monedaBase,
        $tipoCambio,
    ): float {
        $monto = round($monto, 2);

        if ($monedaMovimiento === $monedaBase) {
            return $monto;
        }

        $tc = (float) $tipoCambio;
        if ($tc <= 0) {
            throw new DomainException('Tipo de cambio es obligatorio cuando la moneda difiere.');
        }

        if ($monedaBase === 'USD' && $monedaMovimiento === 'BOB') {
            return round($monto / $tc, 2);
        }

        if ($monedaBase === 'BOB' && $monedaMovimiento === 'USD') {
            return round($monto * $tc, 2);
        }

        throw new DomainException('Conversión de moneda no soportada.');
    }

    private function generarNroRendicion(int $empresaId): string
    {
        $year = now()->format('Y');

        $seq =
            (int) Rendicion::query()
                ->where('empresa_id', $empresaId)
                ->whereYear('created_at', $year)
                ->lockForUpdate()
                ->count() + 1;

        return sprintf('R-%d-%s-%06d', $empresaId, $year, $seq);
    }
}

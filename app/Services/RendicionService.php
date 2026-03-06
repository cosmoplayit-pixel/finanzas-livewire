<?php

namespace App\Services;

use App\Models\AgenteServicio;
use App\Models\Banco;
use App\Models\Rendicion;
use App\Models\RendicionMovimiento;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio unificado para el módulo de Presupuestos / Rendiciones.
 *
 * Una Rendicion = Presupuesto + sus movimientos.
 * Los movimientos (COMPRA | DEVOLUCION) viven en rendicion_movimientos.
 * Patrón idéntico a FacturaPagoService.
 */
class RendicionService
{
    // =========================================================
    // CREAR PRESUPUESTO/RENDICIÓN
    // =========================================================
    public function crear(
        AgenteServicio $agente,
        Banco $banco,
        float $monto,
        string $moneda,
        string $fechaPresupuesto,
        ?string $nroTransaccion,
        ?string $observacion,
        ?string $fotoComprobante,
        User $user,
    ): Rendicion {
        return DB::transaction(function () use (
            $agente, $banco, $monto, $moneda, $fechaPresupuesto,
            $nroTransaccion, $observacion, $fotoComprobante, $user,
        ) {
            $empresaId = (int) ($user->empresa_id ?? 0);

            $banco  = Banco::query()->lockForUpdate()->findOrFail($banco->id);
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
                throw new DomainException('La moneda del presupuesto debe coincidir con la moneda del banco.');
            }

            $saldoBancoAntes = round((float) ($banco->monto ?? 0), 2);
            if ($monto > $saldoBancoAntes) {
                throw new DomainException('No puede ser mayor al saldo actual del banco.');
            }

            $saldoBancoDespues = round($saldoBancoAntes - $monto, 2);
            $banco->update(['monto' => $saldoBancoDespues]);

            // Acreditar saldo al agente
            if ($moneda === 'USD') {
                $agente->update(['saldo_usd' => round((float) ($agente->saldo_usd ?? 0) + $monto, 2)]);
            } else {
                $agente->update(['saldo_bob' => round((float) ($agente->saldo_bob ?? 0) + $monto, 2)]);
            }

            return Rendicion::create([
                'empresa_id'          => $empresaId,
                'banco_id'            => $banco->id,
                'agente_servicio_id'  => $agente->id,

                'moneda'              => $moneda,
                'monto'               => $monto,
                'nro_transaccion'     => $nroTransaccion,

                'saldo_banco_antes'   => $saldoBancoAntes,
                'saldo_banco_despues' => $saldoBancoDespues,

                'rendido_total'       => 0,
                'saldo_por_rendir'    => $monto,

                'nro_rendicion'       => $this->generarNroRendicion($empresaId),
                'fecha_presupuesto'   => $fechaPresupuesto,
                'fecha_cierre'        => null,

                'estado'              => 'abierto',
                'active'              => true,

                'observacion'         => $observacion,
                'foto_comprobante'    => $fotoComprobante,

                'created_by'          => $user->id,
            ]);
        });
    }

    // =========================================================
    // ELIMINAR PRESUPUESTO/RENDICIÓN (solo sin movimientos)
    // =========================================================
    public function eliminarPresupuesto(Rendicion $rendicion, User $user): void
    {
        DB::transaction(function () use ($rendicion, $user) {
            $r = Rendicion::query()->with(['banco', 'agente'])->lockForUpdate()->findOrFail($rendicion->id);

            // Validar si tiene movimientos
            $movimientosCount = RendicionMovimiento::where('rendicion_id', $r->id)->count();
            if ($movimientosCount > 0) {
                throw new DomainException('No se puede eliminar el presupuesto/rendición porque ya tiene movimientos registrados.');
            }

            if ($r->estado === 'cerrado') {
                throw new DomainException('No se puede eliminar un presupuesto que ya está cerrado.');
            }

            $banco  = $r->banco;
            $agente = $r->agente;
            $monto  = (float) $r->monto;

            // Al eliminar el presupuesto, le RETORNAMOS el dinero al banco
            if ($banco) {
                // Bloquear banco para update
                $b = Banco::query()->lockForUpdate()->findOrFail($banco->id);
                $b->update(['monto' => round((float) ($b->monto ?? 0) + $monto, 2)]);
            }

            // Al eliminar el presupuesto, le RESTAMOS el dinero al agente
            if ($agente) {
                $a = AgenteServicio::query()->lockForUpdate()->findOrFail($agente->id);
                $saldoAgente = $r->moneda === 'USD' ? (float) ($a->saldo_usd ?? 0) : (float) ($a->saldo_bob ?? 0);

                if ($monto > $saldoAgente) {
                    // Lanzar excepción especial para que el frontend lo trate
                    throw new DomainException("SALDO_AGENTE_INSUFICIENTE:{$saldoAgente}:{$monto}");
                }

                if ($r->moneda === 'USD') {
                    $a->update(['saldo_usd' => round($saldoAgente - $monto, 2)]);
                } else {
                    $a->update(['saldo_bob' => round($saldoAgente - $monto, 2)]);
                }
            }

            // Eliminar la foto si tiene
            if ($r->foto_comprobante && Storage::disk('public')->exists($r->foto_comprobante)) {
                Storage::disk('public')->delete($r->foto_comprobante);
            }

            // Eliminar registro
            $r->delete();
        });
    }

    // =========================================================
    // REGISTRAR MOVIMIENTO (COMPRA | DEVOLUCION)
    // =========================================================
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

            if (!$r->active) {
                throw new DomainException('La rendición está inactiva.');
            }

            if ($r->estado === 'cerrado') {
                throw new DomainException('La rendición ya está cerrada. No se pueden agregar movimientos.');
            }

            if (!in_array($tipo, ['COMPRA', 'DEVOLUCION'], true)) {
                throw new DomainException('Tipo de movimiento inválido.');
            }

            $baseMoneda = (string) $r->moneda;
            $movMoneda  = strtoupper(trim((string) ($data['mov_moneda'] ?? '')));
            if (!in_array($movMoneda, ['BOB', 'USD'], true)) {
                throw new DomainException('Moneda inválida.');
            }

            $monto = round((float) ($data['mov_monto'] ?? 0), 2);
            if ($monto <= 0) {
                throw new DomainException('Monto inválido.');
            }

            $montoBase = $this->convertirAMonedaBase(
                monto: $monto,
                monedaMovimiento: $movMoneda,
                monedaBase: $baseMoneda,
                tipoCambio: $data['mov_tipo_cambio'] ?? null,
            );

            if ($montoBase <= 0) {
                throw new DomainException('Monto base inválido.');
            }

            $sumComprasBase = round(
                (float) RendicionMovimiento::query()
                    ->where('rendicion_id', $r->id)->where('tipo', 'COMPRA')->where('active', true)->sum('monto_base'),
                2,
            );

            $sumDevolBase = round(
                (float) RendicionMovimiento::query()
                    ->where('rendicion_id', $r->id)->where('tipo', 'DEVOLUCION')->where('active', true)->sum('monto_base'),
                2,
            );

            $presupuestoTotal = round((float) ($r->monto ?? 0), 2);
            $rendidoActual    = round($sumComprasBase + $sumDevolBase, 2);
            $saldoActual      = round(max(0, $presupuestoTotal - $rendidoActual), 2);

            if ($tipo === 'COMPRA') {
                $nuevoRendido = round($rendidoActual + $montoBase, 2);
                if ($nuevoRendido > $presupuestoTotal) {
                    $disponible = round(max(0, $presupuestoTotal - $rendidoActual), 2);
                    throw new DomainException(
                        "El monto excede el presupuesto disponible. Disponible: {$disponible} {$baseMoneda}.",
                    );
                }

                if ((int) ($data['mov_entidad_id'] ?? 0) <= 0) {
                    throw new DomainException('Debe seleccionar entidad.');
                }
                if ((int) ($data['mov_proyecto_id'] ?? 0) <= 0) {
                    throw new DomainException('Debe seleccionar proyecto.');
                }
            }

            if ($tipo === 'DEVOLUCION') {
                if ($montoBase > $saldoActual) {
                    throw new DomainException(
                        "La devolución excede el saldo disponible. Disponible: " . number_format($saldoActual, 2) . " {$baseMoneda}.",
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
                    throw new DomainException('La moneda del banco no coincide con la moneda de la devolución.');
                }

                $b->monto = round(((float) ($b->monto ?? 0)) + $monto, 2);
                $b->save();
            }

            // Reducir saldo del agente
            /** @var AgenteServicio $ag */
            $ag = AgenteServicio::query()->lockForUpdate()->findOrFail((int) $r->agente_servicio_id);
            if ((int) $ag->empresa_id !== (int) $r->empresa_id) {
                throw new DomainException('El agente no pertenece a la empresa.');
            }
            $this->aplicarDeltaAgente($ag, $baseMoneda, -$montoBase);

            // Guardar foto
            $path = null;
            if ($foto) {
                $path = $foto->store("empresas/{$r->empresa_id}/agente_presupuestos/rendiciones", 'public');
            }

            $mov = RendicionMovimiento::create([
                'empresa_id'  => (int) $r->empresa_id,
                'rendicion_id'=> (int) $r->id,
                'tipo'        => $tipo,
                'fecha'       => $data['mov_fecha'] ?? null,

                'entidad_id'       => $tipo === 'COMPRA' ? (int) ($data['mov_entidad_id'] ?? 0) : null,
                'proyecto_id'      => $tipo === 'COMPRA' ? (int) ($data['mov_proyecto_id'] ?? 0) : null,
                'tipo_comprobante' => $tipo === 'COMPRA' ? ($data['mov_tipo_comprobante'] ?? null) : null,
                'nro_comprobante'  => $tipo === 'COMPRA' ? ($data['mov_nro_comprobante'] ?? null) : null,

                'banco_id'        => $tipo === 'DEVOLUCION' ? (int) ($data['mov_banco_id'] ?? 0) : null,
                'nro_transaccion' => $tipo === 'DEVOLUCION' ? ($data['mov_nro_transaccion'] ?? null) : null,

                'moneda'      => $movMoneda,
                'tipo_cambio' => $movMoneda !== $baseMoneda ? ((float) ($data['mov_tipo_cambio'] ?? 0)) : null,

                'monto'      => $monto,
                'monto_base' => $montoBase,

                'foto_path'   => $path,
                'observacion' => $data['mov_observacion'] ?? null,
                'active'      => true,
            ]);

            $this->recalcularTotales($r);

            return $mov->fresh();
        });
    }

    // =========================================================
    // ELIMINAR MOVIMIENTO
    // =========================================================
    public function eliminarMovimiento(Rendicion $rendicion, int $movimientoId, User $user): void
    {
        DB::transaction(function () use ($rendicion, $movimientoId, $user) {
            /** @var Rendicion $r */
            $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

            if (!$r->active) {
                throw new DomainException('La rendición está inactiva.');
            }

            $estabaCerrado = $r->estado === 'cerrado';

            /** @var RendicionMovimiento $m */
            $m = RendicionMovimiento::query()
                ->lockForUpdate()
                ->where('rendicion_id', $r->id)
                ->where('id', $movimientoId)
                ->firstOrFail();

            // Revertir banco si era DEVOLUCION
            if ($m->tipo === 'DEVOLUCION') {
                if (empty($m->banco_id)) {
                    throw new DomainException('La devolución no tiene banco asociado.');
                }

                /** @var Banco $b */
                $b = Banco::query()->lockForUpdate()->findOrFail((int) $m->banco_id);

                if ((int) $b->empresa_id !== (int) $r->empresa_id) {
                    throw new DomainException('El banco no pertenece a la empresa.');
                }

                $montoMov   = round((float) ($m->monto ?? 0), 2);
                $saldoBanco = round((float) ($b->monto ?? 0), 2);

                if ($saldoBanco < $montoMov) {
                    throw new DomainException(
                        "El saldo del banco quedaría negativo al revertir. Saldo actual: {$saldoBanco} {$b->moneda}.",
                    );
                }

                $b->monto = round($saldoBanco - $montoMov, 2);
                $b->save();
            }

            // Revertir saldo del agente
            $montoBase = round((float) ($m->monto_base ?? 0), 2);
            if ($montoBase <= 0) {
                throw new DomainException('Monto base inválido.');
            }

            /** @var AgenteServicio $ag */
            $ag = AgenteServicio::query()->lockForUpdate()->findOrFail((int) $r->agente_servicio_id);
            if ((int) $ag->empresa_id !== (int) $r->empresa_id) {
                throw new DomainException('El agente no pertenece a la empresa.');
            }

            $this->aplicarDeltaAgente($ag, (string) $r->moneda, +$montoBase);

            // Eliminar foto si existe
            if (!empty($m->foto_path)) {
                Storage::disk('public')->delete($m->foto_path);
            }

            $m->delete();

            $this->recalcularTotales($r);

            // Si estaba cerrado, reabrirlo
            if ($estabaCerrado) {
                $r->estado      = 'abierto';
                $r->fecha_cierre = null;
                $r->save();
            }
        });
    }

    // =========================================================
    // CERRAR RENDICIÓN
    // =========================================================
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

            if (round((float) ($r->saldo_por_rendir ?? 0), 2) > 0) {
                throw new DomainException('No se puede cerrar: todavía hay saldo por rendir.');
            }

            $r->estado       = 'cerrado';
            $r->fecha_cierre = now()->toDateString();
            $r->save();
        });
    }

    // =========================================================
    // RECALCULAR TOTALES
    // =========================================================
    public function recalcularTotales(Rendicion $rendicion): void
    {
        $r = Rendicion::query()->lockForUpdate()->findOrFail($rendicion->id);

        $sumComprasBase = round(
            (float) RendicionMovimiento::query()
                ->where('rendicion_id', $r->id)->where('tipo', 'COMPRA')->where('active', true)->sum('monto_base'),
            2,
        );

        $sumDevolBase = round(
            (float) RendicionMovimiento::query()
                ->where('rendicion_id', $r->id)->where('tipo', 'DEVOLUCION')->where('active', true)->sum('monto_base'),
            2,
        );

        $rendidoTotal = round($sumComprasBase + $sumDevolBase, 2);
        $presTotal    = round((float) ($r->monto ?? 0), 2);
        $saldo        = round(max(0, $presTotal - $rendidoTotal), 2);

        $r->rendido_total    = $rendidoTotal;
        $r->saldo_por_rendir = $saldo;
        $r->save();
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================
    private function generarNroRendicion(int $empresaId): string
    {
        $year = now()->format('Y');
        $seq  = (int) Rendicion::query()
            ->where('empresa_id', $empresaId)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count() + 1;

        return sprintf('PR-%d-%s-%04d', $empresaId, $year, $seq);
    }

    private function aplicarDeltaAgente(AgenteServicio $agente, string $monedaBase, float $deltaBase): void
    {
        $monedaBase = strtoupper(trim($monedaBase));
        $deltaBase  = round($deltaBase, 2);

        if ($deltaBase === 0.0) {
            return;
        }

        if ($monedaBase === 'USD') {
            $actual = round((float) ($agente->saldo_usd ?? 0), 2);
            $nuevo  = round($actual + $deltaBase, 2);
            if ($nuevo < 0) {
                throw new DomainException("Saldo insuficiente del agente. Disponible: {$actual} USD.");
            }
            $agente->saldo_usd = $nuevo;
            $agente->save();
            return;
        }

        if ($monedaBase === 'BOB') {
            $actual = round((float) ($agente->saldo_bob ?? 0), 2);
            $nuevo  = round($actual + $deltaBase, 2);
            if ($nuevo < 0) {
                throw new DomainException("Saldo insuficiente del agente. Disponible: {$actual} BOB.");
            }
            $agente->saldo_bob = $nuevo;
            $agente->save();
            return;
        }

        throw new DomainException('Moneda base inválida para saldo del agente.');
    }

    private function convertirAMonedaBase(float $monto, string $monedaMovimiento, string $monedaBase, $tipoCambio): float
    {
        $monto = round($monto, 2);

        if ($monedaMovimiento === $monedaBase) {
            return $monto;
        }

        $tc = (float) $tipoCambio;
        if ($tc <= 0) {
            throw new DomainException('Tipo de cambio obligatorio cuando la moneda difiere.');
        }

        if ($monedaBase === 'BOB' && $monedaMovimiento === 'USD') {
            return round($monto * $tc, 2);
        }

        if ($monedaBase === 'USD' && $monedaMovimiento === 'BOB') {
            return round($monto / $tc, 2);
        }

        throw new DomainException('Conversión de moneda no soportada.');
    }
}

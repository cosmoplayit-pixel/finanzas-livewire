<?php

namespace App\Services;

use App\Models\Factura;

class FacturaFinance
{
    public static function retencionTotal(Factura $factura): float
    {
        return (float) ($factura->retencion ?? 0);
    }

    public static function pagadoNormal(Factura $factura): float
    {
        if ($factura->relationLoaded('pagos')) {
            return (float) $factura->pagos->where('tipo', 'normal')->sum('monto');
        }

        return (float) $factura->pagos()->where('tipo', 'normal')->sum('monto');
    }

    public static function pagadoRetencion(Factura $factura): float
    {
        if ($factura->relationLoaded('pagos')) {
            return (float) $factura->pagos->where('tipo', 'retencion')->sum('monto');
        }

        return (float) $factura->pagos()->where('tipo', 'retencion')->sum('monto');
    }

    public static function retencionPendiente(Factura $factura): float
    {
        $total = self::retencionTotal($factura);
        $pagado = self::pagadoRetencion($factura);

        return max(0, round($total - $pagado, 2));
    }

    public static function neto(Factura $factura): float
    {
        $facturado = (float) ($factura->monto_facturado ?? 0);
        $ret = self::retencionTotal($factura);

        return max(0, round($facturado - $ret, 2));
    }

    public static function saldo(Factura $factura): float
    {
        $neto = self::neto($factura);
        $pn = self::pagadoNormal($factura);

        return max(0, round($neto - $pn, 2));
    }

    public static function puedePagarRetencion(Factura $factura): bool
    {
        return self::saldo($factura) == 0.0 && self::retencionPendiente($factura) > 0.0;
    }

    // =========================
    // ESTADOS
    // =========================
    public static function estadoPagoKey(Factura $factura): string
    {
        $neto = self::neto($factura);
        $pn = self::pagadoNormal($factura);
        $saldo = self::saldo($factura);

        if ($neto <= 0) {
            return 'pagada_neto';
        }
        if ($pn <= 0) {
            return 'pendiente';
        }
        if ($saldo > 0) {
            return 'parcial';
        }

        return 'pagada_neto';
    }

    public static function estadoPagoLabel(Factura $factura): string
    {
        return match (self::estadoPagoKey($factura)) {
            'pendiente' => 'Pendiente',
            'parcial' => 'Parcial',
            default => 'Pagada (Neto)',
        };
    }

    public static function estadoRetencionKey(Factura $factura): ?string
    {
        $rt = self::retencionTotal($factura);
        if ($rt <= 0) {
            return null;
        }

        return self::retencionPendiente($factura) > 0 ? 'retencion_pendiente' : 'retencion_pagada';
    }

    public static function estadoRetencionLabel(Factura $factura): ?string
    {
        return match (self::estadoRetencionKey($factura)) {
            'retencion_pendiente' => 'Retención pendiente',
            'retencion_pagada' => 'Retención pagada',
            default => null,
        };
    }

    public static function estaCerrada(Factura $factura): bool
    {
        return self::saldo($factura) == 0.0 && self::retencionPendiente($factura) == 0.0;
    }

    public static function pagadoTotal(Factura $factura): float
    {
        return max(0, round(self::pagadoNormal($factura) + self::pagadoRetencion($factura), 2));
    }

    public static function porcentajePago(Factura $factura): float
    {
        $neto = self::neto($factura);
        if ($neto <= 0) {
            return 100;
        }

        $pagado = self::pagadoNormal($factura);
        return min(100, round(($pagado / $neto) * 100, 0));
    }
}

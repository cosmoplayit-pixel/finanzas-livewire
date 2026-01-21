<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Models\Banco;
use App\Models\Factura;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        view()->composer('components.layouts.app.sidebar', function ($view) {
            $user = auth()->user();

            // Evitar consultas si no hay sesión
            if (!$user) {
                $view->with('navCounts', []);
                return;
            }

            $navCounts = [];

            // ===== PLATAFORMA =====
            if ($user->can('users.view')) {
                $navCounts['usuarios'] = User::where('active', true)->count();
            }

            if ($user->can('roles.view')) {
                $navCounts['roles'] = Role::count();
            }

            if ($user->can('empresas.view')) {
                $navCounts['empresas'] = Empresa::where('active', true)->count();
            }

            // ===== GESTIÓN FINANCIERA =====

            // Empresa del usuario (null para Admin si no aplica)
            $empresaId = $user->empresa_id;

            // ENTIDADES
            if ($user->can('entidades.view')) {
                $navCounts['entidades'] = Entidad::where('active', true)
                    ->when(!$user->hasRole('Administrador'), function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    })
                    ->count();
            }

            // PROYECTOS
            if ($user->can('proyectos.view')) {
                $navCounts['proyectos'] = Proyecto::where('active', true)
                    ->when(!$user->hasRole('Administrador'), function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    })
                    ->count();
            }

            // BANCOS
            if ($user->can('bancos.view')) {
                $navCounts['bancos'] = Banco::where('active', true)
                    ->when(!$user->hasRole('Administrador'), function ($q) use ($empresaId) {
                        $q->where('empresa_id', $empresaId);
                    })
                    ->count();
            }

            // FACTURAS
            if ($user->can('facturas.view')) {
                // ==== Expresiones (equivalentes a tu FacturaFinance) ====
                $retExpr = 'COALESCE(facturas.retencion, 0)';
                $factExpr = 'COALESCE(facturas.monto_facturado, 0)';

                $pagadoNormalSub = "(
                    SELECT COALESCE(SUM(fp.monto), 0)
                    FROM factura_pagos fp
                    WHERE fp.factura_id = facturas.id AND fp.tipo = 'normal'
                )";

                $pagadoRetSub = "(
                    SELECT COALESCE(SUM(fp.monto), 0)
                    FROM factura_pagos fp
                    WHERE fp.factura_id = facturas.id AND fp.tipo = 'retencion'
                )";

                // saldo = (monto_facturado - retencion) - pagado_normal
                $saldoExpr = "({$factExpr} - {$retExpr}) - {$pagadoNormalSub}";

                // retencion_pendiente = retencion - pagado_retencion
                $retPendExpr = "({$retExpr}) - {$pagadoRetSub}";

                $navCounts['facturas'] = Factura::query()
                    ->where('facturas.active', true) // ✅ SOLO ACTIVAS

                    // ✅ Deben saldo o retención (ABIERTO)
                    // Usa > 0.01 para evitar falsos positivos por decimales
                    ->where(function ($q) use ($saldoExpr, $retPendExpr) {
                        $q->whereRaw("{$saldoExpr} > 0.01")->orWhereRaw("{$retPendExpr} > 0.01");
                    })

                    // ✅ Multiempresa vía proyecto (porque facturas no tiene empresa_id)
                    ->when(!$user->hasRole('Administrador'), function ($q) use ($empresaId) {
                        $q->whereHas('proyecto', function ($p) use ($empresaId) {
                            $p->where('empresa_id', $empresaId);
                        });
                    })
                    ->count();
            }

            $view->with('navCounts', $navCounts);
        });
    }
}

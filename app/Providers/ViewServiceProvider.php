<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Proyecto;
use App\Models\Banco;
use Spatie\Permission\Models\Role;

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

            $view->with('navCounts', $navCounts);
        });
    }
}

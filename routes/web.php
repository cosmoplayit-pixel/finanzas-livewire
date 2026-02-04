<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Empresas;
use App\Livewire\Admin\Entidades;
use App\Livewire\Admin\Proyectos;
use App\Livewire\Admin\Bancos;
use App\Livewire\Admin\Facturas;
use App\Livewire\Admin\AgentesServicio;
use App\Livewire\Admin\AgentePresupuestos;
use App\Livewire\Admin\BoletasGarantia;

Route::get('/', fn() => redirect()->route('login'))->name('home');

/**
 * Dashboard / Panel
 * ✅ auth + verified + active
 */
Route::get('panel', Dashboard::class)
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

/**
 * Rutas protegidas
 * ✅ auth + active
 */
Route::middleware(['auth', 'active'])->group(function () {
    // =======================
    // Ajustes de Perfil (Volt)
    // =======================
    Route::redirect('ajustes', 'ajustes/perfil');

    Volt::route('ajustes/perfil', 'settings.profile')->name('profile.edit');

    Volt::route('ajustes/contrasena', 'settings.password')->name('user-password.edit');

    Volt::route('ajustes/apariencia', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication() &&
                Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // =======================
    // USUARIOS
    // =======================
    Route::middleware(['permission:users.view'])
        ->get('/usuarios', Usuarios::class)
        ->name('usuarios');

    // =======================
    // ROLES
    // =======================
    Route::middleware(['permission:roles.view'])
        ->get('/roles', Roles::class)
        ->name('roles');

    // =======================
    // EMPRESAS
    // =======================
    Route::middleware(['permission:empresas.view'])
        ->get('/empresas', Empresas::class)
        ->name('empresas');

    // =======================
    // ENTIDADES
    // =======================
    Route::middleware(['permission:entidades.view'])
        ->get('/entidades', Entidades::class)
        ->name('entidades');

    // =======================
    // PROYECTOS
    // =======================
    Route::middleware(['permission:proyectos.view'])
        ->get('/proyectos', Proyectos::class)
        ->name('proyectos');

    // =======================
    // FACTURAS
    // =======================
    Route::middleware(['permission:facturas.view'])
        ->get('/facturas', Facturas::class)
        ->name('facturas');

    // =======================
    // BANCOS
    // =======================
    Route::middleware(['permission:bancos.view'])
        ->get('/bancos', Bancos::class)
        ->name('bancos');

    // =======================
    // AGENTES DE SERVICIO
    // =======================
    Route::middleware(['permission:agentes_servicio.view'])
        ->get('/agentes_servicio', AgentesServicio::class)
        ->name('agentes_servicio');

    // =======================
    // AGENTE PRESUPUESTOS + RENDICIÓN (UNIFICADO)
    // =======================
    Route::middleware(['permission:agente_presupuestos.view'])->group(function () {
        Route::get('/agente_presupuestos', AgentePresupuestos::class)->name('agente_presupuestos');
    });

    // =======================
    // Boleta de Garantia
    // =======================
    Route::middleware(['permission:boletas_garantia.view'])->group(function () {
        Route::get('/boletas_garantia', BoletasGarantia::class)->name('boletas_garantia');
    });
});

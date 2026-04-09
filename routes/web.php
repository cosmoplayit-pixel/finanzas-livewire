<?php

use App\Livewire\Admin\AgentesServicio;
use App\Livewire\Admin\Bancos;
use App\Livewire\Admin\BoletasGarantia\Index as BoletasGarantiaIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Empresas;
use App\Livewire\Admin\Entidades;
use App\Livewire\Admin\Facturas;
use App\Livewire\Admin\Herramientas;
use App\Livewire\Admin\Inversiones\Index as InversionesIndex;
use App\Livewire\Admin\PrestamosHerramientas;
use App\Livewire\Admin\Presupuestos\Index as PresupuestosIndex;
use App\Livewire\Admin\Proyectos;
use App\Livewire\Admin\Proyectos\Resumen;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Transacciones;
use App\Livewire\Admin\Usuarios;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', fn () => redirect()->route('login'))->name('home');

// Livewire update endpoint only accepts POST; redirect stale GET navigations
Route::get('/livewire/update', fn () => redirect()->route('dashboard'));

Route::get('panel', Dashboard::class)
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::redirect('ajustes', 'ajustes/perfil');

    Volt::route('ajustes/perfil', 'settings.profile')->name('profile.edit');

    Volt::route('ajustes/contrasena', 'settings.password')->name('user-password.edit');

    Volt::route('ajustes/apariencia', 'settings.appearance')->name('appearance.edit');

    Route::middleware(['permission:users.view'])
        ->get('/usuarios', Usuarios::class)
        ->name('usuarios');

    // ROLES
    Route::middleware(['permission:roles.view'])
        ->get('/roles', Roles::class)
        ->name('roles');

    // EMPRESAS
    Route::middleware(['permission:empresas.view'])
        ->get('/empresas', Empresas::class)
        ->name('empresas');

    // CLIENTES
    Route::middleware(['permission:entidades.view'])
        ->get('/clientes', Entidades::class)
        ->name('clientes');

    // PROYECTOS
    Route::middleware(['permission:proyectos.view'])
        ->get('/proyectos', Proyectos::class)
        ->name('proyectos');

    Route::middleware(['permission:proyectos.resumen'])
        ->get('/proyectos/resumen', Resumen::class)
        ->name('proyectos.resumen');

    // HERRAMIENTAS
    Route::middleware(['permission:herramientas.view'])->group(function () {
        Route::get('/herramientas', Herramientas::class)->name('herramientas');
        Route::get('/prestamos_herramientas', PrestamosHerramientas::class)->name('prestamos_herramientas');
    });

    // FACTURAS
    Route::middleware(['permission:facturas.view'])
        ->get('/facturas', Facturas::class)
        ->name('facturas');

    // BANCOS
    Route::middleware(['permission:bancos.view'])
        ->get('/bancos', Bancos::class)
        ->name('bancos');

    // AGENTES DE SERVICIO
    Route::middleware(['permission:agentes_servicio.view'])
        ->get('/agentes_servicio', AgentesServicio::class)
        ->name('agentes_servicio');

    // AGENTE PRESUPUESTOS + RENDICIÓN (UNIFICADO)
    Route::middleware(['permission:agente_presupuestos.view'])->group(function () {
        Route::get('/agente_presupuestos', PresupuestosIndex::class)->name('agente_presupuestos');
    });

    // BOLETAS DE GARANTÍA
    Route::middleware(['permission:boletas_garantia.view'])->group(function () {
        Route::get('/boletas_garantia', BoletasGarantiaIndex::class)->name('boletas_garantia');
    });

    // INVERSIONES
    Route::middleware(['permission:inversiones.view'])->group(function () {
        Route::get('/inversiones', InversionesIndex::class)->name('inversiones');
    });

    // TRANSACCIONES
    Route::middleware(['permission:transacciones.view'])->group(function () {
        Route::get('/transacciones', Transacciones::class)->name('transacciones');
    });
});

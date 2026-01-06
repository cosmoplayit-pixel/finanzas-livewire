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
        ->get('/admin/roles', Roles::class)
        ->name('admin.roles');

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
    // BANCOS
    // =======================
    Route::middleware(['permission:bancos.view'])
        ->get('/bancos', Bancos::class)
        ->name('bancos');
});

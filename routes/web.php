<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Admin\Empresas;
use App\Livewire\Admin\Entidades;
use App\Livewire\Admin\Proyectos;

Route::get('/', fn() => redirect()->route('login'))->name('home');

Route::view('panel', view: 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Ajuste de Perfil (Volt)
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

    // Ruta de Usuarios
    Route::middleware(['auth', 'role:Administrador'])
        ->get('/usuarios', Usuarios::class)
        ->name('usuarios');

    // Ruta de empresas
    Route::middleware(['auth', 'role:Administrador|Manager'])
        ->get('/empresas', Empresas::class)
        ->name('empresas');

    // Ruta de Entidades
    Route::middleware(['auth', 'role:Administrador|Manager'])
        ->get('/entidades', Entidades::class)
        ->name('entidades');

    // Ruta de Proyectos
    Route::middleware(['auth', 'role:Administrador|Manager'])
        ->get('/proyectos', Proyectos::class)
        ->name('proyectos');

    // Contabilidad
    Route::middleware('role:Contabilidad')->group(function () {
        Route::view('/contabilidad', 'contabilidad')->name('contabilidad');
    });

    // Finanzas
    Route::middleware('role:Finanzas')->group(function () {
        Route::view('/finanzas', 'finanzas')->name('finanzas');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

use App\Livewire\Admin\Usuarios;
use App\Livewire\Admin\Empresas;
use App\Livewire\Admin\Entidades;
use App\Livewire\Admin\Proyectos;

Route::get('/', fn() => redirect()->route('login'))->name('home');

// Dashboard / Panel (✅ agrega middleware 'active')
Route::view('panel', 'dashboard')
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

/**
 * Rutas protegidas (✅ auth + active a todo el bloque)
 * - active expulsa al usuario desactivado en su siguiente request.
 */
Route::middleware(['auth', 'active'])->group(function () {
    // Ajustes de Perfil (Volt)
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

    // Admin: Usuarios (role ya incluye auth por el grupo, no hace falta repetirlo)
    Route::middleware(['role:Administrador'])
        ->get('/usuarios', Usuarios::class)
        ->name('usuarios');

    // Admin/Manager: Empresas
    Route::middleware(['role:Administrador'])
        ->get('/empresas', Empresas::class)
        ->name('empresas');

    // Admin/Manager: Entidades
    Route::middleware(['role:Administrador|Manager'])
        ->get('/entidades', Entidades::class)
        ->name('entidades');

    // Admin/Manager: Proyectos
    Route::middleware(['role:Administrador|Manager'])
        ->get('/proyectos', Proyectos::class)
        ->name('proyectos');
});

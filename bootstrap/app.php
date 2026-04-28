<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\ValidateTurnstile;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de Spatie
        $middleware->alias([
            // Reemplaza el Authenticate de Laravel para que las requests de Livewire
            // reciban 401 en lugar de redirigir al login (evita el 405 en /livewire/update)
            'auth' => Authenticate::class,

            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,

            // ✅ Alias para bloquear usuarios inactivos
            'active' => CheckUserActive::class,

            // ✅ Alias para validar Turnstile captcha en login
            'turnstile' => ValidateTurnstile::class,
        ]);

        // ✅ Session timeout por inactividad aplicado a todo el grupo web
        $middleware->appendToGroup('web', SessionTimeout::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

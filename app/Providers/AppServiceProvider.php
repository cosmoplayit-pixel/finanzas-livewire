<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Si la URL "intended" tras login apunta a una ruta interna de Livewire
        // (ej: /livewire/update), el browser haría un GET que devuelve 405.
        // La limpiamos antes de que Fortify haga el redirect post-login.
        Event::listen(Login::class, function () {
            $intended = session('url.intended', '');
            if (str_contains($intended, '/livewire/')) {
                session()->forget('url.intended');
            }
        });
    }
}

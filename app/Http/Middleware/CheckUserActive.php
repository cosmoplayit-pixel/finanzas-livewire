<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // A) Usuario inactivo => logout
            if (!(bool) $user->active) {
                auth()->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Su cuenta fue desactivada. Contacte al administrador.',
                    ]);
            }

            // B) Rol inactivo asignado => logout
            // Cacheado en archivo por 5 min para no ejecutar una query extra en cada request de Livewire.
            // Al desactivar un rol, llamar: Cache::store('file')->forget("user_{id}_role_check")
            $hasInactiveRole = Cache::store('file')->remember(
                "user_{$user->id}_role_check",
                now()->addMinutes(5),
                fn () => $user->roles()->where('active', false)->exists()
            );

            if ($hasInactiveRole) {
                auth()->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Su rol fue desactivado. Contacte al administrador.',
                    ]);
            }
        }

        return $next($request);
    }
}

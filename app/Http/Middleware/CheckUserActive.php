<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            if ($user->roles()->where('active', false)->exists()) {
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

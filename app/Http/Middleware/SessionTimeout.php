<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Tiempo de inactividad permitido en minutos.
     * Usa SESSION_LIFETIME del .env o 30 minutos como fallback.
     */
    protected int $timeout;

    public function __construct()
    {
        $this->timeout = (int) config('session.lifetime', 30);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('_last_activity');

            if ($lastActivity && (time() - $lastActivity) > ($this->timeout * 60)) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.']);
            }

            // Actualizar timestamp de última actividad
            session(['_last_activity' => time()]);
        }

        return $next($request);
    }
}

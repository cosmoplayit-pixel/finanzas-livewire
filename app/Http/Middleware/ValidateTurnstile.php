<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ValidateTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo valida en la ruta POST de login
        if ($request->isMethod('POST') && $request->routeIs('login.store')) {
            $token = $request->input('cf-turnstile-response');
            $secret = config('turnstile.secret_key');

            // Si no hay site_key configurada (claves de prueba vacías), omitir validación
            if (empty($secret) || str_starts_with($secret, '1x000')) {
                return $next($request);
            }

            $response = Http::asForm()->post(config('turnstile.url'), [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            if (! ($result['success'] ?? false)) {
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['cf-turnstile-response' => 'La verificación de seguridad falló. Intenta de nuevo.']);
            }
        }

        return $next($request);
    }
}

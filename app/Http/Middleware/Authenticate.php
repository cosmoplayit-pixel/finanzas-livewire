<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Livewire update requests send X-Livewire header but NOT Accept: application/json,
     * so the default expectsJson() returns false and would store /livewire/update as the
     * "intended" URL. After login, Fortify would redirect the browser there via GET,
     * causing a 405 Method Not Allowed error.
     *
     * By returning null for Livewire requests, we force a 401 response instead,
     * letting Livewire's client-side error handling take over.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->hasHeader('X-Livewire') || $request->is('livewire/*')) {
            return null;
        }

        return route('login');
    }
}

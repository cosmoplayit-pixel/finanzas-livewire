{{-- resources/views/auth/login.blade.php --}}
<x-layouts.auth>
    <style>
        /* Quitar scroll (layout + página) */
        html,
        body {
            height: 100%;
            overflow: hidden !important;
        }
    </style>

    <div class="relative h-screen w-screen overflow-hidden bg-white dark:bg-zinc-950">

        @include('partials.nodes-background')

        {{-- ===================== CONTENIDO CENTRADO (SIN SCROLL) ===================== --}}
        <div class="fixed inset-0 z-10 flex items-center justify-center px-4">
            <div class="w-full max-w-md">

                {{-- Brand / Title --}}
                <div class="text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        {{ __('Accede a tu cuenta') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                        {{ __('Control financiero seguro y trazable. Inicia sesión para continuar.') }}
                    </p>
                </div>

                {{-- Status --}}
                <x-auth-session-status class="mt-5 text-center" :status="session('status')" />

                {{-- Card --}}
                <div
                    class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6">
                        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                            @csrf

                            {{-- Email --}}
                            <flux:input name="email" :label="__('Correo electrónico')" :value="old('email')"
                                type="email" required autofocus autocomplete="email"
                                placeholder="correo@ejemplo.com" />

                            {{-- Password --}}
                            <flux:input name="password" :label="__('Contraseña')" type="password" required
                                autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />

                            <div class="flex items-center justify-between gap-3">
                                <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember', true)" />
                                <a href="{{ route('password.request') }}"
                                   class="text-xs text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            {{-- Cloudflare Turnstile CAPTCHA --}}
                            <div class="flex justify-center">
                                <div class="cf-turnstile" data-sitekey="{{ config('turnstile.site_key') }}"
                                    data-theme="light" data-language="es">
                                </div>
                            </div>

                            @error('cf-turnstile-response')
                                <p class="text-xs text-red-500 text-center -mt-2">
                                    ⚠ {{ $message }}
                                </p>
                            @enderror

                            {{-- Submit --}}
                            <flux:button variant="primary" type="submit" class="w-full cursor-pointer justify-center"
                                data-test="login-button">
                                {{ __('Ingresar') }}
                            </flux:button>
                        </form>
                    </div>

                    {{-- Footer note --}}
                    <div class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 text-center">
                        <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed">
                            {{ __('Este sistema prioriza seguridad, control de accesos y trazabilidad. Si no tienes acceso, contacta a un administrador.') }}
                        </p>
                    </div>
                </div>

                {{-- Micro footer --}}
                <p class="mt-6 text-center text-xs text-gray-400">
                    © {{ date('Y') }} — Gestión Financiera
                </p>

            </div>
        </div>

        {{-- Cloudflare Turnstile Script --}}
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

        <style>
            header, nav { display: none !important; }
            .min-h-screen>.pt-6, .min-h-screen>.sm\:pt-0 { display: none !important; }
        </style>

    </div>
</x-layouts.auth>

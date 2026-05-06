<x-layouts.auth>
    <style>
        html, body { height: 100%; overflow: hidden !important; }
    </style>

    <div class="relative h-screen w-screen overflow-hidden bg-white dark:bg-zinc-950">

        @include('partials.nodes-background')

        {{-- Contenido centrado --}}
        <div class="fixed inset-0 z-10 flex items-center justify-center px-4">
            <div class="w-full max-w-md">

                {{-- Logo + Título --}}
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        ¿Olvidaste tu contraseña?
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                        Ingresa tu correo y te enviaremos un enlace para restablecerla.
                    </p>
                </div>

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6 space-y-5">

                        @if (session('status'))
                            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-4 py-3">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                                    {{ __('Correo enviado. Revisa tu bandeja de entrada.') }}
                                </p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                            @csrf

                            <flux:input
                                name="email"
                                :label="__('Correo electrónico')"
                                type="email"
                                required
                                autofocus
                                placeholder="correo@ejemplo.com"
                            />

                            @error('email')
                                <p class="text-xs text-red-500 -mt-3">{{ $message }}</p>
                            @enderror

                            <flux:button variant="primary" type="submit" class="w-full">
                                {{ __('Enviar enlace de recuperación') }}
                            </flux:button>
                        </form>

                        <div class="text-center text-sm text-gray-500 dark:text-zinc-400">
                            <span>¿Ya la recordaste? </span>
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 font-medium transition-colors">
                                Inicia sesión
                            </a>
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <p class="mt-6 text-center text-xs text-gray-400">
                    © {{ date('Y') }} — Gestión Financiera
                </p>

            </div>
        </div>

        <style>
            header, nav { display: none !important; }
        </style>
    </div>
</x-layouts.auth>

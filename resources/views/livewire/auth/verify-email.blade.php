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
                        Verifica tu correo
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                        Te enviamos un enlace de verificación. Revisa tu bandeja de entrada.
                    </p>
                </div>

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6 space-y-4">

                        @if (session('status') == 'verification-link-sent')
                            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-4 py-3">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                                    ¡Correo reenviado! Revisa tu bandeja de entrada.
                                </p>
                            </div>
                        @endif

                        <p class="text-sm text-gray-600 dark:text-zinc-400 text-center">
                            ¿No recibiste el correo? Haz clic en el botón para reenviar el enlace de verificación.
                        </p>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <flux:button type="submit" variant="primary" class="w-full">
                                Reenviar correo de verificación
                            </flux:button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="text-center">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200 transition-colors cursor-pointer">
                                Cerrar sesión
                            </button>
                        </form>

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

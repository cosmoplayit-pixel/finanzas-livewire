<x-layouts.auth>
    <style>
        html, body { height: 100%; overflow: hidden !important; }
    </style>

    <div class="relative h-screen w-screen overflow-hidden bg-white dark:bg-zinc-950">

        @include('partials.nodes-background')

        {{-- ===================== CONTENIDO CENTRADO ===================== --}}
        <div class="fixed inset-0 z-10 flex items-center justify-center px-4">
            <div class="w-full max-w-md">

                {{-- Brand / Title --}}
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        Código de Autenticación
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                        Ingresa el código de 6 dígitos de tu aplicación autenticadora.
                    </p>
                </div>

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6">
                        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5 text-center">
                            @csrf

                            <input type="hidden" name="recovery_code" value="">

                            <div class="flex items-center justify-center my-5">
                                <flux:otp
                                    length="6"
                                    name="code"
                                    label="Código OTP"
                                    label:sr-only
                                    class="mx-auto"
                                 />
                            </div>

                            @error('code')
                                <p class="text-xs text-red-500 text-center mt-2">{{ $message }}</p>
                            @enderror

                            <flux:button variant="primary" type="submit" class="w-full">
                                {{ __('Continuar') }}
                            </flux:button>
                        </form>
                    </div>
                </div>

                {{-- Micro footer --}}
                <p class="mt-6 text-center text-xs text-gray-400">
                    © {{ date('Y') }} — Gestión Financiera
                </p>

            </div>
        </div>

        <style>
            header, nav { display: none !important; }
            .min-h-screen>.pt-6, .min-h-screen>.sm\:pt-0 { display: none !important; }
        </style>

    </div>
</x-layouts.auth>

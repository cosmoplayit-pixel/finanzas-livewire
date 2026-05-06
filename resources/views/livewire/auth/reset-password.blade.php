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
                        Restablecer contraseña
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                        Ingresa tu nueva contraseña para recuperar el acceso.
                    </p>
                </div>

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6">
                        <form method="POST" action="{{ route('password.update') }}" class="space-y-5"
                            x-data="{
                                password: '',
                                confirmation: '',
                                get hasUpper() { return /[A-Z]/.test(this.password); },
                                get hasLower() { return /[a-z]/.test(this.password); },
                                get hasLength() { return this.password.length >= 8; },
                                get hasNumber() { return /[0-9]/.test(this.password); },
                                get matches() { return this.password === this.confirmation && this.confirmation !== ''; },
                            }">
                            @csrf
                            <input type="hidden" name="token" value="{{ request()->route('token') }}">

                            {{-- Correo --}}
                            <flux:input
                                name="email"
                                value="{{ request('email') }}"
                                :label="__('Correo electrónico')"
                                type="email"
                                required
                                autocomplete="email"
                            />

                            {{-- Nueva contraseña --}}
                            <div>
                                <flux:input
                                    name="password"
                                    :label="__('Nueva contraseña')"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Mínimo 8 caracteres"
                                    viewable
                                    x-model="password"
                                />

                                {{-- Indicadores en tiempo real --}}
                                <div x-show="password.length > 0" class="mt-2 space-y-1">
                                    <p class="text-xs flex items-center gap-1.5"
                                        :class="hasLength ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                                        <span x-text="hasLength ? '✓' : '✗'"></span> Mínimo 8 caracteres
                                    </p>
                                    <p class="text-xs flex items-center gap-1.5"
                                        :class="hasUpper ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                                        <span x-text="hasUpper ? '✓' : '✗'"></span> Al menos una mayúscula
                                    </p>
                                    <p class="text-xs flex items-center gap-1.5"
                                        :class="hasLower ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                                        <span x-text="hasLower ? '✓' : '✗'"></span> Al menos una minúscula
                                    </p>
                                    <p class="text-xs flex items-center gap-1.5"
                                        :class="hasNumber ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                                        <span x-text="hasNumber ? '✓' : '✗'"></span> Al menos un número
                                    </p>
                                </div>
                            </div>

                            {{-- Confirmar contraseña --}}
                            <div>
                                <flux:input
                                    name="password_confirmation"
                                    :label="__('Confirmar contraseña')"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Repite la contraseña"
                                    viewable
                                    x-model="confirmation"
                                />
                                <p x-show="confirmation.length > 0" class="mt-1 text-xs flex items-center gap-1.5"
                                    :class="matches ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                                    <span x-text="matches ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden'"></span>
                                </p>
                            </div>

                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ __('Restablecer contraseña') }}
                            </flux:button>
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

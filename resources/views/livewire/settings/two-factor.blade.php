<div>
    @section('title', 'Seguridad (2FA)')
    <section class="w-full">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Autenticación en Dos Pasos (2FA)')" :subheading="__('Añade seguridad adicional a tu cuenta usando el Autenticador de Google u otra aplicación compatible.')">
            <div class="mt-4 w-full">

                @if(! $this->user->two_factor_secret)

                    {{-- ======================== MODO: DESHABILITADO ======================== --}}
                    <div class="mb-5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="rounded-full bg-indigo-50 dark:bg-indigo-500/10 p-3 text-indigo-600 dark:text-indigo-400">
                                <flux:icon.shield-exclamation variant="outline" class="size-6" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white text-base">Autenticación no configurada</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                                    Cuando la autenticación de dos pasos está habilitada, se te pedirá un código seguro al iniciar sesión. Obtén el código desde la aplicación Google Authenticator en tu teléfono.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <flux:button variant="primary" wire:click="enableTwoFactorAuthentication" wire:loading.attr="disabled">
                                Habilitar Autenticación de Dos Pasos
                            </flux:button>
                        </div>
                    </div>

                @else

                    {{-- ======================== MODO: HABILITADO ======================== --}}
                    <div class="mb-5 rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-500/5 p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="rounded-full bg-emerald-100 dark:bg-emerald-500/20 p-3 text-emerald-600 dark:text-emerald-400">
                                <flux:icon.shield-check variant="solid" class="size-6" />
                            </div>
                            <div>
                                <h3 class="font-medium text-emerald-900 dark:text-emerald-400 text-base">Autenticación Habilitada</h3>
                                <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-500/80">
                                    Tu cuenta tiene una capa adicional de seguridad. Se te pedirá un código en cada inicio de sesión.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- QR Code: se muestra al recién habilitar (showingQrCode=true) --}}
                    @if($showingQrCode)
                        <div class="mb-5 rounded-xl border border-indigo-200 dark:border-indigo-500/20 bg-indigo-50/50 dark:bg-indigo-500/5 p-5 shadow-sm">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-base">⚠️ Importante: Escanea este código QR ahora</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-zinc-400">
                                Abre <strong>Google Authenticator</strong> en tu celular, toca el botón <strong>"+"</strong> y selecciona <strong>"Escanear código QR"</strong>. Apunta la cámara a este código.
                            </p>

                            <div class="mt-4 p-3 inline-block bg-white rounded-xl shadow-sm border border-gray-200">
                                {!! $this->user->twoFactorQrCodeSvg() !!}
                            </div>

                            <div class="mt-5">
                                <flux:button variant="primary" wire:click="$set('showingQrCode', false)">
                                    Ya escaneé el código QR
                                </flux:button>
                            </div>
                        </div>
                    @endif

                    {{-- Códigos de recuperación --}}
                    @if($showingRecoveryCodes)
                        <div class="mt-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm">
                            <div class="mt-4 flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 dark:text-white text-base">Códigos de Recuperación</h3>
                            <flux:button size="sm" variant="subtle" icon="clipboard" 
                                x-on:click="
                                    let codes = [];
                                    document.querySelectorAll('.recovery-code-item').forEach(el => codes.push(el.innerText.trim()));
                                    navigator.clipboard.writeText(codes.join('\n'));
                                    $dispatch('toast', { type: 'success', message: 'Códigos copiados al portapapeles' });
                                ">
                                Copiar todos
                            </flux:button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400">
                            Guarda estos códigos en un administrador de contraseñas seguro. Sirven para acceder a tu cuenta si pierdes tu teléfono.
                        </p>

                        <div class="grid grid-cols-2 gap-2 max-w-xl mt-4 p-4 font-mono text-sm bg-gray-50 dark:bg-zinc-900 border border-gray-100 dark:border-zinc-700 rounded-lg text-gray-700 dark:text-zinc-300">
                            @foreach ((array) $this->user->recoveryCodes() as $code)
                                <div class="recovery-code-item">{{ $code }}</div>
                            @endforeach
                        </div>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <flux:button wire:click="regenerateRecoveryCodes" wire:loading.attr="disabled">
                                    Regenerar Códigos
                                </flux:button>
                                <flux:button wire:click="$set('showingRecoveryCodes', false)">
                                    Ocultar Códigos
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <div class="mt-2 flex flex-wrap gap-3">
                            <flux:button wire:click="showRecoveryCodes" wire:loading.attr="disabled">
                                Mostrar Códigos de Recuperación
                            </flux:button>

                            <flux:button variant="danger" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                                Deshabilitar Autenticación
                            </flux:button>
                        </div>
                    @endif

                @endif

            </div>
        </x-settings.layout>
    </section>
</div>

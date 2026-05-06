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

                    {{-- QR Code: se muestra al recién habilitar --}}
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

                    {{-- Botón deshabilitar --}}
                    <div class="mt-2">
                        <flux:button variant="danger" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                            Deshabilitar Autenticación
                        </flux:button>
                    </div>

                @endif

            </div>
        </x-settings.layout>
    </section>
</div>

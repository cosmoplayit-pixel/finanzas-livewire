{{-- ===================== MODAL CREAR INVERSION ===================== --}}
<x-ui.modal wire:key="inversion-create-{{ $open ? 'open' : 'closed' }}" model="open" title="Nueva inversión"
    maxWidth="sm:max-w-xl md:max-w-4xl" onClose="close">

    <div class="space-y-3 sm:space-y-4">

        {{-- DATOS PRINCIPALES --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos principales</div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

                    {{-- Tipo --}}
                    <div class="col-span-1">
                        <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model.live="tipo"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Ninguno</option>
                            <option value="PRIVADO">Privado</option>
                            <option value="BANCO">Banco</option>
                        </select>
                        @error('tipo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>



                    {{-- Nombre --}}
                    <div class="col-span-2 lg:col-span-1">
                        <label class="block text-sm mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input wire:model.live="nombre_completo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('nombre_completo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="col-span-1">
                        <label class="block text-sm mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="fecha_inicio"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('fecha_inicio')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Banco (OCULTO hasta elegir tipo) --}}
                    @if ($showTipoSelectedFields)

                        {{-- Fecha vencimiento --}}
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Fecha vencimiento <span
                                    class="text-red-500">*</span></label>
                            <input type="date" wire:model="fecha_vencimiento" disabled
                                class="w-full cursor-not-allowed rounded-lg border px-3 py-2
                                       bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       opacity-80 focus:outline-none" />
                            @error('fecha_vencimiento')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Banco <span class="text-red-500">*</span></label>
                            <select wire:model.live="banco_id"
                                class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                                <option value="">Seleccione…</option>
                                @foreach ($this->bancos as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->nombre }} — {{ $b->numero_cuenta ?? '—' }} ({{ $b->moneda ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('banco_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Capital (OCULTO hasta elegir tipo) --}}
                    @if ($showTipoSelectedFields)
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Capital <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.defer="capital_formatted"
                                wire:blur="formatCapital" placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('capital')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- % Utilidad (solo PRIVADO) --}}
                    @if ($showPrivadoFields)
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">% Interes <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.defer="porcentaje_utilidad_formatted"
                                wire:blur="formatPorcentaje" placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('porcentaje_utilidad')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($showBancoFields)
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Tasa anual (%) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.defer="tasa_anual_formatted"
                                wire:blur="formatTasaAnual" placeholder="18,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('tasa_anual')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- PLAN (PRIVADO y BANCO): plazo/día --}}
                    @if ($showTipoSelectedFields)
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Plazo (meses) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" wire:model.defer="plazo_meses_formatted"
                                wire:blur="formatPlazo" placeholder="12"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('plazo_meses')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($showBancoFields)
                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Día de pago (1–28) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" wire:model.defer="dia_pago_formatted"
                                wire:blur="formatDiaPago" placeholder="1"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('dia_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif



                    {{-- Foto --}}
                    <div class="col-span-2 lg:col-span-3">
                        <x-ui.scanner model="comprobante" label="Foto del comprobante (opcional)" :file="$comprobante" />
                    </div>

                </div>
            </div>
        </div>

        {{-- Impacto banco --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto banco</div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2 border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</div>
                        <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2 border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Aumento</div>
                        <div class="text-sm text-green-600 font-semibold tabular-nums">
                            +{{ number_format((float) $saldo_banco_aumento_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>

                    <div
                        class="col-span-2 sm:col-span-1 rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2 border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</div>
                        <div
                            class="text-sm text-red-600 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @slot('footer')
        <div class="grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
            <button type="button" wire:click="close"
                class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="create" wire:loading.attr="disabled" wire:target="create,comprobante"
                class="px-4 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="create,comprobante">Guardar</span>
                <span wire:loading wire:target="create,comprobante">Procesando…</span>
            </button>
        </div>
    @endslot

</x-ui.modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('swal:error', (e) => {
            Swal.fire({
                icon: 'error',
                title: 'No se puede registrar',
                text: e.detail.message,
                confirmButtonText: 'Entendido',
            });
        });

        window.addEventListener('swal:success', (e) => {
            Swal.fire({
                icon: 'success',
                title: 'Listo',
                text: e.detail.message,
                timer: 1800,
                showConfirmButton: false,
            });
        });
    });
</script>

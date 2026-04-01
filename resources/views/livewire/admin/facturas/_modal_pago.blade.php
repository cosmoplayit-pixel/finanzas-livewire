<x-ui.modal wire:key="pago-modal" model="openPagoModal" title="Registrar Pago" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="closePago">
    <div class="space-y-4">
        {{-- Tipo / Método / Banco --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Tipo: <span class="text-red-500">*</span></label>
                <select wire:model.live="tipo" disabled
                    class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-neutral-800 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 opacity-80 cursor-not-allowed">
                    <option value="normal">Normal</option>
                    <option value="retencion">Retención</option>
                </select>
                @error('tipo')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Método de pago: <span class="text-red-500">*</span></label>
                <select wire:model="metodo_pago"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                    <option value="transferencia">Transferencia</option>
                    <option value="deposito">Depósito</option>
                    <option value="cheque">Cheque</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="qr">QR</option>
                    <option value="otro">Otro</option>
                </select>
                @error('metodo_pago')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Banco --}}
            <div>
                <label class="block text-sm mb-1">Banco destino: <span class="text-red-500">*</span></label>
                <select wire:model.live="banco_id"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                    <option value="">Seleccione...</option>
                    @foreach ($bancos as $b)
                        <option value="{{ $b->id }}">
                            {{ $b->nombre }} | {{ $b->numero_cuenta }} | {{ $b->moneda }}
                        </option>
                    @endforeach
                </select>
                @error('banco_id')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Monto --}}
            <div>
                <label class="block text-sm mb-1">Monto: <span class="text-red-500">*</span></label>
                <input type="text" inputmode="decimal" wire:model.lazy="monto_formatted" placeholder="0,00"
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 placeholder:text-gray-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-gray-500/40">

                @error('monto')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nro. Operación --}}
            <div>
                <label class="block text-sm mb-1">Nro. Operación (Opcional):</label>
                <input wire:model="nro_operacion"
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                @error('nro_operacion')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>


            {{-- Fecha de Pago --}}
            <div>
                <label class="block text-sm mb-1">Fecha de pago: <span class="text-red-500">*</span></label>
                <input type="datetime-local" wire:model="fecha_pago"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                @error('fecha_pago')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm mb-1">Observación (Opcional):</label>
            <textarea wire:model="observacion" rows="2"
                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300/60 dark:border-neutral-700/60 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40"></textarea>
            @error('observacion')
                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div x-data="{ uploading: false }" x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false">
            {{-- Comprobante (Imagen o PDF) --}}
            <div>
                <x-ui.scanner model="pago_foto_comprobante" label="Respaldo" :file="$pago_foto_comprobante" />
            </div>
        </div>

        {{-- Impacto Financiero --}}
        @if ($facturaId || $banco_id)
            <div
                class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-2">
                <div class="px-3 sm:px-4 py-1 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Impacto financiero
                    </div>
                </div>
                <div class="px-2 py-1 sm:p-4 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- FACTURA --}}
                    @if ($facturaId)
                        <div class="space-y-3 md:border-r md:border-gray-200 md:dark:border-neutral-700 md:pr-6">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $tipo === 'retencion' ? 'RETENCIÓN' : 'FACTURA' }}
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                                <span class="font-medium text-gray-900 dark:text-neutral-100">
                                    Bs {{ number_format((float) $preview_saldo_actual, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-red-600 dark:text-red-400">
                                <span>Abono a deuda</span>
                                <span class="font-medium">
                                    - {{ $monto_formatted ?: '0,00' }}
                                </span>
                            </div>
                            <div
                                class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                                <span
                                    class="font-bold {{ $preview_saldo_nuevo <= 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-neutral-100' }}">
                                    Bs {{ number_format((float) $preview_saldo_nuevo, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3 md:border-r md:border-gray-200 md:dark:border-neutral-700 md:pr-6">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">FACTURA
                            </div>
                            <div class="text-sm text-gray-500">-</div>
                        </div>
                    @endif

                    {{-- BANCO --}}
                    @if ($banco_id && $preview_banco_nombre)
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                BANCO {{ $preview_banco_moneda ? "({$preview_banco_moneda})" : '' }}
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                                <span class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ number_format((float) $preview_banco_actual, 2, ',', '.') }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm text-emerald-600 dark:text-emerald-400">
                                <span>Ingreso</span>
                                <span class="font-medium">
                                    + {{ $monto_formatted ?: '0,00' }}
                                </span>
                            </div>
                            <div
                                class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                                <span class="font-bold text-gray-900 dark:text-neutral-100">
                                    {{ number_format((float) $preview_banco_nuevo, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">BANCO</div>
                            <div class="text-sm text-gray-500">-</div>
                        </div>
                    @endif

                </div>
            </div>
        @endif
    </div>

    @slot('footer')
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3" x-data="{ uploading: false }"
            x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-error="uploading = false">
            <button type="button" @click="close()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="savePago" wire:loading.attr="disabled"
                wire:target="savePago,pago_foto_comprobante"
                x-bind:disabled="uploading || !$wire.tipo || !$wire.metodo_pago || !$wire.monto_formatted || !$wire.fecha_pago || ($wire
                    .metodo_pago !== 'efectivo' && (!$wire.banco_id)) || !$wire.pago_foto_comprobante"
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                <span x-show="!uploading" wire:loading.remove wire:target="savePago">Guardar</span>
                <span x-show="uploading" x-cloak>Subiendo…</span>
                <span wire:loading wire:target="savePago">Guardando…</span>
            </button>
        </div>
    @endslot
</x-ui.modal>

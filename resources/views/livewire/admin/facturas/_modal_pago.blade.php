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
                <label class="block text-sm mb-1">Nro. Operación: <span class="text-red-500">*</span></label>
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

        {{-- Comprobante (Imagen o PDF) --}}
        <div>
            <label class="block text-sm mb-1">Respaldo (Opcional):</label>
            <label
                class="group flex items-center justify-between w-full rounded-lg border border-dashed
                       border-gray-300/70 dark:border-neutral-700/70
                       bg-white dark:bg-neutral-900 px-4 py-2 cursor-pointer
                       hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg border border-gray-200/70 dark:border-neutral-700/70
                               bg-gray-50 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                        @if (
                            $pago_foto_comprobante &&
                                !is_string($pago_foto_comprobante) &&
                                strtolower($pago_foto_comprobante->getClientOriginalExtension()) === 'pdf')
                            {{-- Icono PDF --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                        @elseif ($pago_foto_comprobante && !is_string($pago_foto_comprobante))
                            {{-- Icono Imagen --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                        @else
                            {{-- Icono Upload --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600 dark:text-neutral-200"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">
                            Adjuntar archivo
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                            @if ($pago_foto_comprobante && !is_string($pago_foto_comprobante))
                                {{ $pago_foto_comprobante->getClientOriginalName() }}
                            @else
                                JPG, PNG o PDF (máx. 5MB)
                            @endif
                        </div>
                    </div>
                </div>

                <input type="file" wire:model.live="pago_foto_comprobante" accept=".jpg,.jpeg,.png,.pdf"
                    class="hidden" />
            </label>

            <div wire:loading wire:target="pago_foto_comprobante"
                class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                Subiendo archivo...
            </div>
            @error('pago_foto_comprobante')
                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
            @enderror

            @if ($pago_foto_comprobante && !is_string($pago_foto_comprobante))
                <div class="mt-2 text-right">
                    <button type="button" wire:click="$set('pago_foto_comprobante', null)"
                        class="text-xs text-red-500 hover:text-red-700 underline">
                        Quitar archivo
                    </button>
                </div>
            @endif
        </div>

        {{-- Impacto Financiero --}}
        @if ($facturaId || $banco_id)
            <div
                class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-2">
                <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Impacto financiero
                    </div>
                </div>
                <div class="p-3 sm:p-4 grid grid-cols-1 md:grid-cols-2 gap-6">

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

        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">
            <span class="text-red-500">*</span> Campos obligatorios.
        </p>
    </div>

    @slot('footer')
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">
            <button type="button" @click="close()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="savePago" wire:loading.attr="disabled"
                wire:target="savePago,pago_foto_comprobante"
                x-bind:disabled="!$wire.tipo || !$wire.metodo_pago || !$wire.monto_formatted || !$wire.fecha_pago || ($wire
                    .metodo_pago !== 'efectivo' && (!$wire.banco_id || !$wire.nro_operacion))"
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="savePago,pago_foto_comprobante">Guardar</span>
                <span wire:loading wire:target="pago_foto_comprobante">Subiendo…</span>
                <span wire:loading wire:target="savePago">Guardando…</span>
            </button>
        </div>
    @endslot
</x-ui.modal>

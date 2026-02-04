{{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
<x-ui.modal wire:key="boleta-garantia-devolucion-{{ $open ? 'open' : 'closed' }}" model="open"
    title="Registrar Devolución" maxWidth="sm:max-w-xl md:max-w-2xl" onClose="close">

    <div class="space-y-3 sm:space-y-4">

        {{-- RESUMEN DEVOLUCIÓN --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    Datos de devolución
                </div>

                <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                    Devuelto:
                    <span class="font-semibold tabular-nums">
                        {{ number_format((float) ($this->totalDevuelto ?? 0), 2, ',', '.') }}
                    </span>
                    |
                    Restante:
                    <span class="font-semibold tabular-nums">
                        {{ number_format((float) $this->restante, 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    {{-- BANCO DESTINO --}}
                    <div>
                        <label class="block text-sm mb-1">Banco destino <span class="text-red-500">*</span></label>
                        <select wire:model.live="banco_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->nombre }} — {{ $b->numero_cuenta }} ({{ $b->moneda }})
                                </option>
                            @endforeach
                        </select>
                        @error('banco_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHA DEVOLUCIÓN --}}
                    <div>
                        <label class="block text-sm mb-1">Fecha devolución <span class="text-red-500">*</span></label>
                        <input type="datetime-local" wire:model.live="fecha_devolucion"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('fecha_devolucion')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NRO TRANSACCIÓN --}}
                    <div>
                        <label class="block text-sm mb-1">Nro. transacción</label>
                        <input type="text" wire:model.live="nro_transaccion" placeholder="Opcional"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('nro_transaccion')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MONTO --}}
                    <div>
                        <label class="block text-sm mb-1">Monto <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" wire:model.blur="devol_monto_formatted"
                            placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />

                        {{-- aviso excede --}}
                        @if ($devol_monto > $this->restante)
                            <div class="text-xs text-red-600 mt-1">El monto excede el restante.</div>
                        @endif

                        @error('devol_monto')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- OBSERVACIÓN --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm mb-1">Observación</label>
                        <textarea rows="3" wire:model.live="observacion" placeholder="Opcional"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40"></textarea>
                        @error('observacion')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500 dark:text-neutral-400">
            <span class="text-red-500">*</span> Campos obligatorios.
        </p>
    </div>

    @slot('footer')
        <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">

            <button type="button" wire:click="close"
                class="w-full px-4 py-2 rounded-lg border cursor-pointer
                   border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar </button>


            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                @disabled(!$this->puedeGuardar)
                class="w-full px-4 py-2 rounded-lg cursor-pointer bg-emerald-600 text-white hover:opacity-90
                   disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">Guardar devolución</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>

        </div>
    @endslot

</x-ui.modal>

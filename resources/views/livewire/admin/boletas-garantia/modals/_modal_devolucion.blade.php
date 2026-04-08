{{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
<x-ui.modal wire:key="boleta-garantia-devolucion-{{ $open ? 'open' : 'closed' }}" model="open"
    title="Registrar Devolución" maxWidth="sm:max-w-xl md:max-w-4xl" onClose="close">

    <div class="space-y-0 sm:space-y-3">

        {{-- FORM (SIN "CAJAS" EXTRA) --}}
        <div class="space-y-4">

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">


                {{-- FECHA DEVOLUCIÓN --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Fecha devolución <span class="text-red-500">*</span></label>
                    <input type="datetime-local" wire:model.live="fecha_devolucion"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('fecha_devolucion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- BANCO DESTINO --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Banco destino <span class="text-red-500">*</span></label>
                    <select wire:model.live="banco_id"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                        <option value="">Seleccione…</option>
                        @foreach ($bancos as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->nombre }} | {{ $b->titular }} | {{ $b->moneda }}
                            </option>
                        @endforeach
                    </select>
                    @error('banco_id')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- MONTO --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Monto <span class="text-red-500">*</span></label>
                    <input type="text" inputmode="decimal" wire:model.blur="devol_monto_formatted" placeholder="0,00"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />

                    {{-- aviso excede --}}
                    @if ($devol_monto > $this->restante)
                        <div class="text-xs text-red-600 mt-1">El monto excede el restante.</div>
                    @endif

                    @error('devol_monto')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Comprobante (Imagen o PDF) --}}
                <div class="col-span-1 lg:col-span-1">
                    <x-ui.scanner model="foto_comprobante" label="Comprobante (Imagen/PDF)" :file="$foto_comprobante" />
                </div>

                {{-- NRO TRANSACCIÓN --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Nro. transacción</label>
                    <input type="text" wire:model.live="nro_transaccion" placeholder="Opcional"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('nro_transaccion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- OBSERVACIÓN --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Observación</label>
                    <input type="text" wire:model.live="observacion" placeholder="Opcional"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('observacion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        {{-- IMPACTO FINANCIERO --}}
        <div
            class="rounded-lg mt-3 border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-2">
            <div class="px-3 sm:px-4 py-1 border-b dark:border-neutral-700 flex justify-between items-center">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto financiero</div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    BANCO DESTINO {{ $this->monedaBoleta ? "({$this->monedaBoleta})" : '' }}
                </div>
            </div>

            <div class="p-2 sm:p-3 pb-3">
                <div class="grid grid-cols-3 gap-3 text-sm divide-x divide-gray-200 dark:divide-neutral-700">
                    <div class="text-center">
                        <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Saldo actual</div>
                        <div class="font-medium text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $this->saldo_banco_actual_preview, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="text-center pl-3">
                        <div
                            class="text-gray-500 dark:text-neutral-400 mb-1 text-xs text-emerald-600 dark:text-emerald-400">
                            Ingreso Devolución</div>
                        <div class="font-medium text-emerald-600 dark:text-emerald-400">
                            + {{ $devol_monto_formatted ?: '0,00' }}
                        </div>
                    </div>

                    <div class="text-center pl-3">
                        <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Nuevo saldo</div>
                        <div class="font-bold text-red-600 dark:text-red-400">
                            {{ number_format((float) $this->saldo_banco_despues_preview, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFORMATIVO PROYECTOS TIPO PROPUESTA --}}
        <div class="px-1 py-1 mt-2 flex justify-start">
            <div
                class="w-fit flex items-center gap-2 text-blue-700 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-800/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div class="text-[11px] leading-tight whitespace-nowrap">
                    <span class="font-semibold text-blue-800 dark:text-blue-300">Nota:</span> Se Puede Registrar Varias
                    <span class="font-bold underline decoration-blue-300 dark:decoration-blue-700">Devoluciones</span>.
                </div>
            </div>
        </div>
    </div>

    @slot('footer')
        <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3" x-data="{ uploading: false }"
            x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-error="uploading = false">

            <button type="button" wire:click="close"
                class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer
                   border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save, foto_comprobante"
                @disabled(!$this->puedeGuardar || $this->devol_monto > $this->restante || !$this->foto_comprobante)
                class="w-full sm:w-auto px-5 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                   disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!uploading" wire:loading.remove wire:target="save">Guardar</span>
                <span x-show="uploading" x-cloak>Procesando…</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>

        </div>
    @endslot

</x-ui.modal>

{{-- CREAR PRESUPUESTO --}}
<x-ui.modal wire:key="presupuesto-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
    title="Registrar Presupuesto" maxWidth="sm:max-w-xl md:max-w-2xl" onClose="closeModal">

    <div class="space-y-2">

        {{-- FORM (SIN “CAJAS” EXTRA) --}}
        <div class="space-y-2">

            {{-- FILA 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- BANCO --}}
                <div>
                    <label class="block text-sm mb-1">
                        Banco: <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="banco_id"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Seleccione…</option>
                        @foreach ($bancos as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->nombre }} — {{ $b->numero_cuenta }} ({{ $b->moneda }})
                            </option>
                        @endforeach
                    </select>

                    @error('banco_id')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- AGENTE --}}
                <div>
                    <label class="block text-sm mb-1">
                        Agente: <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="agente_servicio_id"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Seleccione…</option>
                        @foreach ($agentes as $a)
                            <option value="{{ $a->id }}">
                                {{ $a->nombre }} — CI: {{ $a->ci ?? '—' }}
                            </option>
                        @endforeach
                    </select>

                    @error('agente_servicio_id')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- FILA 2 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- MONTO --}}
                <div>
                    <label class="block text-sm mb-1">
                        Monto: <span class="text-red-500">*</span>
                    </label>
                    <input type="text" inputmode="decimal" wire:model.blur="monto_formatted" placeholder="0,00"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('monto')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- FECHA --}}
                <div>
                    <label class="block text-sm mb-1">
                        Fecha Pago: <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" wire:model="fecha_presupuesto"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('fecha_presupuesto')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NRO TRANSACCIÓN --}}
                <div>
                    <label class="block text-sm mb-1">
                        Nro. Transacción: <span class="text-red-500">*</span>
                    </label>
                    <input wire:model.live="nro_transaccion" placeholder="Ej: 156285"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('nro_transaccion')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- FOTO COMPROBANTE --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-sm mb-1 text-gray-700 dark:text-neutral-300">
                        Respaldo (Opcional):
                    </label>

                    <label
                        class="group flex items-center justify-between w-full rounded-lg border border-dashed
                               border-gray-300/70 dark:border-neutral-700/70
                               bg-white dark:bg-neutral-900 px-4 py-2 cursor-pointer
                               hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-8 h-8 rounded-lg border border-gray-200/70 dark:border-neutral-700/70
                                       bg-gray-50 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">
                                    Adjuntar archivo
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                    @if ($foto_comprobante && !is_string($foto_comprobante))
                                        {{ $foto_comprobante->getClientOriginalName() }}
                                    @else
                                        JPG, PNG o PDF (máx. 5MB)
                                    @endif
                                </div>
                            </div>
                        </div>

                        <input type="file" wire:model="foto_comprobante" class="hidden"
                            accept=".jpg,.jpeg,.png,.pdf" />
                    </label>

                    <div wire:loading wire:target="foto_comprobante" class="text-xs text-blue-600 mt-1">
                        Subiendo archivo...
                    </div>
                    @error('foto_comprobante')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    @if ($foto_comprobante && !is_string($foto_comprobante))
                        <div class="mt-2 text-right">
                            <button type="button" wire:click="$set('foto_comprobante', null)"
                                class="text-xs text-red-500 hover:text-red-700 underline">
                                Quitar archivo
                            </button>
                        </div>
                    @endif
                </div>

            </div>

            @if ($monto_excede_saldo)
                <div class="text-xs text-red-600 dark:text-red-400">
                    El monto no puede ser mayor al saldo actual del banco.
                </div>
            @endif

            <p class="text-xs text-gray-500 dark:text-neutral-400">
                <span class="text-red-500">*</span> Campos obligatorios.
            </p>

        </div>

        {{-- IMPACTO FINANCIERO (NO TOCAR) --}}
        <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    Impacto financiero
                </div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Vista previa del movimiento.
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">

                    <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                        <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">Banco</div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</span>
                            <span class="font-medium tabular-nums">
                                {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</span>
                            <span class="font-medium tabular-nums">
                                {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                        <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">Agente</div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</span>
                            <span class="font-medium tabular-nums">
                                {{ number_format((float) $saldo_agente_actual_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</span>
                            <span class="font-medium tabular-nums">
                                {{ number_format((float) $saldo_agente_despues_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    @slot('footer')
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">

            <button type="button" wire:click="closeModal"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="savePresupuesto" wire:loading.attr="disabled"
                wire:target="savePresupuesto" @disabled(!$this->puedeGuardar)
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="savePresupuesto">Guardar</span>
                <span wire:loading wire:target="savePresupuesto">Guardando…</span>
            </button>

        </div>
    @endslot
</x-ui.modal>

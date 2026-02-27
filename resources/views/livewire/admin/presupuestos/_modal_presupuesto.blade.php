{{-- CREAR PRESUPUESTO --}}
<x-ui.modal wire:key="presupuesto-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
    title="Registrar Presupuesto" maxWidth="sm:max-w-xl md:max-w-2xl" onClose="closeModal">

    <div class="space-y-3 sm:space-y-4">

        {{-- ORIGEN Y DESTINO --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    Origen y destino de fondos
                </div>
            </div>

            <div class="p-3 sm:p-4 space-y-3 sm:space-y-4">

                {{-- BANCO --}}
                <div>
                    <label class="block text-sm mb-1">
                        Banco <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="banco_id"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
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
                        Agente <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="agente_servicio_id"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
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
        </div>

        {{-- DATOS DE LA OPERACIÓN --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    Datos de la operación
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                    <div>
                        <label class="block text-sm mb-1">
                            Monto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" inputmode="decimal" wire:model.blur="monto_formatted" placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('monto')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" wire:model="fecha_presupuesto"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('fecha_presupuesto')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Nro. Transacción <span class="text-red-500">*</span>
                        </label>
                        <input wire:model.live="nro_transaccion" placeholder="Ej: 156285"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('nro_transaccion')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                @if ($monto_excede_saldo)
                    <div class="mt-3 text-xs text-red-600 dark:text-red-400">
                        El monto no puede ser mayor al saldo actual del banco.
                    </div>
                @endif

                <p class="mt-3 text-xs text-gray-500 dark:text-neutral-400">
                    <span class="text-red-500">*</span> Campos obligatorios.
                </p>
            </div>
        </div>

        {{-- IMPACTO FINANCIERO --}}
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
        {{-- Footer mobile-first: Agregar arriba, Cancelar abajo --}}
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">

             <button type="button" wire:click="closeModal"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>
            
            <button type="button" wire:click="savePresupuesto" wire:loading.attr="disabled" wire:target="savePresupuesto"
                @disabled(!$this->puedeGuardar)
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="savePresupuesto">Agregar</span>
                <span wire:loading wire:target="savePresupuesto">Guardando…</span>
            </button>    
            
        </div>
    @endslot
</x-ui.modal>

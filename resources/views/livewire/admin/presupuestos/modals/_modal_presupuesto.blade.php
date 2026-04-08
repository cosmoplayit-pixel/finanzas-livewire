{{-- CREAR PRESUPUESTO --}}
<x-ui.modal wire:key="presupuesto-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
    title="Registrar Presupuesto" maxWidth="sm:max-w-xl md:max-w-2xl" onClose="closeModal">

    <div class="space-y-2">

        {{-- FORM (SIN “CAJAS” EXTRA) --}}
        <div class="space-y-2">

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- BANCO --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">
                        Banco: <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="banco_id"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Seleccione…</option>
                        @foreach ($bancos as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->nombre }} — {{ $b->titular }} ({{ $b->moneda }})
                            </option>
                        @endforeach
                    </select>

                    @error('banco_id')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- AGENTE --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">
                        Agente: <span class="text-red-500">*</span>
                    </label>

                    <select wire:model.live="agente_servicio_id"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2
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

                {{-- MONTO --}}
                <div class="col-span-1 lg:col-span-1">
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

                {{-- NRO TRANSACCIÓN --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">
                        Nro. Transacción (Opcional):
                    </label>
                    <input wire:model.live="nro_transaccion"
                        class="w-full rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('nro_transaccion')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- FECHA --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">
                        Fecha Pago: <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" wire:model="fecha_presupuesto"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300/60 dark:border-neutral-700/60
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('fecha_presupuesto')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- FOTO COMPROBANTE --}}
                <div class="col-span-1 lg:col-span-1">
                    <x-ui.scanner model="foto_comprobante" label="Respaldo (Opcional)" :file="$foto_comprobante" />
                </div>


                @if ($monto_excede_saldo)
                    <div class="text-xs text-red-600 dark:text-red-400">
                        El monto no puede ser mayor al saldo actual del banco.
                    </div>
                @endif
            </div>

            {{-- IMPACTO FINANCIERO --}}
            <div
                class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-4">
                <div class="px-3 sm:px-4 py-1 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto financiero</div>
                </div>

                {{-- MOBILE: tiras compactas --}}
                <div class="md:hidden p-2 space-y-2">
                    {{-- Banco strip --}}
                    <div>
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-1 mb-1">
                            Banco {{ $monedaBanco ? "({$monedaBanco})" : '' }}
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo actual</div>
                                <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                    {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Egreso</div>
                                <div class="text-xs font-bold tabular-nums text-red-600 dark:text-red-400">
                                    - {{ $monto_formatted ?: '0,00' }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Nuevo saldo</div>
                                <div class="text-xs font-bold tabular-nums {{ $monto_excede_saldo ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-neutral-200' }}">
                                    {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Agente strip --}}
                    <div>
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-1 mb-1">
                            Agente {{ $monedaBanco ? "({$monedaBanco})" : '' }}
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo actual</div>
                                <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                    {{ number_format((float) $saldo_agente_actual_preview, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Asignación</div>
                                <div class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                    + {{ $monto_formatted ?: '0,00' }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Nuevo saldo</div>
                                <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                    {{ number_format((float) $saldo_agente_despues_preview, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DESKTOP: 2 columnas --}}
                <div class="hidden md:grid px-2 py-1 sm:p-4 grid-cols-2 gap-6">

                    {{-- BANCO --}}
                    <div class="space-y-3 md:border-r md:border-gray-200 md:dark:border-neutral-700 md:pr-6">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            BANCO {{ $monedaBanco ? "({$monedaBanco})" : '' }}
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                            <span class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-red-600 dark:text-red-400">
                            <span>Egreso</span>
                            <span class="font-medium">- {{ $monto_formatted ?: '0,00' }}</span>
                        </div>
                        <div
                            class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                            <span class="font-bold {{ $monto_excede_saldo ? 'text-red-600' : 'text-gray-900 dark:text-neutral-100' }}">
                                {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- AGENTE --}}
                    <div class="space-y-3">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            AGENTE {{ $monedaBanco ? "({$monedaBanco})" : '' }}
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                            <span class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ number_format((float) $saldo_agente_actual_preview, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-emerald-600 dark:text-emerald-400">
                            <span>Asignación</span>
                            <span class="font-medium">+ {{ $monto_formatted ?: '0,00' }}</span>
                        </div>
                        <div
                            class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                            <span class="font-bold text-gray-900 dark:text-neutral-100">
                                {{ number_format((float) $saldo_agente_despues_preview, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    @slot('footer')
            <div class="grid grid-cols-2 gap-2 w-full sm:flex sm:justify-end sm:gap-3">

                <button type="button" wire:click="closeModal"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="savePresupuesto" wire:loading.attr="disabled"
                    wire:target="savePresupuesto,foto_comprobante" @disabled(!$this->puedeGuardar)
                    class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="savePresupuesto,foto_comprobante">Guardar</span>
                    <span wire:loading wire:target="foto_comprobante">Procesando…</span>
                    <span wire:loading wire:target="savePresupuesto">Guardando…</span>
                </button>

            </div>
        @endslot
</x-ui.modal>

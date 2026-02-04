{{-- ===================== MODAL BOLETA (SOLO CREAR) ===================== --}}
<x-ui.modal wire:key="boleta-garantia-create-{{ $open ? 'open' : 'closed' }}" model="open"
    title="Nueva Boleta de Garantía" maxWidth="sm:max-w-xl md:max-w-4xl" onClose="close">


    <div class="space-y-3 sm:space-y-4">

        {{-- DATOS PRINCIPALES --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos principales</div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                    <div>
                        <label class="block text-sm mb-1">Agente de servicio <span class="text-red-500">*</span></label>
                        <select wire:model.live="agente_servicio_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($agentes as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }} — CI: {{ $a->ci ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                        @error('agente_servicio_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model.live="tipo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="SERIEDAD">Garantía de Seriedad de Propuesta</option>
                            <option value="CUMPLIMIENTO">Garantía de Cumplimiento de Contrato</option>
                        </select>
                        @error('tipo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Nro. Boleta <span class="text-red-500">*</span></label>
                        <input wire:model.live="nro_boleta" placeholder="Ej: BG-001"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('nro_boleta')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Entidad <span class="text-red-500">*</span></label>
                        <select wire:model.live="entidad_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($entidades as $en)
                                <option value="{{ $en->id }}">{{ $en->nombre }}</option>
                            @endforeach
                        </select>
                        @error('entidad_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Proyecto <span class="text-red-500">*</span></label>
                        <select wire:model.live="proyecto_id" @disabled($this->proyectoBloqueado)
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="">
                                {{ $this->proyectoBloqueado ? 'Seleccione entidad primero…' : 'Seleccione…' }}
                            </option>
                            @foreach ($this->proyectosEntidad as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('proyecto_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Banco egreso <span class="text-red-500">*</span></label>
                        <select wire:model.live="banco_egreso_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">{{ $b->nombre }} — {{ $b->numero_cuenta }}
                                    ({{ $b->moneda }})
                                </option>
                            @endforeach
                        </select>
                        @error('banco_egreso_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Fecha emisión <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="fecha_emision"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('fecha_emision')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Fecha vencimiento</label>
                        <input type="date" wire:model="fecha_vencimiento"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                        @error('fecha_vencimiento')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    <div>
                        <div>
                            <label class="block text-sm mb-1">Retención <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.blur="retencion_formatted"
                                placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('retencion')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror

                            @if ($total_excede_saldo)
                                <div class="text-xs text-red-600 mt-1">La retención excede el saldo del banco.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- IMPACTO BANCO --}}
        <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto banco egreso</div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</div>
                        <div class="font-semibold tabular-nums">
                            {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                            <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</div>
                        <div class="font-semibold tabular-nums">
                            {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                            <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                        </div>
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
                Cancelar
            </button>

            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                @disabled(!$this->puedeGuardar)
                class="w-full px-4 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                   disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    @endslot


</x-ui.modal>

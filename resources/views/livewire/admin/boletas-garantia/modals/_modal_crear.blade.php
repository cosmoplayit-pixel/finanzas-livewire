{{-- ===================== MODAL BOLETA (SOLO CREAR) ===================== --}}
<x-ui.modal wire:key="boleta-garantia-create-{{ $open ? 'open' : 'closed' }}" model="open"
    title="Nueva Boleta de Garantía" maxWidth="sm:max-w-xl md:max-w-4xl" onClose="close">


    <div class="space-y-0 sm:space-y-3">

        {{-- FORM (SIN "CAJAS" EXTRA) --}}
        <div class="space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Agente de servicio <span class="text-red-500">*</span></label>
                    <select wire:model.live="agente_servicio_id"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
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

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
                    <select wire:model.live="tipo"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                        <option value="">Seleccione…</option>
                        <option value="SERIEDAD">Garantía de Seriedad de Propuesta</option>
                        <option value="CUMPLIMIENTO">Garantía de Cumplimiento de Contrato</option>
                    </select>
                    @error('tipo')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Nro. Boleta <span class="text-red-500">*</span></label>
                    <input wire:model.live="nro_boleta" placeholder="Ej: BG-001"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('nro_boleta')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Cliente <span class="text-red-500">*</span></label>
                    <select wire:model.live="entidad_id"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                        <option value="">Seleccione…</option>
                        @foreach ($entidades as $en)
                            <option value="{{ $en->id }}">{{ $en->nombre }}</option>
                        @endforeach
                    </select>
                    @error('entidad_id')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Proyecto <span class="text-red-500">*</span></label>
                    <select wire:model.live="proyecto_id" @disabled($this->proyectoBloqueado)
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40
                                   disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
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

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Banco egreso <span class="text-red-500">*</span></label>
                    <select wire:model.live="banco_egreso_id"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                        <option value="">Seleccione…</option>
                        @foreach ($bancos as $b)
                            <option value="{{ $b->id }}">{{ $b->nombre }} | {{ $b->titular }} |
                                ({{ $b->moneda }})
                            </option>
                        @endforeach
                    </select>
                    @error('banco_egreso_id')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Fecha emisión <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.live="fecha_emision"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('fecha_emision')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1">Fecha vencimiento</label>
                    <input type="date" wire:model="fecha_vencimiento"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('fecha_vencimiento')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-span-1">
                    <label class="block text-sm mb-1">Monto <span class="text-red-500">*</span></label>
                    <input type="text" inputmode="decimal" wire:model.blur="retencion_formatted" placeholder="0,00"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    @error('retencion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    @if ($total_excede_saldo)
                        <div class="text-xs text-red-600 mt-1">La retención excede el saldo del banco.</div>
                    @endif
                </div>

                {{-- Comprobante (Imagen o PDF) --}}
                <div class="col-span-1">
                    <x-ui.scanner model="foto_comprobante" label="Comprobante (Imagen/PDF)" :file="$foto_comprobante" />
                </div>

                {{-- Observación --}}
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Observación</label>
                    <input type="text" wire:model.live="observacion" placeholder="Opcional"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                    @error('observacion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>


        {{-- IMPACTO FINANCIERO --}}
        <div class="rounded-lg mt-3 border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden ">
            <div class="px-3 sm:px-4  py-1 border-b dark:border-neutral-700 flex justify-between items-center">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto financiero</div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    BANCO EGRESO {{ $monedaBanco ? "({$monedaBanco})" : '' }}
                </div>
            </div>

            <div class="p-2 sm:p-3 pb-3">
                <div class="grid grid-cols-3 gap-3 text-sm divide-x divide-gray-200 dark:divide-neutral-700">
                    <div class="text-center">
                        <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Saldo actual</div>
                        <div class="font-medium text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="text-center pl-3">
                        <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Retención</div>
                        <div class="font-medium text-red-600 dark:text-red-400">
                            - {{ $retencion_formatted ?: '0,00' }}
                        </div>
                    </div>

                    <div class="text-center pl-3">
                        <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Nuevo saldo</div>
                        <div
                            class="font-bold {{ $total_excede_saldo ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-neutral-100' }}">
                            {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFORMATIVO PROYECTOS TIPO PROPUESTA --}}
        <div class="px-1 py-1 mt-2 flex justify-start">
            <div
                class="w-fit flex items-center gap-2 text-blue-700 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-800/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div class="text-[11px] leading-tight whitespace-nowrap">
                    <span class="font-semibold text-blue-800 dark:text-blue-300">Nota:</span> Solo se pueden emitir
                    boletas para proyectos categorizados como <span
                        class="font-bold underline decoration-blue-300 dark:decoration-blue-700">Propuesta</span>.
                </div>
            </div>
        </div>

    </div>
    @slot('footer')
        <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">

            <button type="button" wire:click="close"
                class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer
                   border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                   hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-medium">
                Cancelar
            </button>

            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save, foto_comprobante"
                @disabled(!$this->puedeGuardar)
                class="w-full sm:w-auto px-5 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                   disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
                <span wire:loading.remove wire:target="save, foto_comprobante">Guardar</span>
                <span wire:loading wire:target="foto_comprobante">Procesando…</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    @endslot


</x-ui.modal>

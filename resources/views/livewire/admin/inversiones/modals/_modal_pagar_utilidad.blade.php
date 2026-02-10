{{-- resources/views/livewire/admin/inversiones/modals/_modal_pagar_utilidad.blade.php --}}

<div>
    <x-ui.modal wire:key="pago-inversion-{{ $open ? 'open' : 'closed' }}" model="open" title="Registrar pago"
        maxWidth="sm:max-w-2xl" onClose="close">

        <div class="space-y-3">

            {{-- RESUMEN --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        {{ $inversion?->nombre_completo ?? '—' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
                        <span class="font-mono">{{ $inversion?->codigo ?? '—' }}</span>
                        <span class="text-gray-300 dark:text-neutral-600">•</span>
                        <span>Base: {{ $inversion?->moneda ?? '—' }}</span>
                        <span class="text-gray-300 dark:text-neutral-600">•</span>
                        <span class="truncate max-w-[280px]">{{ $inversion?->banco?->nombre ?? 'Sin banco' }}</span>
                    </div>
                </div>

            </div>

            {{-- FORM --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- TIPO PAGO --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Tipo de pago <span class="text-red-500">*</span></label>
                        <select wire:model.live="tipo_pago"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="INGRESO_CAPITAL">Ingreso a capital</option>
                            <option value="DEVOLUCION_CAPITAL">Devolución a capital</option>
                            <option value="PAGO_UTILIDAD">Pago utilidad</option>
                        </select>
                        @error('tipo_pago')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHAS --}}
                    @if ($tipo_pago === 'PAGO_UTILIDAD')
                        {{-- Fecha inicio auto (bloqueada) --}}
                        <div>
                            <label class="block text-sm mb-1">Fecha inicio (auto) <span
                                    class="text-red-500">*</span></label>
                            <input type="date" value="{{ $utilidad_fecha_inicio }}" disabled
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       disabled:opacity-80 disabled:cursor-not-allowed">
                        </div>

                        {{-- Fecha final (manual) --}}
                        <div>
                            <label class="block text-sm mb-1">Fecha final <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="fecha"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            @error('fecha')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cantidad días (bloqueada) --}}
                        <div>
                            <label class="block text-sm mb-1">Cantidad días</label>
                            <input type="text" disabled value="{{ (int) ($utilidad_dias ?? 0) }}"
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                *Máximo 30 (si sale 31, se toma 30)
                            </div>
                        </div>

                        {{-- Fecha pago (opcional) --}}
                        <div>
                            <label class="block text-sm mb-1">Fecha pago</label>
                            <input type="date" wire:model.live="fecha_pago"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            @error('fecha_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- Capital: fecha bloqueada en ingreso/devolución --}}
                        <div>
                            <label class="block text-sm mb-1">
                                @if (in_array($tipo_pago, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL']))
                                    Fecha de inicio (último movimiento) <span class="text-red-500">*</span>
                                @else
                                    Fecha <span class="text-red-500">*</span>
                                @endif
                            </label>

                            <input type="date" wire:model.live="fecha" @disabled(in_array($tipo_pago, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL']))
                                class="w-full rounded-lg border px-3 py-2
                                       {{ in_array($tipo_pago, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL']) ? 'bg-gray-50 dark:bg-neutral-800' : 'bg-white dark:bg-neutral-900' }}
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40
                                       disabled:opacity-80 disabled:cursor-not-allowed">
                            @error('fecha')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">
                                Fecha pago <span class="text-red-500">*</span>
                            </label>

                            <input type="date" wire:model.live="fecha_pago"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            @error('fecha_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- BANCO --}}
                    <div>
                        <label class="block text-sm mb-1">
                            Debitar del banco <span class="text-red-500">*</span>
                        </label>

                        <select wire:model.live="banco_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b['id'] }}">
                                    {{ $b['nombre'] }} — {{ $b['numero_cuenta'] }} ({{ $b['moneda'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('banco_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror

                        @if (!empty($mov_moneda))
                            <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                Moneda banco: <span class="font-semibold">{{ $mov_moneda }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- COMPROBANTE --}}
                    <div>
                        <label class="block text-sm mb-1">Nro comprobante</label>
                        <input type="text" wire:model.live="nro_comprobante" placeholder="Ej: 100"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        @error('nro_comprobante')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- TC CONDICIONAL + PREVIEW --}}
                    @if ($needs_tc)
                        <div>
                            <label class="block text-sm mb-1">Tipo de cambio <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="tipo_cambio_formatted" placeholder="Ej: 6,96"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            @error('tipo_cambio')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Monto en moneda base (preview)</label>
                            <input type="text" disabled value="{{ $monto_base_preview ?: '—' }}"
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                Base: <span class="font-semibold">{{ $inversion?->moneda ?? '—' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- CAMPOS POR TIPO --}}
                    @if ($tipo_pago === 'PAGO_UTILIDAD')
                        {{-- % calculado --}}
                        <div>
                            <label class="block text-sm mb-1">% utilidad (calculado)</label>
                            <input type="text" disabled
                                value="{{ number_format((float) ($utilidad_pct_calc ?? 0), 2, ',', '.') }}%"
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                        </div>

                        {{-- monto utilidad mes --}}
                        <div>
                            <label class="block text-sm mb-1">
                                Monto utilidad mes <span class="text-red-500">*</span>
                            </label>

                            <input type="text" wire:model.blur="utilidad_monto_mes_formatted"
                                placeholder="Ej: 5.000,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-emerald-500/40">

                            @error('utilidad_monto_mes')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- A pagar (calculado) --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">A pagar (calculado)</label>
                            <input type="text" disabled value="{{ $utilidad_a_pagar_formatted ?: '0,00' }}"
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            @error('utilidad_a_pagar')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- capital --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Monto (capital) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="monto_capital_formatted"
                                placeholder="Ej: 10.000,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            @error('monto_capital')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Foto (TU FORMATO) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Foto del comprobante (opcional)</label>

                        <label
                            class="group flex items-center justify-between w-full rounded-lg border border-dashed
                                   border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                   px-4 py-0.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-7 h-7 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800
                                           flex items-center justify-center shrink-0">
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
                                        @if ($comprobante_imagen)
                                            {{ $comprobante_imagen->getClientOriginalName() }}
                                        @else
                                            JPG, JPEG o PNG (máx. 5MB)
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <input type="file" wire:model.live="comprobante_imagen" accept=".jpg,.jpeg,.png"
                                class="hidden" />
                        </label>

                        @error('comprobante_imagen')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="close"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                           border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                           hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    wire:target="save,comprobante_imagen"
                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:opacity-90 cursor-pointer
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save,comprobante_imagen">Guardar</span>
                    <span wire:loading wire:target="save,comprobante_imagen">Guardando…</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>
</div>

{{-- ===================== MODAL CREAR INVERSION ===================== --}}
<x-ui.modal wire:key="inversion-create-{{ $open ? 'open' : 'closed' }}" model="open" title="Nueva inversión"
    maxWidth="sm:max-w-xl md:max-w-4xl" onClose="close">

    <div class="space-y-3 sm:space-y-4">

        {{-- DATOS PRINCIPALES --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos principales</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                    Completa la información base de la inversión.
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                    {{-- Código --}}
                    <div>
                        <label class="block text-sm mb-1">Código <span class="text-red-500">*</span></label>
                        <input wire:model.live="codigo" placeholder="Ej: 260101"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('codigo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nombre --}}
                    <div class="sm:col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input wire:model.live="nombre_completo" placeholder="Ej: Willam Rojas Vidal"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('nombre_completo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha inicio --}}
                    <div>
                        <label class="block text-sm mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="fecha_inicio"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('fecha_inicio')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha vencimiento --}}
                    <div>
                        <label class="block text-sm mb-1">Fecha vencimiento</label>
                        <input type="date" wire:model="fecha_vencimiento"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('fecha_vencimiento')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo --}}
                    <div>
                        <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model.live="tipo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Ninguno</option>
                            <option value="PRIVADO">Privado</option>
                            <option value="BANCO">Banco</option>
                        </select>
                        @error('tipo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Banco --}}
                    <div class="lg:col-span-1">
                        <label class="block text-sm mb-1">Banco:<span class="text-red-500">*</span></label>
                        <select wire:model.live="banco_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Ninguno</option>
                            @foreach ($this->bancos as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->nombre }} — {{ $b->numero_cuenta ?? '—' }} ({{ $b->moneda ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('banco_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Capital --}}
                    <div>
                        <label class="block text-sm mb-1">Capital <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" wire:model.defer="capital_formatted"
                            wire:blur="formatCapital" placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('capital')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- % Utilidad --}}
                    <div>
                        <label class="block text-sm mb-1">% Utilidad <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" wire:model.defer="porcentaje_utilidad_formatted"
                            wire:blur="formatPorcentaje" placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('porcentaje_utilidad')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div class="lg:col-span-1">
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
                                        @if ($comprobante)
                                            {{ $comprobante->getClientOriginalName() }}
                                        @else
                                            JPG, JPEG o PNG (máx. 5MB)
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <input type="file" wire:model="comprobante" accept=".jpg,.jpeg,.png" class="hidden" />
                        </label>

                        @error('comprobante')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <p class="mt-3 text-xs text-gray-500 dark:text-neutral-400">
                    <span class="text-red-500">*</span> Campos obligatorios.
                </p>
            </div>
        </div>

        {{-- IMPACTO EN BANCO (PREVISUALIZACIÓN) --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto banco</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                    Previsualización del saldo del banco con el capital ingresado.
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2
                                    border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</div>
                        <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2
                                    border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Aumento</div>
                        <div class="text-sm font-semibold tabular-nums text-gray-700 dark:text-gray-300">
                            +{{ number_format((float) $saldo_banco_aumento_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 px-3 py-2
                                    border-gray-200 dark:border-neutral-700">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</div>
                        <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                            {{ $moneda === 'USD' ? '$' : 'Bs' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>

    @slot('footer')
        <div class="grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
            <button type="button" wire:click="close"
                class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="create" wire:loading.attr="disabled" wire:target="create,comprobante"
                class="px-4 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="create,comprobante"> Guardar </span>
                <span wire:loading wire:target="create,comprobante">Procesando…</span>
            </button>
        </div>
    @endslot

</x-ui.modal>

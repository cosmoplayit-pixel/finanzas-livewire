<div>
    <x-ui.modal wire:key="pago-banco-{{ $open ? 'open' : 'closed' }}" model="open"
        title="{{ $modoConfirmar ? 'Confirmar pago banco' : 'Registrar pago banco' }}"
        maxWidth="sm:max-w-2xl md:max-w-4xl" onClose="close">

        @php
            $inv = $inversion;
            $invMon = strtoupper($inv?->moneda ?? 'BOB');
            $hasTC = (bool) $needs_tc;
        @endphp

        <div class="space-y-4">

            {{-- RESUMEN --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $inv?->nombre_completo ?? '—' }}
                            </div>

                            <div
                                class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
                                {{-- Código --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 7H4" />
                                        <path d="M20 11H4" />
                                        <path d="M20 15H4" />
                                        <path d="M20 19H4" />
                                        <path d="M8 3v4" />
                                        <path d="M16 3v4" />
                                    </svg>
                                    <span class="font-mono">{{ $inv?->codigo ?? '—' }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Tipo --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 3v18" />
                                        <path d="M3 12h18" />
                                    </svg>
                                    <span>{{ $inv?->tipo ?? '—' }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Saldo --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 10h18" />
                                        <path d="M5 10V20" />
                                        <path d="M19 10V20" />
                                        <path d="M2 20h20" />
                                        <path d="M12 2 2 7h20L12 2z" />
                                    </svg>
                                    <span class="tabular-nums">
                                        {{ $invMon === 'USD'
                                            ? '$ ' . number_format((float) ($inv?->capital_actual ?? 0), 2, ',', '.')
                                            : number_format((float) ($inv?->capital_actual ?? 0), 2, ',', '.') . ' Bs' }}
                                    </span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Plazo --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 3" />
                                    </svg>
                                    <span>{{ (int) ($inv?->plazo_meses ?? 0) }} meses</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Día pago --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <path d="M16 2v4" />
                                        <path d="M8 2v4" />
                                        <path d="M3 10h18" />
                                        <path d="M8 14h.01" />
                                        <path d="M12 14h.01" />
                                        <path d="M16 14h.01" />
                                    </svg>
                                    <span>Día pago: {{ (int) ($inv?->dia_pago ?? 0) }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Fechas --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <path d="M16 2v4" />
                                        <path d="M8 2v4" />
                                        <path d="M3 10h18" />
                                    </svg>
                                    <span>
                                        {{ $inv?->fecha_inicio ? $inv->fecha_inicio->format('d/m/Y') : '—' }}
                                        -
                                        {{ $inv?->fecha_vencimiento ? $inv->fecha_vencimiento->format('d/m/Y') : '—' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- CONCEPTO --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Concepto</label>
                            <input type="text" value="Pago cuota" readonly
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       cursor-not-allowed opacity-80">
                        </div>

                        {{-- FECHA CONTABLE --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Fecha (contable) <span
                                    class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="fecha" @disabled($modoConfirmar)
                                class="w-full rounded-lg border px-3 py-2
                                {{ $modoConfirmar ? 'bg-gray-50 dark:bg-neutral-800 cursor-not-allowed opacity-80' : 'bg-white dark:bg-neutral-900' }}
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('fecha')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- FECHA PAGO --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Fecha pago <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="fecha_pago" @disabled($modoConfirmar)
                                class="w-full rounded-lg border px-3 py-2
                                {{ $modoConfirmar ? 'bg-gray-50 dark:bg-neutral-800 cursor-not-allowed opacity-80' : 'bg-white dark:bg-neutral-900' }}
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('fecha_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- BANCO --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Debitar del banco <span
                                    class="text-red-500">*</span></label>
                            <select
                                wire:key="banco-select-{{ $open ? 1 : 0 }}-{{ $movimientoId ?? 'new' }}-{{ (string) $banco_id }}"
                                wire:model.live="banco_id" class="w-full rounded-lg border px-3 py-2">
                                <option value="">Seleccione…</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ (string) $b['id'] }}">
                                        {{ $b['nombre'] }} — {{ $b['numero_cuenta'] }} ({{ $b['moneda'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('banco_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- COMPROBANTE --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Nro comprobante</label>
                            <input type="text" wire:model.live="nro_comprobante" placeholder="Ej: 12345"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('nro_comprobante')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TIPO CAMBIO --}}
                        @if ($hasTC)
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">Tipo de cambio <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.live="tipo_cambio_formatted" placeholder="Ej: 6,96"
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                                @error('tipo_cambio')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- TOTAL --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Monto total (base {{ $invMon }}) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="monto_total_formatted" placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('monto_total')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- CAPITAL --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Capital (base) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="monto_capital_formatted" placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('monto_capital')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- INTERÉS --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Interés (auto) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model="monto_interes_formatted" readonly
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800 text-right tabular-nums
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       cursor-not-allowed opacity-80">
                            @error('monto_interes')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- FOTO --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Foto comprobante (opcional)</label>

                            <label
                                class="group h-11 flex items-center justify-between w-full rounded-lg border border-dashed
                                border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-4 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-7 h-7 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800
                                        flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">Adjuntar
                                            archivo</div>
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

                        {{-- IMPACTO --}}
                        <div class="md:col-span-3">
                            <div class="rounded-xl border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-4">
                                <div class="font-semibold text-sm text-gray-900 dark:text-neutral-100">Impacto
                                    financiero</div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 mb-3">Vista previa del
                                    movimiento.</div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="rounded-lg border dark:border-neutral-700 p-3">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Banco</div>
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Saldo actual</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_banco_actual_fmt }}</span>
                                        </div>
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Saldo después</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_banco_despues_fmt }}</span>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border dark:border-neutral-700 p-3">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Deuda (saldo)</div>
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Actual</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_deuda_actual_fmt }}</span>
                                        </div>
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Después</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_deuda_despues_fmt }}</span>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border dark:border-neutral-700 p-3">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Estado</div>
                                        <div
                                            class="mt-2 text-sm font-semibold {{ $impacto_ok ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $impacto_texto }}
                                        </div>
                                        @if (!empty($impacto_detalle))
                                            <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $impacto_detalle }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div> {{-- grid --}}
                </div>
            </div>

        </div> {{-- space-y --}}

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="close"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                    border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                    hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                @if ($modoConfirmar && $movimientoId)
                    {{-- Confirmar (guarda cambios + confirma) --}}
                    <button type="button" wire:click="confirmar" wire:loading.attr="disabled"
                        wire:target="confirmar,save,comprobante_imagen"
                        class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 cursor-pointer
                        disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="confirmar,save,comprobante_imagen">Confirmar</span>
                        <span wire:loading wire:target="confirmar,save,comprobante_imagen">Confirmando…</span>
                    </button>
                @else
                    {{-- Guardar (crea pendiente) --}}
                    <button type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,comprobante_imagen"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:opacity-90 cursor-pointer
                        disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save,comprobante_imagen">Guardar</span>
                        <span wire:loading wire:target="save,comprobante_imagen">Procesando…</span>
                    </button>
                @endif
            </div>
        @endslot

    </x-ui.modal>
</div>

{{-- resources/views/livewire/admin/inversiones/modals/_modal_pagar_utilidad.blade.php --}}

<div>
    <x-ui.modal wire:key="pago-inversion-{{ $open ? 'open' : 'closed' }}" model="open"
        title="{{ $modoConfirmar ? 'Confirmar pago' : 'Registrar pago' }}" maxWidth="sm:max-w-2xl md:max-w-3xl"
        onClose="close">

        @php
            $isUtilidad = $tipo_pago === 'PAGO_UTILIDAD';
            $inv = $inversion;
            $invMon = strtoupper($inv?->moneda ?? 'BOB');
            $hasTC = (bool) $needs_tc;
            $inputCurrency = $invMon; // Capital y utilidad siempre en moneda base de la inversión
        @endphp

        <div class="space-y-4">

            {{-- RESUMEN  --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                        {{-- IZQUIERDA --}}
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $inversion?->nombre_completo ?? '—' }}
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
                                    <span class="font-mono">{{ $inversion?->codigo ?? '—' }}</span>
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
                                    <span>{{ $inversion?->tipo ?? '—' }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                {{-- Capital actual + Moneda --}}
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="2" y="6" width="20" height="12" rx="2" />
                                        <circle cx="12" cy="12" r="2" />
                                        <path d="M6 12h.01M18 12h.01" />
                                    </svg>
                                    <span class="tabular-nums">{{ number_format((float) ($inversion?->capital_actual ?? 0), 2, ',', '.') }}</span>
                                    <span class="font-semibold">{{ $invMon }}</span>
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
                                        {{ $inversion?->fecha_inicio ? $inversion->fecha_inicio->format('d/m/Y') : '—' }}
                                        -
                                        {{ $inversion?->fecha_vencimiento ? $inversion->fecha_vencimiento->format('d/m/Y') : '—' }}
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
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        {{-- TIPO PAGO --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm mb-1">Tipo de Pago <span class="text-red-500">*</span></label>
                            <select wire:model.live="tipo_pago" @disabled($modoConfirmar)
                                class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40
                                       disabled:bg-gray-50 dark:disabled:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-80">
                                <option value="PAGO_UTILIDAD">Pago de Interés</option>
                                <option value="INGRESO_CAPITAL">Ingreso a Capital</option>
                                <option value="DEVOLUCION_CAPITAL">Devolución a Capital</option>

                            </select>
                            @error('tipo_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- FECHAS --}}
                        @if ($isUtilidad)
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Fecha Inicio (auto) <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                    wire:key="utilidad-fecha-inicio-{{ $inversion?->id ?? 0 }}-{{ $tipo_pago }}"
                                    value="{{ $utilidad_fecha_inicio }}" disabled
                                    class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            </div>

                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Fecha Final <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model.live="fecha"
                                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                @error('fecha')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($modoConfirmar)
                                <div class="col-span-1 md:col-span-1">
                                    <label class="block text-sm mb-1">
                                        Fecha Pago <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model.live="fecha_pago"
                                        class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                    @error('fecha_pago')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">Cantidad Días</label>
                                <input type="text" disabled
                                    value="{{ (int) ($utilidad_dias ?? ' Regla: 28–31 ⇒ 30') }}"
                                    class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            </div>
                        @else
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Fecha de Inicio (últ. movimiento) <span class="text-red-500">*</span>
                                </label>

                                <input wire:ignore type="date" value="{{ $fecha_inicio_ref }}" readonly disabled
                                    class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">

                                @error('fecha')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Fecha Pago <span class="text-red-500">*</span>
                                </label>

                                <input type="date" wire:model.live="fecha_pago"
                                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                    focus:outline-none focus:ring-2 focus:ring-emerald-500/40">

                                @error('fecha_pago')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if ($modoConfirmar || !$isUtilidad)

                            {{-- BANCO --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Debitar del Banco <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="banco_id"
                                    class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                    <option value="">Seleccione…</option>
                                    @foreach ($bancos as $b)
                                        <option value="{{ $b['id'] }}">
                                            {{ $b['nombre'] }} — {{ $b['titular'] }} ({{ $b['moneda'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('banco_id')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                                @if (!empty($mov_moneda))
                                    <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                        Moneda Banco: <span class="font-semibold">{{ $mov_moneda }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- TIPO DE CAMBIO (si aplica) --}}
                            @if ($hasTC)
                                <div class="col-span-1 md:col-span-1">
                                    <label class="block text-sm mb-1">
                                        Tipo de cambio <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.blur="tipo_cambio_formatted"
                                        placeholder="Ej: 6,96"
                                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                    @error('tipo_cambio')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        @endif

                        {{-- CAMPOS POR TIPO --}}
                        @if ($isUtilidad)

                            {{-- MONTO INTERÉS --}}
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Monto Interés Mes ({{ $inputCurrency }}) <span class="text-red-500">*</span>
                                </label>
                                <input wire:key="utilidad-mes-{{ $tipo_pago }}" type="text"
                                    wire:model.blur="utilidad_monto_mes_formatted" placeholder="Ej: 0,00"
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                    focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                @error('utilidad_monto_mes')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- INTERÉS CALCULADO --}}
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">% Interés (calculado por
                                    {{ $utilidad_dias ?? 0 }} Días)</label>
                                <input type="text" disabled
                                    value="{{ number_format((float) ($utilidad_pct_calc ?? 0), 2, ',', '.') }}%"
                                    class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            </div>

                            {{-- MONTO A PAGAR --}}
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">A Pagar (calculado)
                                    ({{ $inputCurrency }})</label>
                                <input type="text" disabled value="{{ $utilidad_a_pagar_formatted ?: '0,00' }}"
                                    class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                                @if ($hasTC && $monto_base_preview)
                                    <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                        Banco: <span class="font-semibold">{{ $monto_base_preview }}
                                            {{ $mov_moneda }}</span>
                                    </div>
                                @endif
                                @error('utilidad_a_pagar')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Monto (capital) ({{ $inputCurrency }}) <span class="text-red-500">*</span>
                                </label>
                                <input wire:key="capital-monto-{{ $tipo_pago }}" type="text"
                                    wire:model.blur="monto_capital_formatted" placeholder="Ej: 0,00"
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                    focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                @if ($hasTC && $monto_base_preview)
                                    <div class="text-[11px] mt-1 text-gray-500 dark:text-neutral-400">
                                        Banco: <span class="font-semibold">{{ $monto_base_preview }}
                                            {{ $mov_moneda }}</span>
                                    </div>
                                @endif
                                @error('monto_capital')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if ($modoConfirmar || !$isUtilidad)
                            {{-- COMPROBANTE --}}
                            <div class="col-span-1 md:col-span-1">
                                <label class="block text-sm mb-1">Nro Comprobante <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.live="nro_comprobante" placeholder="Ej: 100"
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                @error('nro_comprobante')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- FOTO DEL COMPROBANTE --}}
                            <div class="col-span-2 md:col-span-1">
                                <x-ui.scanner model="comprobante_imagen" label="Foto Comprobante (opcional)"
                                    :file="$comprobante_imagen" />
                            </div>
                        @endif

                        {{-- IMPACTO FINANCIERO --}}
                        @if ($modoConfirmar || !$isUtilidad)
                        <div class="col-span-2 md:col-span-3">
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
                                        @if ($preview_banco_debito_fmt)
                                            <div class="mt-1 flex justify-between text-sm">
                                                @if ($preview_banco_es_ingreso)
                                                    <span class="text-gray-500 dark:text-neutral-400">Ingreso</span>
                                                    <span
                                                        class="font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">+
                                                        {{ $preview_banco_debito_fmt }}</span>
                                                @else
                                                    <span class="text-gray-500 dark:text-neutral-400">Débito</span>
                                                    <span
                                                        class="font-semibold tabular-nums text-red-500 dark:text-red-400">−
                                                        {{ $preview_banco_debito_fmt }}</span>
                                                @endif
                                            </div>
                                            <div class="my-1 border-t dark:border-neutral-700"></div>
                                        @endif
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Saldo después</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_banco_despues_fmt }}</span>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border dark:border-neutral-700 p-3">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Capital (base)</div>
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Actual</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_capital_actual_fmt }}</span>
                                        </div>
                                        @if ($preview_capital_debito_fmt)
                                            <div class="mt-1 flex justify-between text-sm">
                                                @if ($preview_capital_es_ingreso)
                                                    <span class="text-gray-500 dark:text-neutral-400">Ingreso</span>
                                                    <span
                                                        class="font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">+
                                                        {{ $preview_capital_debito_fmt }}</span>
                                                @else
                                                    <span class="text-gray-500 dark:text-neutral-400">Devolución</span>
                                                    <span
                                                        class="font-semibold tabular-nums text-red-500 dark:text-red-400">−
                                                        {{ $preview_capital_debito_fmt }}</span>
                                                @endif
                                            </div>
                                            <div class="my-1 border-t dark:border-neutral-700"></div>
                                        @endif
                                        <div class="mt-1 flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-neutral-300">Después</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">{{ $preview_capital_despues_fmt }}</span>
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
                                                {{ $impacto_detalle }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif {{-- modoConfirmar || !isUtilidad --}}

                    </div> {{-- grid --}}
                </div>
            </div>

            {{-- FOOTER BOTONES --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" wire:click="close"
                    class="px-4 py-2 cursor-pointer rounded-lg border border-gray-300 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 text-gray-700 dark:text-neutral-200
                           hover:bg-gray-50 dark:hover:bg-neutral-800">
                    Cancelar
                </button>
                <button type="button" wire:click="save" wire:target="save,comprobante_imagen"
                    wire:loading.attr="disabled" @disabled(!$this->canSave)
                    class="px-4 py-2 cursor-pointer rounded-lg text-white
                    {{ $this->canSave ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-emerald-600/60 cursor-not-allowed' }}
                    disabled:opacity-50 disabled:cursor-not-allowed">

                    <span wire:loading.remove wire:target="save,comprobante_imagen">
                        {{ $modoConfirmar ? 'Confirmar pago' : 'Guardar' }}
                    </span>
                    <span wire:loading wire:target="save,comprobante_imagen">Procesando…</span>
                </button>
            </div>

        </div> {{-- space-y --}}
    </x-ui.modal>
</div>

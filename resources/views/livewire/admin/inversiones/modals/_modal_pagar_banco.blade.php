<div>
    <x-ui.modal wire:key="pago-banco-{{ $open ? 'open' : 'closed' }}" model="open" title="Registrar pago banco"
        maxWidth="sm:max-w-2xl md:max-w-4xl" onClose="close">

        @php
            $inv = $inversion;
            $invMon = $inv?->moneda ?? 'BOB';
            $hasTC = (bool) $needs_tc;

            $isCuota = $concepto === 'PAGO_CUOTA';
            $isAbono = $concepto === 'ABONO_CAPITAL';

            $showCapital = $show_capital ?? true;
            $showInteres = $show_interes ?? true;
            $showComision = $show_comision ?? true;
            $showSeguro = $show_seguro ?? true;

            $lockTotal = $lock_total ?? false;
            $lockBreakdown = $lock_breakdown ?? false;
        @endphp

        <div class="space-y-4">

            {{-- RESUMEN --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        {{ $inv?->nombre_completo ?? '—' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
                        <span class="font-mono">{{ $inv?->codigo ?? '—' }}</span>
                        <span class="text-gray-300 dark:text-neutral-600">•</span>
                        <span>Tipo: {{ $inv?->tipo ?? '—' }}</span>
                        <span class="text-gray-300 dark:text-neutral-600">•</span>
                        <span>Base: {{ $invMon }}</span>
                    </div>
                </div>
            </div>

            {{-- BLOQUE BANCO: SOLO PAGO CUOTA --}}
            @if ($isCuota)
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Próximo pago (sugerido)</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $proxima_fecha_pago_fmt ?? '—' }}
                                </div>
                                @if (!empty($aviso_vencimiento))
                                    <div class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ $aviso_vencimiento }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-right">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Monto cuota</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 tabular-nums">
                                    {{ $monto_cuota_fmt ?? '0,00' }}
                                </div>
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">
                                    Capital: {{ $cuota_capital_fmt ?? '0,00' }} • Interés:
                                    {{ $cuota_interes_fmt ?? '0,00' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FORM --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- CONCEPTO --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Concepto <span class="text-red-500">*</span></label>
                            <select wire:model.live="concepto"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                                <option value="PAGO_CUOTA">Pago cuota</option>
                                <option value="ABONO_CAPITAL">Amortización (abono a capital)</option>
                                <option value="CARGO">Cargo (seguro/comisión financiada)</option>
                            </select>
                            @error('concepto')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- FECHAS --}}
                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Fecha (contable) <span
                                    class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="fecha"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            @error('fecha')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm mb-1">Fecha pago <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="fecha_pago" @disabled($lock_fechas)
                                class="w-full rounded-lg border px-3 py-2 disabled:cursor-not-allowed bg-white dark:bg-neutral-900
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
                            <select wire:model.live="banco_id"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
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

                        {{-- TIPO DE CAMBIO --}}
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
                        @if ($concepto !== 'ABONO_CAPITAL')
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">
                                    Monto total (base {{ $invMon }}) <span class="text-red-500">*</span>
                                </label>

                                <input type="text" wire:model.blur="monto_total_formatted" @readonly($lockTotal)
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                    border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500/40
                                    {{ $lockTotal ? 'opacity-70 cursor-not-allowed' : '' }}">

                                @error('monto_total')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif


                        {{-- DESGLOSE --}}
                        @if ($showCapital)
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">Capital (base) @if ($isAbono || $isCuota)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <input type="text" wire:model.blur="monto_capital_formatted" placeholder="0,00"
                                    @readonly($lockBreakdown)
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500/40
                                           {{ $lockBreakdown ? 'opacity-70 cursor-not-allowed' : '' }}">
                                @error('monto_capital')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if ($showInteres)
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">Interés</label>
                                <input type="text" wire:model.blur="monto_interes_formatted" placeholder="0,00"
                                    @readonly($lockBreakdown)
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500/40
                                           {{ $lockBreakdown ? 'opacity-70 cursor-not-allowed' : '' }}">
                                @error('monto_interes')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if ($showComision)
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">Comisión</label>
                                <input type="text" wire:model.blur="monto_comision_formatted" placeholder="0,00"
                                    @readonly($lockBreakdown)
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500/40
                                           {{ $lockBreakdown ? 'opacity-70 cursor-not-allowed' : '' }}">
                                @error('monto_comision')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if ($showSeguro)
                            <div class="md:col-span-1">
                                <label class="block text-sm mb-1">Seguro</label>
                                <input type="text" wire:model.blur="monto_seguro_formatted" placeholder="0,00"
                                    @readonly($lockBreakdown)
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 text-right tabular-nums
                                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500/40
                                           {{ $lockBreakdown ? 'opacity-70 cursor-not-allowed' : '' }}">
                                @error('monto_seguro')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

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
                                                {{ $impacto_detalle }}</div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

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
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:opacity-90 cursor-pointer
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save,comprobante_imagen">Guardar</span>
                    <span wire:loading wire:target="save,comprobante_imagen">Guardando…</span>
                </button>
            </div>
        @endslot

    </x-ui.modal>
</div>

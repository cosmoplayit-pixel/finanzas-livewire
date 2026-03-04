{{-- MODAL MOVIMIENTO (COMPRA / DEVOLUCION - UNICO) --}}
<x-ui.modal wire:key="movimiento-modal-{{ $editorRendicionId ?? 'none' }}" model="openMovimientoModal"
    title="{{ $mov_modal_tipo === 'DEVOLUCION' ? 'Registrar Devolución' : 'Registrar Compra' }}"
    maxWidth="sm:max-w-2xl md:max-w-4xl" onClose="closeMovimientoModal">

    {{-- SELECTOR TIPO --}}
    <div class="mb-4 flex items-center gap-3">
        <div class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
            <button type="button" wire:click="setMovimientoTipo('COMPRA')"
                class="cursor-pointer px-4 py-2 text-xs font-semibold transition
                {{ $mov_modal_tipo === 'COMPRA'
                    ? 'bg-gray-900 hover:bg-gray-700 text-white dark:bg-white dark:text-gray-900'
                    : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                Compra
            </button>

            <button type="button" wire:click="setMovimientoTipo('DEVOLUCION')"
                class="cursor-pointer px-4 py-2 text-xs font-semibold transition
                {{ $mov_modal_tipo === 'DEVOLUCION'
                    ? 'bg-gray-900 hover:bg-gray-700 text-white dark:bg-white dark:text-gray-900'
                    : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                Devolución
            </button>
        </div>

        <div class="text-xs text-gray-500 dark:text-neutral-400">
            {{ $mov_modal_tipo === 'DEVOLUCION' ? 'Devuelve saldo al banco' : 'Registra un gasto de la rendición' }}
        </div>
    </div>

    {{-- CUERPO (ESTILO PLANTILLA PRESUPUESTO: SIN “CAJAS” EXTRA) --}}
    <div class="space-y-2">

        <div class="space-y-2">

            @php
                $baseMoneda = $editorMonedaBase ?: 'BOB';
            @endphp

            {{-- =========================
                COMPRA
            ========================= --}}
            @if ($mov_modal_tipo === 'COMPRA')
                {{-- FILA 1: FECHA / MONEDA / MONTO --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Fecha Pago: <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" wire:model.live="mov_fecha"
                            class="cursor-pointer w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_fecha')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Moneda: <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="mov_moneda"
                            class="cursor-pointer w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="BOB">BOB</option>
                            <option value="USD">USD</option>
                        </select>
                        @error('mov_moneda')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Monto: ({{ $mov_moneda }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live.blur="mov_monto_formatted" placeholder="Ej: 1.234,56"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_monto')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- TIPO DE CAMBIO (SI APLICA) --}}
                @if ($mov_needs_tc)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm mb-1">
                                Tipo de cambio: <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.live.blur="mov_tipo_cambio_formatted"
                                placeholder="Ej: 6,96"
                                class="w-full rounded-lg border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300/60 dark:border-neutral-700/60
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('mov_tipo_cambio')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">
                                Equivalente en moneda base: ({{ $baseMoneda }})
                            </label>
                            <input type="text" readonly value="{{ $mov_monto_base_preview ?? '—' }}" placeholder="—"
                                class="w-full rounded-lg border px-3 py-2
                                       bg-gray-50 dark:bg-neutral-900/50
                                       border-gray-300/60 dark:border-neutral-700/60
                                       text-gray-900 dark:text-neutral-100" />
                            <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">
                                Se calcula automáticamente el monto.
                            </div>
                        </div>

                    </div>
                @endif

                {{-- FILA 2: ENTIDAD / PROYECTO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Entidad: <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="mov_entidad_id"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($editorEntidades as $e)
                                <option value="{{ $e['id'] }}">{{ $e['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('mov_entidad_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Proyecto: <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="mov_proyecto_id" @disabled(empty($mov_entidad_id))
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40
                                   disabled:opacity-60 disabled:bg-gray-100 dark:disabled:bg-neutral-800
                                   disabled:cursor-not-allowed">
                            <option value="">
                                {{ empty($mov_entidad_id) ? 'Seleccione entidad primero…' : 'Seleccione…' }}
                            </option>
                            @foreach ($editorProyectos as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('mov_proyecto_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- FILA 3: TIPO / NRO / OBS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Tipo comprobante: <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="mov_tipo_comprobante"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Seleccione…</option>
                            <option value="FACTURA">Factura</option>
                            <option value="RECIBO">Recibo</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                        </select>
                        @error('mov_tipo_comprobante')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Nro comprobante:</label>
                        <input type="text" wire:model.live="mov_nro_comprobante"
                            placeholder="Ej: 12345 / N° / código"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_nro_comprobante')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Observación:</label>
                        <input type="text" wire:model.live="mov_observacion" placeholder="Ej: Compra de materiales…"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_observacion')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- FOTO --}}
                <div>
                    <label class="block text-sm mb-1">Foto (opcional):</label>

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
                                    @if ($mov_foto)
                                        {{ $mov_foto->getClientOriginalName() }}
                                    @else
                                        JPG, PNG o PDF (máx. 5MB)
                                    @endif
                                </div>
                            </div>
                        </div>

                        <input type="file" wire:model="mov_foto" class="hidden" accept=".jpg,.jpeg,.png,.pdf" />
                    </label>

                    @error('mov_foto')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- IMPACTO FINANCIERO COMPRA --}}
                <div
                    class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-4">
                    <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                            Impacto financiero ({{ $baseMoneda }})
                        </div>
                    </div>
                    <div class="p-3 sm:p-4 grid grid-cols-1 gap-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-neutral-400">Saldo a rendir actual</span>
                            <span class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ number_format($mov_saldo_actual_preview, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-red-600 dark:text-red-400">
                            <span>Monto a descontar</span>
                            <span class="font-medium">
                                - {{ $mov_monto_base_preview ?: '0,00' }}
                            </span>
                        </div>
                        <div
                            class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo a rendir</span>
                            <span
                                class="font-bold {{ $mov_monto_excede_saldo ? 'text-red-600' : 'text-gray-900 dark:text-neutral-100' }}">
                                {{ number_format($mov_saldo_despues_preview, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =========================
                DEVOLUCION
            ========================= --}}
            @if ($mov_modal_tipo === 'DEVOLUCION')
                {{-- FILA 1: BANCO / MONEDA / MONTO --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Fecha Pago: <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" wire:model.live="mov_fecha"
                            class="cursor-pointer w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_fecha')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Banco: <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="mov_banco_id"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($editorBancos as $b)
                                <option value="{{ $b['id'] }}">
                                    {{ $b['nombre'] }} — {{ $b['numero_cuenta'] }} ({{ $b['moneda'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('mov_banco_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Monto: ({{ $mov_moneda }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live.blur="mov_monto_formatted" placeholder="Ej: 1.234,56"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_monto')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- TIPO DE CAMBIO (SI APLICA) --}}
                @if ($mov_needs_tc)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm mb-1">
                                Tipo de cambio: <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.live.blur="mov_tipo_cambio_formatted"
                                placeholder="Ej: 6,96"
                                class="w-full rounded-lg border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300/60 dark:border-neutral-700/60
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('mov_tipo_cambio')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">
                                Equivalente en moneda base: ({{ $baseMoneda }})
                            </label>
                            <input type="text" readonly value="{{ $mov_monto_base_preview ?? '—' }}"
                                placeholder="—"
                                class="w-full rounded-lg border px-3 py-2
                                       bg-gray-50 dark:bg-neutral-900/50
                                       border-gray-300/60 dark:border-neutral-700/60
                                       text-gray-900 dark:text-neutral-100" />
                            <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">
                                Se calcula automáticamente el monto.
                            </div>
                        </div>

                    </div>
                @endif

                {{-- FILA 2: NRO / OBS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Nro transacción: <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live="mov_nro_transaccion"
                            placeholder="Ej: TRX-000123 / N° operación"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_nro_transaccion')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Observación: </label>
                        <input type="text" wire:model.live="mov_observacion"
                            placeholder="Ej: Devolución de saldo no utilizado…"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_observacion')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- FOTO --}}
                <div>
                    <label class="block text-sm mb-1">Foto (opcional): </label>

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
                                    @if ($mov_foto)
                                        {{ $mov_foto->getClientOriginalName() }}
                                    @else
                                        JPG, PNG o PDF (máx. 5MB)
                                    @endif
                                </div>
                            </div>
                        </div>

                        <input type="file" wire:model="mov_foto" class="hidden" accept=".jpg,.jpeg,.png,.pdf" />
                    </label>

                    @error('mov_foto')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- IMPACTO FINANCIERO DEVOLUCIÓN --}}
                <div
                    class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-4">
                    <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                            Impacto financiero
                        </div>
                    </div>
                    <div class="p-3 sm:p-4 grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- SALDO A RENDIR --}}
                        <div class="space-y-3 md:border-r md:border-gray-200 md:dark:border-neutral-700 md:pr-6">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                Saldo a Rendir ({{ $baseMoneda }})
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                                <span class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ number_format($mov_saldo_actual_preview, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-red-600 dark:text-red-400">
                                <span>Devolución</span>
                                <span class="font-medium">
                                    - {{ $mov_monto_base_preview ?: '0,00' }}
                                </span>
                            </div>
                            <div
                                class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                                <span
                                    class="font-bold {{ $mov_monto_excede_saldo ? 'text-red-600' : 'text-gray-900 dark:text-neutral-100' }}">
                                    {{ number_format($mov_saldo_despues_preview, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- BANCO --}}
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                Banco {{ $mov_banco_moneda_preview ? "($mov_banco_moneda_preview)" : '' }}
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-neutral-400">Saldo actual</span>
                                <span class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ number_format($mov_banco_actual_preview, 2, ',', '.') }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm text-emerald-600 dark:text-emerald-400">
                                <span>Ingreso</span>
                                <span class="font-medium">
                                    + {{ $mov_monto_formatted ?: '0,00' }}
                                </span>
                            </div>
                            <div
                                class="pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-neutral-100">Nuevo saldo</span>
                                <span class="font-bold text-gray-900 dark:text-neutral-100">
                                    {{ number_format($mov_banco_despues_preview, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <p class="text-xs text-gray-500 dark:text-neutral-400">
                <span class="text-red-500">*</span> Campos obligatorios.
            </p>

        </div>
    </div>

    {{-- FOOTER --}}
    @slot('footer')
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">

            <button type="button" wire:click="closeMovimientoModal"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="addMovimiento" wire:loading.attr="disabled"
                wire:target="addMovimiento, mov_foto" @disabled(!$this->puedeGuardarMovimiento)
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="addMovimiento, mov_foto">Guardar</span>
                <span wire:loading wire:target="addMovimiento, mov_foto">Procesando…</span>
            </button>

        </div>
    @endslot
</x-ui.modal>

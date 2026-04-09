{{-- MODAL MOVIMIENTO (COMPRA / DEVOLUCION - UNICO) --}}
<x-ui.modal wire:key="movimiento-modal-{{ $editorRendicionId ?? 'none' }}" model="openMovimientoModal"
    maxWidth="sm:max-w-2xl md:max-w-4xl" onClose="closeMovimientoModal">

    <x-slot:title>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-wrap">
            <span class="shrink-0">
                @if ($mov_edit_id)
                    {{ $mov_modal_tipo === 'DEVOLUCION' ? 'Editar Devolución' : 'Editar Compra' }}
                @else
                    {{ $mov_modal_tipo === 'DEVOLUCION' ? 'Registrar Devolución' : 'Registrar Compra' }}
                @endif
            </span>
        </div>
    </x-slot:title>

    {{-- SELECTOR TIPO --}}
    <div class="mb-4 flex items-center gap-3">
        <div
            class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden {{ $mov_edit_id ? 'opacity-50 pointer-events-none' : '' }}">
            <button type="button" @if (!$mov_edit_id) wire:click="setMovimientoTipo('COMPRA')" @endif
                @disabled(!!$mov_edit_id)
                class="cursor-pointer px-4 py-2 text-xs font-semibold transition
                {{ $mov_modal_tipo === 'COMPRA'
                    ? 'bg-gray-900 hover:bg-gray-700 text-white dark:bg-white dark:text-gray-900'
                    : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                Compra
            </button>

            <button type="button" @if (!$mov_edit_id) wire:click="setMovimientoTipo('DEVOLUCION')" @endif
                @disabled(!!$mov_edit_id)
                class="cursor-pointer px-4 py-2 text-xs font-semibold transition
                {{ $mov_modal_tipo === 'DEVOLUCION'
                    ? 'bg-gray-900 hover:bg-gray-700 text-white dark:bg-white dark:text-gray-900'
                    : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                Devolución
            </button>
        </div>

        <div class="text-xs text-gray-500 dark:text-neutral-400">
            @if ($mov_edit_id)
                <span class="text-amber-600 dark:text-amber-400 font-medium">Editando movimiento</span>
            @else
                {{ $mov_modal_tipo === 'DEVOLUCION' ? 'Devuelve saldo al banco' : 'Registra un gasto de la rendición' }}
            @endif
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

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- ENTIDAD / CLIENTE --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">
                            Cliente: <span class="text-red-500">*</span>
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

                    {{-- PROYECTO --}}
                    <div class="col-span-1 lg:col-span-1">
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
                                <option value="{{ $p['id'] }}" @selected((int) $p['id'] === (int) $mov_proyecto_id)>{{ $p['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('mov_proyecto_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MONEDA --}}
                    <div class="col-span-1 lg:col-span-1">
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

                    {{-- TIPO COMPROBANTE --}}
                    <div class="col-span-1 lg:col-span-1">
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

                    {{-- NRO COMPROBANTE --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">Nro comprobante:</label>
                        <input type="text" wire:model.live="mov_nro_comprobante"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_nro_comprobante')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DETALLE --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">Detalle:</label>
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

                    {{-- MONTO --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">
                            Monto: ({{ $mov_moneda }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live.blur="mov_monto_formatted" placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_monto')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHA DE PAGO --}}
                    <div class="col-span-1 lg:col-span-1">
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

                    {{-- FOTO --}}
                    <div class="col-span-2 lg:col-span-1">
                        <x-ui.scanner model="mov_foto" :label="$mov_edit_id ? 'Comprobante Actual' : 'Comprobante'" :file="$mov_foto" />

                        {{-- COMPROBANTE EXISTENTE (solo en edición, si no se subió uno nuevo) --}}
                        @if ($mov_edit_id && $mov_existing_foto_path && !$mov_foto)
                            @php
                                $extE = strtolower(pathinfo($mov_existing_foto_path, PATHINFO_EXTENSION));
                                $esImgE = in_array($extE, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                $urlE = asset('storage/' . $mov_existing_foto_path);
                            @endphp
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <span class="text-gray-500 dark:text-neutral-400">Actual:</span>
                                @if ($esImgE)
                                    <button type="button" wire:click="openFotoComprobante('{{ $urlE }}')"
                                        class="inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-700 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Ver imagen
                                    </button>
                                @else
                                    <a href="{{ $urlE }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-700 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                        </svg>
                                        Ver archivo
                                    </a>
                                @endif
                                <button type="button" wire:click="removeExistingFoto"
                                    class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 font-medium ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                    </svg>
                                    Quitar
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($mov_needs_tc)
                        {{-- TIPO DE CAMBIO --}}
                        <div class="col-span-1 lg:col-span-1">
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

                        {{-- EQUIVALENTE EN MONEDA BASE --}}
                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-sm mb-1">
                                Equivalente moneda base: ({{ $baseMoneda }})
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
                    @endif
                </div>

                {{-- IMPACTO FINANCIERO COMPRA --}}
                <div
                    class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-4">
                    <div class="px-3 sm:px-4 py-1 border-b dark:border-neutral-700 flex justify-between items-center">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto financiero</div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            ({{ $baseMoneda }})</div>
                    </div>

                    {{-- MOBILE: tira compacta --}}
                    <div class="md:hidden p-2">
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-1 mb-1">
                            Saldo a Rendir
                        </div>
                        <div
                            class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo actual</div>
                                <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                    {{ number_format($mov_saldo_actual_preview, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Descuento</div>
                                <div class="text-xs font-bold tabular-nums text-red-600 dark:text-red-400">
                                    - {{ $mov_monto_base_preview ?: '0,00' }}
                                </div>
                            </div>
                            <div class="py-2 px-1">
                                <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Nuevo saldo</div>
                                <div
                                    class="text-xs font-bold tabular-nums {{ $mov_monto_excede_saldo ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-neutral-200' }}">
                                    {{ number_format($mov_saldo_despues_preview, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DESKTOP: 3 columnas --}}
                    <div class="hidden md:block p-3 sm:p-4">
                        <div class="grid grid-cols-3 gap-3 text-sm divide-x divide-gray-200 dark:divide-neutral-700">
                            <div class="text-center">
                                <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Saldo a rendir actual
                                </div>
                                <div class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ number_format($mov_saldo_actual_preview, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-center pl-3">
                                <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Monto a descontar</div>
                                <div class="font-medium text-red-600 dark:text-red-400">
                                    - {{ $mov_monto_base_preview ?: '0,00' }}
                                </div>
                            </div>
                            <div class="text-center pl-3">
                                <div class="text-gray-500 dark:text-neutral-400 mb-1 text-xs">Nuevo saldo a rendir
                                </div>
                                <div
                                    class="font-bold {{ $mov_monto_excede_saldo ? 'text-red-600' : 'text-gray-900 dark:text-neutral-100' }}">
                                    {{ number_format($mov_saldo_despues_preview, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =========================
                DEVOLUCION
            ========================= --}}
            @if ($mov_modal_tipo === 'DEVOLUCION')

                {{-- FILA 1: BANCO / MONTO / NRO TRANSACCION --}}
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- BANCO --}}
                    <div class="col-span-1 lg:col-span-1">
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
                                    {{ $b['nombre'] }} — {{ $b['titular'] }} ({{ $b['moneda'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('mov_banco_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MONTO --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">
                            Monto: ({{ $mov_moneda }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live.blur="mov_monto_formatted" placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_monto')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NRO TRANSACCION --}}
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-sm mb-1">
                            Nro transacción: <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live="mov_nro_transaccion"
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_nro_transaccion')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- FECHA DE PAGO --}}
                    <div class="col-span-1 lg:col-span-1">
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

                    {{-- DETALLE --}}
                    <div class="col-span-2 lg:col-span-1">
                        <label class="block text-sm mb-1">Detalle:</label>
                        <input type="text" wire:model.live="mov_observacion"
                            placeholder="Ej: Devolución de saldo..."
                            class="w-full rounded-lg border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300/60 dark:border-neutral-700/60
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('mov_observacion')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- FOTO --}}
                    <div class="col-span-2 lg:col-span-1">
                        <x-ui.scanner model="mov_foto" :label="$mov_edit_id ? 'Comprobante Actual' : 'Comprobante'" :file="$mov_foto" />

                        @if ($mov_edit_id && $mov_existing_foto_path && !$mov_foto)
                            @php
                                $extE = strtolower(pathinfo($mov_existing_foto_path, PATHINFO_EXTENSION));
                                $esImgE = in_array($extE, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                $urlE = asset('storage/' . $mov_existing_foto_path);
                            @endphp
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <span class="text-gray-500 dark:text-neutral-400">Actual:</span>
                                @if ($esImgE)
                                    <button type="button" wire:click="openFotoComprobante('{{ $urlE }}')"
                                        class="inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-700 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Ver imagen
                                    </button>
                                @else
                                    <a href="{{ $urlE }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-700 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                        </svg>
                                        Ver archivo
                                    </a>
                                @endif
                                <button type="button" wire:click="removeExistingFoto"
                                    class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 font-medium ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                    </svg>
                                    Quitar
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- TIPO DE CAMBIO (SI APLICA) --}}
                    @if ($mov_needs_tc)
                        <div class="col-span-1 lg:col-span-1">
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

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-sm mb-1">
                                Equivalente moneda base: ({{ $baseMoneda }})
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
                    @endif
                </div>

                {{-- IMPACTO FINANCIERO DEVOLUCIÓN --}}
                <div
                    class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden mt-4">
                    <div class="px-3 sm:px-4 py-1 border-b dark:border-neutral-700">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto financiero</div>
                    </div>

                    {{-- MOBILE: tiras compactas --}}
                    <div class="md:hidden p-2 space-y-2">
                        {{-- Saldo a rendir strip --}}
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-1 mb-1">
                                Saldo a Rendir ({{ $baseMoneda }})
                            </div>
                            <div
                                class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                                <div class="py-2 px-1">
                                    <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo actual
                                    </div>
                                    <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                        {{ number_format($mov_saldo_actual_preview, 2, ',', '.') }}
                                    </div>
                                </div>
                                <div class="py-2 px-1">
                                    <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Devolución
                                    </div>
                                    <div class="text-xs font-bold tabular-nums text-red-600 dark:text-red-400">
                                        - {{ $mov_monto_base_preview ?: '0,00' }}
                                    </div>
                                </div>
                                <div class="py-2 px-1">
                                    <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Nuevo saldo
                                    </div>
                                    <div
                                        class="text-xs font-bold tabular-nums {{ $mov_monto_excede_saldo ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-neutral-200' }}">
                                        {{ number_format($mov_saldo_despues_preview, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Banco strip --}}
                        @if ($mov_banco_id)
                            <div>
                                <div
                                    class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-1 mb-1">
                                    Banco {{ $mov_banco_moneda_preview ? "({$mov_banco_moneda_preview})" : '' }}
                                </div>
                                <div
                                    class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                                    <div class="py-2 px-1">
                                        <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo
                                            actual</div>
                                        <div
                                            class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                            {{ number_format($mov_banco_actual_preview, 2, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="py-2 px-1">
                                        <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Ingreso
                                        </div>
                                        <div
                                            class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                            + {{ $mov_monto_formatted ?: '0,00' }}
                                        </div>
                                    </div>
                                    <div class="py-2 px-1">
                                        <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Nuevo saldo
                                        </div>
                                        <div
                                            class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                            {{ number_format($mov_banco_despues_preview, 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- DESKTOP: 2 columnas --}}
                    <div class="hidden md:grid px-2 py-1 sm:p-4 grid-cols-2 gap-6">

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
                                <span class="font-medium">- {{ $mov_monto_base_preview ?: '0,00' }}</span>
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
                                <span class="font-medium">+ {{ $mov_monto_formatted ?: '0,00' }}</span>
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
        </div>
    </div>

    {{-- FOOTER --}}
    @slot('footer')
        <div class="grid grid-cols-2 gap-2 w-full sm:flex sm:justify-end sm:gap-3">

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

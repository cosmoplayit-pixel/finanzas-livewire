{{-- resources/views/livewire/admin/inversiones/modals/_modal_movimiento.blade.php --}}

<div>
    <x-ui.modal wire:key="inversion-movimientos-{{ $openMovimientosModal ? 'open' : 'closed' }}"
        model="openMovimientosModal" title="Movimientos de inversión" maxWidth="sm:max-w-2xl md:max-w-7xl"
        maxHeight="sm:max-h-[95vh]" onClose="closeMovimientos">

        <div class="space-y-3">

            {{-- =========================================================
                HEADER / RESUMEN
            ========================================================= --}}
            @if (!$isBanco)
                {{-- ===================== HEADER PRIVADO ===================== --}}
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="px-4 py-3 border-b dark:border-neutral-700">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                            {{-- IZQUIERDA --}}
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $inversionNombre }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
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
                                        <span>{{ $inversionCodigo }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 3v18" />
                                            <path d="M3 12h18" />
                                        </svg>
                                        <span>{{ $inversionTipo }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 1v22" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                        </svg>
                                        <span class="tabular-nums">{{ $capitalActualFmt }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <path d="M16 2v4" />
                                            <path d="M8 2v4" />
                                            <path d="M3 10h18" />
                                        </svg>
                                        <span>{{ $fechaInicioFmt }} - {{ $fechaVencFmt }}</span>
                                    </span>
                                </div>
                            </div>

                            {{-- DERECHA: ACCIONES PRIVADO --}}
                            <div class="shrink-0 w-full sm:w-auto">
                                <div class="w-full sm:w-auto flex flex-wrap justify-end gap-2">
                                    <button type="button"
                                        wire:click="$dispatch('openPagarUtilidad', { inversionId: {{ $inversionId }} })"
                                        @disabled($bloqueado || !$inversionId)
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                        bg-emerald-600 text-white hover:opacity-90
                                        disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="{{ 'Registrar utilidad' }}">
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-white/15">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 1v22" />
                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                            </svg>
                                        </span>
                                        <span>Registrar Pago</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @else
                {{-- ===================== HEADER BANCO ===================== --}}
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="px-4 py-3 border-b dark:border-neutral-700">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                            {{-- IZQUIERDA --}}
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $inversionNombre }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
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
                                        <span>{{ $inversionCodigo }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 3v18" />
                                            <path d="M3 12h18" />
                                        </svg>
                                        <span>{{ $inversionTipo }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 10h18" />
                                            <path d="M5 10V20" />
                                            <path d="M19 10V20" />
                                            <path d="M2 20h20" />
                                            <path d="M12 2 2 7h20L12 2z" />
                                        </svg>
                                        <span class="tabular-nums">{{ $saldoDeudaFmt }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <!-- icon: porcentaje / interés -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <line x1="15" y1="9" x2="9" y2="15" />
                                            <circle cx="9.5" cy="9.5" r="1" />
                                            <circle cx="14.5" cy="14.5" r="1" />
                                        </svg>

                                        <span>% Mensual: {{ $tasaAmortizacionFmt }} </span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M12 7v5l3 3" />
                                        </svg>
                                        <span>{{ $plazoMeses }} meses</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <path d="M16 2v4" />
                                            <path d="M8 2v4" />
                                            <path d="M3 10h18" />
                                            <path d="M8 14h.01" />
                                            <path d="M12 14h.01" />
                                            <path d="M16 14h.01" />
                                        </svg>
                                        <span>Día pago: {{ $diaPago }}</span>
                                    </span>

                                    <span class="text-gray-300 dark:text-neutral-600">•</span>

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <path d="M16 2v4" />
                                            <path d="M8 2v4" />
                                            <path d="M3 10h18" />
                                        </svg>
                                        <span>{{ $fechaInicioFmt }} - {{ $fechaVencFmt }}</span>
                                    </span>
                                </div>
                            </div>

                            {{-- DERECHA: ACCIONES BANCO --}}
                            <div class="shrink-0 w-full sm:w-auto">
                                <div class="w-full sm:w-auto flex flex-wrap justify-end gap-2">
                                    <button type="button"
                                        wire:click="$dispatch('openPagarBanco', { inversionId: {{ $inversionId }} })"
                                        @disabled($bloqueado)
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                            bg-indigo-600 text-white hover:opacity-90
                                            disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="{{ 'Registrar pago banco' }}">
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-white/15">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 10h18" />
                                                <path d="M5 10V20" />
                                                <path d="M19 10V20" />
                                                <path d="M9 10V20" />
                                                <path d="M15 10V20" />
                                                <path d="M2 20h20" />
                                                <path d="M12 2 2 7h20L12 2z" />
                                            </svg>
                                        </span>
                                        <span>Registrar pago</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif


            {{-- =========================================================
                TABLA MOVIMIENTOS
            ========================================================= --}}
            @if (!$isBanco)
                {{-- ===================== TABLA PRIVADO ===================== --}}
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto overflow-y-auto max-h-[75vh]">
                        <table class="w-full text-[13px] min-w-[1200px] align-middle">
                            <thead
                                class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                <tr class="text-center text-[12px] tracking-wide text-gray-600 dark:text-neutral-300">
                                    <th class="p-3 w-[50px]">#</th>
                                    <th class="p-3 w-[120px]">Fecha</th>
                                    <th class="p-3 w-[120px]">Fecha pago</th>
                                    <th class="p-3 min-w-[260px] text-left">Descripción</th>
                                    <th class="p-3 w-[140px]">Comprobante</th>
                                    <th class="p-3 w-[150px]">Capital</th>
                                    <th class="p-3 w-[150px]">Utilidad</th>
                                    <th class="p-3 w-[110px]">% Utilidad</th>
                                    <th class="p-3 w-[120px]">Estado</th>
                                    <th class="p-3 w-[170px]">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($movimientos as $m)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-neutral-900/50 text-center align-middle font-extralight">

                                        {{-- ÍNDICE --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['idx'] }}
                                        </td>

                                        {{-- FECHA --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha_inicio'] }} {{ $m['fecha'] }}
                                        </td>

                                        {{-- FECHA PAGO --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha_pago'] }}
                                        </td>

                                        {{-- DESCRIPCIÓN --}}
                                        <td class="p-2 text-left align-middle">
                                            <div class="text-gray-900 dark:text-neutral-100">
                                                {{ $m['descripcion'] }}
                                            </div>

                                            @if (!empty($m['banco_linea']))
                                                <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                                    {{ $m['banco_linea'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- NRO COMPROBANTE --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['comprobante'] }}
                                        </td>

                                        {{-- CAPITAL --}}
                                        <td
                                            class="p-2 text-center font-semibold tabular-nums align-middle
                                            {{ ($m['tipo'] ?? '') === 'INGRESO_CAPITAL'
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : (($m['tipo'] ?? '') === 'DEVOLUCION_CAPITAL'
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-900 dark:text-neutral-100') }}">

                                            <div class="flex flex-col items-center leading-tight">
                                                <span>{{ $m['capital'] }}</span>

                                                @if (!empty($m['capital_actual_linea']))
                                                    <span
                                                        class="mt-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                        {{ $m['capital_actual_linea'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- UTILIDAD --}}
                                        <td
                                            class="p-2 text-center font-semibold tabular-nums align-middle font-medium
                                            {{ ($m['tipo'] ?? '') === 'PAGO_UTILIDAD'
                                                ? (($m['estado'] ?? '') === 'PENDIENTE'
                                                    ? 'text-amber-700 dark:text-amber-300'
                                                    : 'text-sky-700 dark:text-sky-300')
                                                : 'text-gray-900 dark:text-neutral-100' }}">
                                            {{ $m['utilidad'] }}
                                        </td>

                                        {{-- % UTILIDAD --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['porcentaje_utilidad'] }}
                                        </td>

                                        {{-- ESTADO --}}
                                        <td class="p-2 align-middle text-[10px] font-bold">
                                            @if (($m['tipo'] ?? '') === 'CAPITAL_INICIAL')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-gray-100 text-gray-700
                                                    dark:bg-neutral-800 dark:text-neutral-200">
                                                    INICIAL
                                                </span>
                                            @elseif (($m['tipo'] ?? '') === 'PAGO_UTILIDAD')
                                                @if (($m['estado'] ?? '') === 'PENDIENTE')
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded
                                                        bg-amber-100 text-amber-700
                                                        dark:bg-amber-900/30 dark:text-amber-300">
                                                        PENDIENTE
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded
                                                        bg-sky-100 text-sky-700
                                                        dark:bg-sky-900/30 dark:text-sky-300">
                                                        PAGADO
                                                    </span>
                                                @endif
                                            @elseif (($m['tipo'] ?? '') === 'INGRESO_CAPITAL')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-emerald-100 text-emerald-700
                                                    dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    INGRESO
                                                </span>
                                            @elseif (($m['tipo'] ?? '') === 'DEVOLUCION_CAPITAL')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-red-100 text-red-700
                                                    dark:bg-red-900/30 dark:text-red-300">
                                                    DEVOLUCIÓN
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">—</span>
                                            @endif
                                        </td>

                                        {{-- ACCIONES (PRIVADO) --}}
                                        <td class="p-2 align-middle">
                                            <div class="flex items-center justify-end gap-2 w-full">

                                                {{-- Confirmar (SOLO primer pendiente) --}}
                                                @if (!empty($m['puede_confirmar_privado']))
                                                    <div x-data class="flex items-center">
                                                        <button type="button"
                                                            class="h-7 px-2 cursor-pointer rounded-lg text-xs font-semibold inline-flex items-center gap-2
                                                            bg-green-600 text-white hover:bg-green-700"
                                                            title="Confirmar pago"
                                                            @click.prevent="
                                                                Swal.fire({
                                                                    title: '¿Confirmar pago?',
                                                                    text: 'Esto debitará el banco y marcará la utilidad como PAGADA.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, confirmar',
                                                                    cancelButtonText: 'Cancelar',
                                                                    reverseButtons: true,
                                                                    confirmButtonColor: '#16a34a',
                                                                    cancelButtonColor: '#6b7280',
                                                                }).then((r) => { if (r.isConfirmed) { $wire.confirmarPagoUtilidad({{ (int) $m['id'] }}); } });
                                                            ">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M20 6 9 17l-5-5" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif

                                                {{-- Ver imagen --}}
                                                @if (!empty($m['tiene_imagen']))
                                                    <button type="button"
                                                        wire:click="verFotoMovimiento({{ $m['id'] }})"
                                                        class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                        border-gray-300 text-gray-700 hover:bg-gray-100
                                                        dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                                        title="Ver imagen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18"
                                                                rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <path d="M21 15l-5-5L5 21" />
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Eliminar por fila (excepto CAPITAL_INICIAL) --}}
                                                @if (($m['tipo'] ?? '') !== 'CAPITAL_INICIAL')
                                                    @if (!empty($m['puede_eliminar_fila']))
                                                        {{-- (Tu lógica actual de borrado, igualita) --}}
                                                        @if (($m['estado'] ?? '') === 'PAGADO')
                                                            <button type="button"
                                                                class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                                border-red-300 text-red-700 hover:bg-red-100
                                                                dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                                title="Eliminar (requiere contraseña)"
                                                                wire:click="abrirEliminarFilaModal({{ (int) $m['id'] }})">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path d="M3 6h18" />
                                                                    <path d="M8 6V4h8v2" />
                                                                    <path d="M6 6l1 16h10l1-16" />
                                                                    <path d="M10 11v6" />
                                                                    <path d="M14 11v6" />
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <div x-data class="flex items-center">
                                                                <button type="button"
                                                                    class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                                        border-red-300 text-red-700 hover:bg-red-100
                                                                        dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                                    title="Eliminar registro"
                                                                    @click.prevent="
                                                                        Swal.fire({
                                                                            title: '¿Eliminar este registro?',
                                                                            text: 'Esta acción revertirá banco/capital según corresponda.',
                                                                            icon: 'warning',
                                                                            showCancelButton: true,
                                                                            confirmButtonText: 'Sí, eliminar',
                                                                            cancelButtonText: 'Cancelar',
                                                                            reverseButtons: true,
                                                                            confirmButtonColor: '#dc2626',
                                                                            cancelButtonColor: '#6b7280',
                                                                        }).then((r) => { if (r.isConfirmed) { $wire.eliminarMovimientoFila({{ (int) $m['id'] }}); } });
                                                                    ">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-4 h-4" viewBox="0 0 24 24"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-width="2" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path d="M3 6h18" />
                                                                        <path d="M8 6V4h8v2" />
                                                                        <path d="M6 6l1 16h10l1-16" />
                                                                        <path d="M10 11v6" />
                                                                        <path d="M14 11v6" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    @else
                                                        {{-- ✅ Bloqueado SOLO para INGRESO/DEVOLUCIÓN que no son el último --}}
                                                        @if (in_array($m['tipo'] ?? '', ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true))
                                                            <button type="button"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border
                                                                border-gray-200 text-gray-400 cursor-not-allowed
                                                                dark:border-neutral-700 dark:text-neutral-500"
                                                                title="No se puede eliminar porque existen movimientos posteriores. Solo se permite eliminar si es el último registro."
                                                                disabled>
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path d="M3 6h18" />
                                                                    <path d="M8 6V4h8v2" />
                                                                    <path d="M6 6l1 16h10l1-16" />
                                                                    <path d="M10 11v6" />
                                                                    <path d="M14 11v6" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    @endif
                                                @endif

                                                {{-- Eliminar TODO (Capital inicial) => Modal contraseña --}}
                                                @if (!empty($m['es_capital_inicial']))
                                                    <div class="flex items-center">
                                                        <button type="button"
                                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                            border-red-400 text-red-800 hover:bg-red-100
                                                            dark:border-red-700 dark:text-red-300 dark:hover:bg-red-500/20"
                                                            title="Eliminar inversión completa"
                                                            wire:click="abrirEliminarTodoModal">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M8 6V4h8v2" />
                                                                <path d="M6 6l1 16h10l1-16" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10"
                                            class="p-6 text-center text-gray-500 dark:text-neutral-400">
                                            No hay movimientos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            {{-- sticky bottom-0 z-10: el tfoot queda fijo al hacer scroll vertical --}}
                            <tfoot
                                class="sticky bottom-0 z-10 bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                                {{-- PAGADOS --}}
                                <tr class="align-middle">
                                    <td colspan="5" class="p-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-[11px] font-bold tracking-wide uppercase
                                                bg-sky-100 text-sky-800 border border-sky-200
                                                dark:bg-sky-900/25 dark:text-sky-200 dark:border-sky-800/40">
                                                <span class="inline-flex w-2 h-2 rounded-full bg-sky-500"></span>
                                                Pagos realizados
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Capital --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Capital
                                                Act.</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pagado']['sumCapitalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Utilidad --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Utilidad</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pagado']['sumUtilidadFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Último % --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                                %</span>
                                            <span class="font-semibold tabular-nums text-sky-900 dark:text-sky-200">
                                                {{ $totales['pagado']['lastPctFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-3"></td>
                                    <td class="p-3"></td>
                                </tr>

                                {{-- PENDIENTES --}}
                                <tr class="align-middle border-t border-gray-200 dark:border-neutral-700">
                                    <td colspan="5" class="p-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-[11px] font-bold tracking-wide uppercase
                                                bg-amber-100 text-amber-800 border border-amber-200
                                                dark:bg-amber-900/25 dark:text-amber-200 dark:border-amber-800/40">
                                                <span class="inline-flex w-2 h-2 rounded-full bg-amber-500"></span>
                                                Pagos pendientes
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Capital --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">—</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pendiente']['sumCapitalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Utilidad --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Utilidad</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pendiente']['sumUtilidadFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Último % --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                                %</span>
                                            <span
                                                class="font-semibold tabular-nums text-amber-900 dark:text-amber-200">
                                                {{ $totales['pendiente']['lastPctFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-3"></td>
                                    <td class="p-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                {{-- ===================== TABLA BANCO ===================== --}}
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto overflow-y-auto max-h-[75vh]">
                        <table class="w-full text-[13px] min-w-[1200px] align-middle">
                            <thead
                                class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                <tr class="text-center text-[12px] tracking-wide text-gray-600 dark:text-neutral-300">
                                    <th class="p-3 w-[50px]">#</th>
                                    <th class="p-3 w-[120px]">Fecha contable</th>
                                    <th class="p-3 w-[120px]">Fecha pago</th>
                                    <th class="p-3 min-w-[210px] text-left">Descripción</th>
                                    <th class="p-3 w-[160px]">Comprobante</th>
                                    <th class="p-3 w-[180px]">Total</th>
                                    <th class="p-3 w-[180px]">Capital</th>
                                    <th class="p-3 w-[180px]">Interés</th>
                                    <th class="p-3 w-[120px]">% Interés</th>
                                    <th class="p-3 w-[120px]">Estado</th>
                                    <th class="p-3 w-[170px]">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($movimientos as $m)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-neutral-900/50 align-middle font-extralight">

                                        {{-- ID --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['idx'] }}
                                        </td>

                                        {{-- FECHA CONTABLE --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha'] }}
                                        </td>

                                        {{-- FECHA PAGO --}}
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha_pago'] }}
                                        </td>

                                        {{-- DESCRIPCION --}}
                                        <td class="p-2 text-left align-middle">
                                            <div class="text-gray-900 dark:text-neutral-100">
                                                {{ $m['descripcion'] }}
                                            </div>
                                            @if (!empty($m['banco_linea']))
                                                <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                                    {{ $m['banco_linea'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- NRO COMPROBANTE --}}
                                        <td class="p-2 text-center text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['comprobante'] }}
                                        </td>

                                        {{-- TOTAL = CAPITAL + INTERES --}}
                                        <td
                                            class="p-2 text-center font-semibold tabular-nums align-middle
                                            {{ ($m['tipo'] ?? '') === 'CAPITAL_INICIAL'
                                                ? 'text-gray-900 dark:text-neutral-100'
                                                : (($m['estado'] ?? '') === 'PAGADO'
                                                    ? 'text-sky-600 dark:text-sky-400'
                                                    : (($m['estado'] ?? '') === 'PENDIENTE'
                                                        ? 'text-amber-600 dark:text-amber-300'
                                                        : 'text-gray-900 dark:text-neutral-100')) }}">
                                            {{ $m['total'] }}
                                        </td>

                                        {{-- CAPITAL --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['capital'] }}
                                        </td>

                                        {{-- INTERES --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['interes'] }}
                                        </td>

                                        {{-- % INTERES --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['pct_interes'] }}
                                        </td>

                                        {{-- ESTADO --}}
                                        <td class="p-2 text-center align-middle text-[10px] font-bold">
                                            @if (($m['tipo'] ?? '') === 'CAPITAL_INICIAL')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-gray-100 text-gray-700
                                                    dark:bg-neutral-800 dark:text-neutral-200">
                                                    INICIAL
                                                </span>
                                            @elseif (($m['estado'] ?? '') === 'PENDIENTE')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-amber-100 text-amber-700
                                                    dark:bg-amber-900/30 dark:text-amber-300">
                                                    PENDIENTE
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-sky-100 text-sky-700
                                                    dark:bg-sky-900/30 dark:text-sky-300">
                                                    PAGADO
                                                </span>
                                            @endif
                                        </td>

                                        {{-- ACCIONES (BANCO) --}}
                                        <td class="p-2 align-middle">
                                            <div class="flex items-center justify-end gap-2 w-full">

                                                {{-- Ver imagen --}}
                                                @if (!empty($m['tiene_imagen']))
                                                    <button type="button"
                                                        wire:click="verFotoMovimiento({{ $m['id'] }})"
                                                        class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                        border-gray-300 text-gray-700 hover:bg-gray-100
                                                        dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                                        title="Ver imagen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18"
                                                                rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <path d="M21 15l-5-5L5 21" />
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Confirmar --}}
                                                @if (!empty($m['puede_confirmar_banco']))
                                                    <div class="flex items-center">
                                                        <button type="button"
                                                            class="h-7 px-2 cursor-pointer rounded-lg text-xs font-semibold inline-flex items-center gap-2
                                                            bg-green-600 text-white hover:bg-green-700"
                                                            title="Abrir para confirmar / editar"
                                                            wire:click="openConfirmarBanco({{ (int) $m['id'] }})">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M20 6 9 17l-5-5" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif

                                                {{-- Eliminar por fila --}}
                                                @if (!empty($m['puede_eliminar_fila']))
                                                    @if (($m['estado'] ?? '') === 'PAGADO')
                                                        {{-- PAGADO => pide contraseña (modal) --}}
                                                        <button type="button"
                                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                            border-red-300 text-red-700 hover:bg-red-100
                                                            dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                            title="Eliminar (requiere contraseña)"
                                                            wire:click="abrirEliminarFilaModal({{ (int) $m['id'] }})">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M8 6V4h8v2" />
                                                                <path d="M6 6l1 16h10l1-16" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        {{-- PENDIENTE => SweetAlert como estaba --}}
                                                        <div x-data class="flex items-center">
                                                            <button type="button"
                                                                class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                                        border-red-300 text-red-700 hover:bg-red-100
                                                                        dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                                title="Eliminar registro"
                                                                @click.prevent="
                                                                        Swal.fire({
                                                                            title: '¿Eliminar este registro?',
                                                                            text: 'Si está PAGADO se revertirá el banco y el saldo. Si está PENDIENTE solo se borrará.',
                                                                            icon: 'warning',
                                                                            showCancelButton: true,
                                                                            confirmButtonText: 'Sí, eliminar',
                                                                            cancelButtonText: 'Cancelar',
                                                                            reverseButtons: true,
                                                                            confirmButtonColor: '#dc2626',
                                                                            cancelButtonColor: '#6b7280',
                                                                        }).then((r) => { if (r.isConfirmed) { $wire.eliminarMovimientoFila({{ (int) $m['id'] }}); } });
                                                                    ">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path d="M3 6h18" />
                                                                    <path d="M8 6V4h8v2" />
                                                                    <path d="M6 6l1 16h10l1-16" />
                                                                    <path d="M10 11v6" />
                                                                    <path d="M14 11v6" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- Eliminar TODO (Capital inicial) --}}
                                                @if (!empty($m['es_capital_inicial']))
                                                    <div class="flex items-center">
                                                        <button type="button"
                                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                            border-red-400 text-red-800 hover:bg-red-100
                                                            dark:border-red-700 dark:text-red-300 dark:hover:bg-red-500/20"
                                                            title="Eliminar inversión completa"
                                                            wire:click="abrirEliminarTodoModal">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M8 6V4h8v2" />
                                                                <path d="M6 6l1 16h10l1-16" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10"
                                            class="p-6 text-center text-gray-500 dark:text-neutral-400">
                                            No hay movimientos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            {{-- sticky bottom-0 z-10 --}}
                            <tfoot
                                class="sticky bottom-0 z-10 bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                                {{-- PAGADOS --}}
                                <tr class="align-middle">
                                    <td colspan="5" class="p-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-[11px] font-bold tracking-wide uppercase
                                                bg-sky-100 text-sky-800 border border-sky-200
                                                dark:bg-sky-900/25 dark:text-sky-200 dark:border-sky-800/40">
                                                <span class="inline-flex w-2 h-2 rounded-full bg-sky-500"></span>
                                                Pagos realizados
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Total --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Total</span>
                                            <span class="font-semibold tabular-nums text-sky-900 dark:text-sky-200">
                                                {{ $totales['pagado']['sumTotalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Capital --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Capital
                                                Act.</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pagado']['sumCapitalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Interés --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Interés</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pagado']['sumInteresFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Último % --}}
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                                %</span>
                                            <span
                                                class="font-semibold tabular-nums
                                                text-sky-900 dark:text-sky-200">
                                                {{ $totales['pagado']['lastPctFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-3"></td>
                                    <td class="p-3"></td>
                                </tr>

                                {{-- PENDIENTES --}}
                                <tr class="align-middle border-t border-gray-200 dark:border-neutral-700">
                                    <td colspan="5" class="p-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <span
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-[11px] font-bold tracking-wide uppercase
                                                bg-amber-100 text-amber-800 border border-amber-200
                                                dark:bg-amber-900/25 dark:text-amber-200 dark:border-amber-800/40">
                                                <span class="inline-flex w-2 h-2 rounded-full bg-amber-500"></span>
                                                Pagos pendientes
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Total --}}
                                    <td class="p-3 text-right">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Total</span>
                                            <span
                                                class="font-semibold tabular-nums text-amber-900 dark:text-amber-200">
                                                {{ $totales['pendiente']['sumTotalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Capital --}}
                                    <td class="p-3 text-right">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Capital</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pendiente']['sumCapitalFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Interés --}}
                                    <td class="p-3 text-right">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Interés</span>
                                            <span
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $totales['pendiente']['sumInteresFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Último % --}}
                                    <td class="p-3 text-right">
                                        <div class="flex flex-col items-end leading-tight">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                                %</span>
                                            <span
                                                class="font-semibold tabular-nums text-amber-900 dark:text-amber-200">
                                                {{ $totales['pendiente']['lastPctFmt'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-3"></td>
                                    <td class="p-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </x-ui.modal>

    {{-- VISOR FOTO --}}
    <div wire:key="foto-inv-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>

    {{-- MODAL: Eliminar inversión completa (requiere contraseña) --}}
    <x-ui.modal wire:key="delete-all-{{ $openEliminarTodoModal ? 'open' : 'closed' }}" model="openEliminarTodoModal"
        title="Eliminar inversión completa" maxWidth="sm:max-w-xl" onClose="closeEliminarTodoModal">

        <div class="space-y-3">
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                Esta acción eliminará <b>TODO</b> el registro de la inversión y revertirá bancos/operaciones.
                Es una acción irreversible.
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                    Confirme con su contraseña
                </label>

                <flux:input wire:model.defer="deleteAllPassword" name="deleteAllPassword" type="password" required
                    autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />
                @error('deleteAllPassword')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeEliminarTodoModal"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="confirmarEliminarTodo"
                    class="px-4 py-2 rounded-lg cursor-pointer
                       bg-red-600 text-white hover:bg-red-700">
                    Eliminar todo
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- MODAL: Eliminar fila PAGADA (requiere contraseña) --}}
    <x-ui.modal wire:key="delete-row-{{ $openEliminarFilaModal ? 'open' : 'closed' }}" model="openEliminarFilaModal"
        title="Eliminar registro pagado" maxWidth="sm:max-w-xl" onClose="closeEliminarFilaModal">

        <div class="space-y-3">
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                Este registro está en estado <b>PAGADO</b>. Para eliminarlo debes confirmar con tu contraseña.
                Se revertirán los saldos según corresponda.
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                    Confirme con su contraseña
                </label>

                <flux:input wire:model.defer="deleteRowPassword" name="deleteRowPassword" type="password" required
                    autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />
                @error('deleteRowPassword')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeEliminarFilaModal"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="confirmarEliminarFilaConPassword"
                    class="px-4 py-2 rounded-lg cursor-pointer
                       bg-red-600 text-white hover:bg-red-700">
                    Eliminar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- SWEET ALERT --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                Swal.fire({
                    icon: data.icon ?? 'info',
                    title: data.title ?? '',
                    text: data.text ?? '',
                });
            });
        });
    </script>

</div>

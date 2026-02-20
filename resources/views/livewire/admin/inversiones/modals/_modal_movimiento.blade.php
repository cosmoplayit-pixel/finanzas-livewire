{{-- resources/views/livewire/admin/inversiones/modals/_modal_movimiento.blade.php --}}

<div>
    <x-ui.modal wire:key="inversion-movimientos-{{ $openMovimientosModal ? 'open' : 'closed' }}"
        model="openMovimientosModal" title="Movimientos de inversión" maxWidth="sm:max-w-2xl md:max-w-7xl"
        onClose="closeMovimientos">

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
                                        @disabled($bloqueado || !$inversionId || $hayUtilidadPendiente)
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                        bg-emerald-600 text-white hover:opacity-90
                                        disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="{{ $hayUtilidadPendiente ? 'Tienes una utilidad PENDIENTE. Confírmala o elimínala para continuar.' : 'Registrar utilidad' }}">
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
                                        @disabled($bloqueado || $hayPagoBancoPendiente)
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                            bg-indigo-600 text-white hover:opacity-90
                                            disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="{{ $hayPagoBancoPendiente ? 'Tienes un pago BANCO PENDIENTE. Confírmalo o elimínalo para continuar.' : 'Registrar pago banco' }}">
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] min-w-[1200px] align-middle">
                            <thead
                                class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
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
                                        class="text-center hover:bg-gray-50 dark:hover:bg-neutral-900/50 text-center align-middle font-extralight">

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
                                            {{ $m['capital'] }}
                                        </td>

                                        {{-- UTILIDAD --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['utilidad'] }}
                                        </td>

                                        {{-- % UTILIDAD --}}
                                        <td
                                            class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['porcentaje_utilidad'] }}
                                        </td>

                                        {{-- ESTADO --}}
                                        <td class="p-2 align-middle text-[10px] font-bold">
                                            @if ($m['tipo'] === 'PAGO_UTILIDAD')
                                                @if ($m['estado'] === 'PENDIENTE')
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
                                            @elseif ($m['tipo'] === 'INGRESO_CAPITAL')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded
                                                    bg-emerald-100 text-emerald-700
                                                    dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    INGRESO
                                                </span>
                                            @elseif ($m['tipo'] === 'DEVOLUCION_CAPITAL')
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

                                        {{-- ACCIONES --}}
                                        <td class="p-2 align-middle">
                                            <div class="flex items-center justify-center gap-2">

                                                {{-- Confirmar utilidad --}}
                                                @if (!empty($m['puede_confirmar_privado']))
                                                    <div x-data class="flex items-center justify-end">
                                                        <button type="button"
                                                            class="h-7 px-2 cursor-pointer rounded-lg text-xs font-semibold inline-flex items-center gap-2
                                                                bg-green-600 text-white hover:bg-green-700
                                                                disabled:opacity-50 disabled:cursor-not-allowed"
                                                            @disabled($bloqueado) title="Confirmar pago"
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
                                                                }).then((r) => {
                                                                    if (r.isConfirmed) {
                                                                        $wire.confirmarPagoUtilidad({{ (int) $m['id'] }});
                                                                    }
                                                                });
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

                                                {{-- Eliminar último privado --}}
                                                @if ($puedeEliminarUltimoPrivado && $loop->last)
                                                    <div x-data class="flex items-center">
                                                        <button type="button"
                                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                                   border-red-300 text-red-700 hover:bg-red-100
                                                                   dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                            title="Eliminar último registro"
                                                            @click.prevent="
                                                                Swal.fire({
                                                                    title: '¿Eliminar el último registro?',
                                                                    text: 'Se revertirá el capital/banco según corresponda.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, eliminar',
                                                                    cancelButtonText: 'Cancelar',
                                                                    reverseButtons: true,
                                                                    confirmButtonColor: '#dc2626',
                                                                    cancelButtonColor: '#6b7280',
                                                                }).then((r) => {
                                                                    if (r.isConfirmed) {
                                                                        $wire.eliminarUltimoRegistroPrivado();
                                                                    }
                                                                });
                                                            ">
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

                            {{-- FILA DE TOTALES --}}
                            <tfoot
                                class="bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                                <tr class="align-middle ">
                                    <td colspan="5"
                                        class="p-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300 align-middle">
                                        Totales
                                    </td>
                                    <td
                                        class="p-3 text-center font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $totales['sumCapitalFmt'] }}
                                    </td>
                                    <td
                                        class="p-3 text-center font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $totales['sumUtilidadFmt'] }}
                                    </td>
                                    <td
                                        class="p-3 text-center font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $ultimaUtilidadPctPagadaFmt }}
                                    </td>
                                    <td class="p-3 align-middle"></td>
                                    <td class="p-3 align-middle"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                {{-- ===================== TABLA BANCO ===================== --}}
                <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] min-w-[1200px] align-middle">
                            <thead
                                class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                <tr class="text-center text-[12px] tracking-wide text-gray-600 dark:text-neutral-300">
                                    <th class="p-3 w-[50px]">#</th>
                                    <th class="p-3 w-[120px]">Fecha contable</th>
                                    <th class="p-3 w-[120px]">Fecha pago</th>
                                    <th class="p-3 min-w-[280px] text-left">Descripción</th>
                                    <th class="p-3 w-[160px]">Comprobante</th>
                                    <th class="p-3 w-[150px]">Total</th>
                                    <th class="p-3 w-[150px]">Capital</th>
                                    <th class="p-3 w-[150px]">Interés</th>
                                    <th class="p-3 w-[120px]">% Interés</th>
                                    <th class="p-3 w-[120px]">Estado</th>
                                    <th class="p-3 w-[170px]">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($movimientos as $m)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-neutral-900/50 text-center align-middle font-extralight">
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['idx'] }}
                                        </td>

                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha'] }}
                                        </td>

                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['fecha_pago'] }}
                                        </td>

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

                                        <td class="p-2 text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['comprobante'] }}
                                        </td>

                                        <td
                                            class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['total'] }}
                                        </td>

                                        <td
                                            class="p-2 text-right tabular-nums align-middle
                                            {{ !empty($m['capital_is_negative']) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-neutral-100' }}">
                                            {{ $m['capital'] }}
                                        </td>

                                        <td
                                            class="p-2 text-right tabular-nums align-middle
                                            {{ !empty($m['interes_is_negative']) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-neutral-100' }}">
                                            {{ $m['interes'] }}
                                        </td>

                                        <td
                                            class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                            {{ $m['pct_interes'] }}
                                        </td>

                                        <td class="p-2 align-middle text-[10px] font-bold">
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
                                        </td>

                                        <td class="p-2 align-middle">
                                            <div class="flex items-center justify-center gap-2">

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
                                                @else
                                                    <span
                                                        class="w-8 h-8 inline-flex items-center justify-center text-xs text-gray-400 dark:text-neutral-500">—</span>
                                                @endif

                                                {{-- Confirmar BANCO (si pendiente) --}}
                                                @if (!empty($m['puede_confirmar_banco']))
                                                    <div x-data class="flex items-center">
                                                        <button type="button"
                                                            class="h-7 px-2 cursor-pointer rounded-lg text-xs font-semibold inline-flex items-center gap-2
                                                                bg-green-600 text-white hover:bg-green-700"
                                                            title="Confirmar pago banco"
                                                            @click.prevent="
                                                                Swal.fire({
                                                                    title: '¿Confirmar pago banco?',
                                                                    text: 'Esto debitará el banco y bajará el saldo de la inversión.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, confirmar',
                                                                    cancelButtonText: 'Cancelar',
                                                                    reverseButtons: true,
                                                                    confirmButtonColor: '#16a34a',
                                                                    cancelButtonColor: '#6b7280',
                                                                }).then((r) => {
                                                                    if (r.isConfirmed) {
                                                                        $wire.confirmarPagoBanco({{ (int) $m['id'] }});
                                                                    }
                                                                });
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

                                                {{-- Eliminar último banco --}}
                                                @if ($puedeEliminarUltimo && $loop->last)
                                                    <div x-data class="flex items-center">
                                                        <button type="button"
                                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                                   border-red-300 text-red-700 hover:bg-red-100
                                                                   dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                            title="Eliminar último registro"
                                                            @click.prevent="
                                                                Swal.fire({
                                                                    title: '¿Eliminar el último registro?',
                                                                    text: 'Si está PAGADO se revertirá el banco y el saldo de la inversión. Si está PENDIENTE solo se borrará.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, eliminar',
                                                                    cancelButtonText: 'Cancelar',
                                                                    reverseButtons: true,
                                                                    confirmButtonColor: '#dc2626',
                                                                    cancelButtonColor: '#6b7280',
                                                                }).then((r) => {
                                                                    if (r.isConfirmed) {
                                                                        $wire.eliminarUltimoPagoBanco();
                                                                    }
                                                                });
                                                            ">
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

                            <tfoot
                                class="bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                                <tr class="align-middle">
                                    <td colspan="5"
                                        class="p-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300 align-middle">
                                        Totales
                                    </td>

                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $totales['sumTotalFmt'] }}
                                    </td>

                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $saldoDeudaFmt }}
                                    </td>

                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                        {{ $totales['sumInteresFmt'] }}
                                    </td>

                                    {{-- % interés --}}
                                    <td class="p-3 align-middle"></td>
                                    {{-- estado --}}
                                    <td class="p-3 align-middle"></td>
                                    {{-- acciones --}}
                                    <td class="p-3 align-middle"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeMovimientos"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                        border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                        hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- ===================== VISOR FOTO ===================== --}}
    <div wire:key="foto-inv-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>
</div>

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

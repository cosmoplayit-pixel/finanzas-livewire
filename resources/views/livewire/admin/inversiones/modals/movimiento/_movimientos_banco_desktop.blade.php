{{-- resources/views/livewire/admin/inversiones/modals/_movimientos_banco_desktop.blade.php --}}

<div class="space-y-3">

    {{-- ===================== HEADER BANCO (DESKTOP) ===================== --}}
    <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
        <div class="px-4 py-3 border-b dark:border-neutral-700">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                {{-- IZQUIERDA --}}
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        {{ $inversionNombre }}
                    </div>

                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">

                        <span class="inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <circle cx="9.5" cy="9.5" r="1" />
                                <circle cx="14.5" cy="14.5" r="1" />
                            </svg>
                            <span>% Mensual: {{ $tasaAmortizacionFmt }}</span>
                        </span>

                        <span class="text-gray-300 dark:text-neutral-600">•</span>

                        <span class="inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 3" />
                            </svg>
                            <span>{{ $plazoMeses }} meses</span>
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
                                <path d="M8 14h.01" />
                                <path d="M12 14h.01" />
                                <path d="M16 14h.01" />
                            </svg>
                            <span>Día pago: {{ $diaPago }}</span>
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

                {{-- DERECHA: ACCIONES BANCO --}}
                <div class="shrink-0 w-full sm:w-auto">
                    <div class="w-full sm:w-auto flex flex-wrap justify-end gap-2">
                        <button type="button"
                            wire:click="$dispatch('openPagarBanco', { inversionId: {{ $inversionId }} })"
                            @disabled($bloqueado)
                            class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                   bg-indigo-600 text-white hover:opacity-90
                                   disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Registrar pago banco">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-white/15">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
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


    {{-- ===================== TABLA BANCO (DESKTOP) ===================== --}}
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/50 align-middle font-extralight">

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

                            <td class="p-2 text-center text-gray-900 dark:text-neutral-100 align-middle">
                                {{ $m['comprobante'] }}
                            </td>

                            {{-- TOTAL --}}
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

                            <td class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                {{ $m['capital'] }}
                            </td>

                            <td class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                {{ $m['interes'] }}
                            </td>

                            <td class="p-2 text-center tabular-nums text-gray-900 dark:text-neutral-100 align-middle">
                                {{ $m['pct_interes'] }}
                            </td>

                            <td class="p-2 text-center align-middle text-[10px] font-bold">
                                @if (($m['tipo'] ?? '') === 'CAPITAL_INICIAL')
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-200">
                                        INICIAL
                                    </span>
                                @elseif (($m['estado'] ?? '') === 'PENDIENTE')
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                        PENDIENTE
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                                        PAGADO
                                    </span>
                                @endif
                            </td>

                            {{-- ACCIONES (BANCO) --}}
                            <td class="p-2 align-middle">
                                <div class="flex items-center justify-end gap-2 w-full">

                                    {{-- Ver imagen --}}
                                    @if (!empty($m['tiene_imagen']))
                                        <button type="button" wire:click="verFotoMovimiento({{ $m['id'] }})"
                                            class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                   border-gray-300 text-gray-700 hover:bg-gray-100
                                                   dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                            title="Ver imagen">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2" />
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
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 6 9 17l-5-5" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Eliminar por fila --}}
                                    @if (!empty($m['puede_eliminar_fila']))
                                        @if (($m['estado'] ?? '') === 'PAGADO')
                                            <button type="button"
                                                class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                       border-red-300 text-red-700 hover:bg-red-100
                                                       dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
                                                title="Eliminar (requiere contraseña)"
                                                wire:click="abrirEliminarFilaModal({{ (int) $m['id'] }})">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                            <td colspan="11" class="p-6 text-center text-gray-500 dark:text-neutral-400">
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
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $totales['pagado']['sumCapitalFmt'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Interés --}}
                        <td class="p-3 text-center">
                            <div class="flex flex-col items-end leading-tight">
                                <span
                                    class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Interés</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
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

                        {{-- Total --}}
                        <td class="p-3 text-right">
                            <div class="flex flex-col items-end leading-tight">
                                <span
                                    class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Total</span>
                                <span class="font-semibold tabular-nums text-amber-900 dark:text-amber-200">
                                    {{ $totales['pendiente']['sumTotalFmt'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Capital --}}
                        <td class="p-3 text-right">
                            <div class="flex flex-col items-end leading-tight">
                                <span
                                    class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Capital</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $totales['pendiente']['sumCapitalFmt'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Interés --}}
                        <td class="p-3 text-right">
                            <div class="flex flex-col items-end leading-tight">
                                <span
                                    class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Interés</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
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
                                <span class="font-semibold tabular-nums text-amber-900 dark:text-amber-200">
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

</div>

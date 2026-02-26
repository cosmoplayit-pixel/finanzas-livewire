{{-- resources/views/livewire/admin/inversiones/modals/_movimientos_privado_mobile.blade.php --}}

@php
    $tPag = $totales['pagado'] ?? [];
    $tPen = $totales['pendiente'] ?? [];
@endphp

<div class="space-y-3">

    {{-- ===================== HEADER PRIVADO (MOBILE) ===================== --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/30">

        {{-- TOP: TITULAR + BOTÓN (MISMA FILA) --}}
        <div class="px-3 py-2 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center justify-between gap-3">

                {{-- Titular --}}
                <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        Titular: {{ $inversionNombre }}
                    </div>
                </div>

                {{-- Botón Pagar --}}
                <div class="shrink-0">
                    <button type="button"
                        wire:click="$dispatch('openPagarUtilidad', { inversionId: {{ $inversionId }} })"
                        @disabled($bloqueado || !$inversionId)
                        class="h-8 px-3 rounded-xl text-[13px] font-semibold inline-flex items-center gap-2
                           bg-emerald-600 text-white hover:opacity-90
                           disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Registrar pago">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-lg bg-white/15">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </span>
                        <span>Pagar</span>
                    </button>
                </div>

            </div>


        </div>

        {{-- RESUMEN --}}
        <div class="px-3 py-2 space-y-2">

            <div class="flex flex-wrap items-center gap-2 text-[12px] text-gray-700 dark:text-neutral-200">

                {{-- Código --}}
                <span
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                         bg-gray-50 border border-gray-200 text-gray-700
                         dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 7H4" />
                        <path d="M20 11H4" />
                        <path d="M20 15H4" />
                        <path d="M20 19H4" />
                        <path d="M8 3v4" />
                        <path d="M16 3v4" />
                    </svg>
                    <span class="font-semibold tabular-nums">{{ $inversionCodigo }}</span>
                </span>

                {{-- Tipo --}}
                <span
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                         bg-gray-50 border border-gray-200 text-gray-700
                         dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v18" />
                        <path d="M3 12h18" />
                    </svg>
                    <span class="font-semibold">{{ $inversionTipo }}</span>
                </span>

                {{-- Capital actual --}}
                <span
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                         bg-emerald-50 border border-emerald-200 text-emerald-800
                         dark:bg-emerald-900/20 dark:border-emerald-800/40 dark:text-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1v22" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                    <span class="font-semibold tabular-nums">{{ $capitalActualFmt }}</span>
                </span>

                {{-- Fechas --}}
                <span
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                         bg-gray-50 border border-gray-200 text-gray-700
                         dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4" />
                        <path d="M8 2v4" />
                        <path d="M3 10h18" />
                    </svg>
                    <span class="font-semibold tabular-nums">{{ $fechaInicioFmt }} - {{ $fechaVencFmt }}</span>
                </span>

            </div>
        </div>
    </div>

    {{-- ===================== MOVIMIENTOS (MOBILE = CARDS) ===================== --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/30">

        {{-- Header listado --}}
        <div class="px-3 py-2 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div class="text-[13px] font-semibold text-gray-800 dark:text-neutral-100">
                    Movimientos
                </div>

                <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                    {{ count($movimientos) }} registros
                </div>
            </div>
        </div>

        {{-- Listado (MOBILE = CARDS) --}}
        <div class="px-3 py-2 space-y-2">
            @forelse($movimientos as $m)
                @php
                    $tipo = $m['tipo'] ?? '';
                    $estado = $m['estado'] ?? '';
                    $isPend = $estado === 'PENDIENTE';
                    $isPag = $estado === 'PAGADO';

                    // Texto badge
                    $badgeText = $estado ?: '—';
                    if ($tipo === 'CAPITAL_INICIAL') {
                        $badgeText = 'INICIAL';
                    } elseif ($tipo === 'PAGO_UTILIDAD') {
                        $badgeText = $isPend ? 'PENDIENTE' : 'PAGADO';
                    } elseif ($tipo === 'INGRESO_CAPITAL') {
                        $badgeText = 'INGRESO';
                    } elseif ($tipo === 'DEVOLUCION_CAPITAL') {
                        $badgeText = 'DEVOLUCIÓN';
                    }

                    // Color badge
                    $badgeBase = 'inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold';
                    $badge = $badgeBase . ' bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-200';
                    if ($tipo === 'PAGO_UTILIDAD') {
                        $badge =
                            $badgeBase .
                            ($isPend
                                ? ' bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                : ' bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300');
                    } elseif ($tipo === 'INGRESO_CAPITAL') {
                        $badge =
                            $badgeBase .
                            ' bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
                    } elseif ($tipo === 'DEVOLUCION_CAPITAL') {
                        $badge = $badgeBase . ' bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                    }

                    // Datos
                    $comp = $m['comprobante'] ?? '—';
                    $capital = $m['capital'] ?? '—';
                    $utilidad = $m['utilidad'] ?? '—';
                    $pct = $m['porcentaje_utilidad'] ?? '—';
                    $fecha = $m['fecha'] ?? ($m['fecha_inicio'] ?? '—');
                    $fechaPago = $m['fecha_pago'] ?? '—';

                    // Acciones
                    $canConfirm = !empty($m['puede_confirmar_privado']);
                    $hasImg = !empty($m['tiene_imagen']);
                    $canDelete = !empty($m['puede_eliminar_fila']);
                    $isCapitalInicial = $tipo === 'CAPITAL_INICIAL';
                    $deleteBlocked = !$canDelete && in_array($tipo, ['INGRESO_CAPITAL', 'DEVOLUCION_CAPITAL'], true);

                    // ===== SOLO FONDO (degradado tenue) según tipo/estado =====
                    // Default: neutro tenue
                    $bgGrad =
                        'bg-gradient-to-b from-white to-gray-50/50 dark:from-neutral-900/40 dark:to-neutral-900/10';

                    if ($tipo === 'CAPITAL_INICIAL') {
                        $bgGrad =
                            'bg-gradient-to-b from-gray-50/80 to-white dark:from-neutral-900/55 dark:to-neutral-900/15';
                    } elseif ($tipo === 'PAGO_UTILIDAD') {
                        if ($isPend) {
                            // ámbar tenue
                            $bgGrad =
                                'bg-gradient-to-b from-amber-50/80 to-white ' .
                                'dark:from-amber-900/15 dark:to-neutral-900/10';
                        } else {
                            // sky tenue
                            $bgGrad =
                                'bg-gradient-to-b from-sky-50/80 to-white ' .
                                'dark:from-sky-900/15 dark:to-neutral-900/10';
                        }
                    } elseif ($tipo === 'INGRESO_CAPITAL') {
                        // emerald tenue
                        $bgGrad =
                            'bg-gradient-to-b from-emerald-50/80 to-white ' .
                            'dark:from-emerald-900/15 dark:to-neutral-900/10';
                    } elseif ($tipo === 'DEVOLUCION_CAPITAL') {
                        // red tenue
                        $bgGrad =
                            'bg-gradient-to-b from-red-50/80 to-white ' . 'dark:from-red-900/15 dark:to-neutral-900/10';
                    }
                @endphp

                <div class="rounded-2xl border border-gray-200 p-3 dark:border-neutral-700 {{ $bgGrad }}">

                    {{-- HEADER (MOBILE CARD) --}}
                    <div class="flex items-start justify-between gap-3">
                        {{-- IZQUIERDA: título --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-[14px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                #{{ $m['idx'] }} {{ $m['descripcion'] }}
                            </div>

                            @if (!empty($m['banco_linea']))
                                <div class="mt-1 text-[12px] text-gray-500 dark:text-neutral-400 truncate">
                                    {{ $m['banco_linea'] }}
                                </div>
                            @endif
                        </div>

                        {{-- DERECHA: BADGE --}}
                        <div class="shrink-0">
                            <span class="{{ $badge }}">{{ $badgeText }}</span>
                        </div>
                    </div>

                    {{-- BLOQUE 2 FILAS --}}
                    <div
                        class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/60">

                        {{-- FILA 1: Comprobante | Capital | Utilidad --}}
                        <div class="grid grid-cols-3 border-b border-gray-200 dark:border-neutral-700">
                            <div class="p-3 min-w-0">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Comprobante
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $comp }}
                                </div>
                            </div>

                            <div class="p-3 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-center">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Capital
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $capital }}
                                </div>
                            </div>

                            <div class="p-3 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-right">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Utilidad
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-extrabold tabular-nums
                            {{ $tipo === 'PAGO_UTILIDAD'
                                ? ($isPend
                                    ? 'text-amber-700 dark:text-amber-300'
                                    : 'text-sky-700 dark:text-sky-300')
                                : 'text-gray-900 dark:text-neutral-100' }} truncate">
                                    {{ $utilidad }}
                                </div>
                            </div>
                        </div>

                        {{-- FILA 2: Fecha | Fecha pago | % Utilidad --}}
                        <div class="grid grid-cols-3">
                            <div class="p-3 min-w-0">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Fecha
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $fecha }}
                                </div>
                            </div>

                            <div class="p-3 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-center">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Fecha pago
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $fechaPago }}
                                </div>
                            </div>

                            <div class="p-3 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-right">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    % Utilidad
                                </div>
                                <div
                                    class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $pct }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACCIONES (siempre visibles, bloqueadas si no aplica) --}}
                    <div class="mt-3 grid grid-cols-3 gap-2">

                        {{-- Confirmar --}}
                        @if ($canConfirm)
                            <div x-data>
                                <button type="button"
                                    class="h-8 w-full rounded-lg px-3 text-[13px] font-semibold inline-flex items-center justify-center gap-2
                                   bg-emerald-600 text-white hover:bg-emerald-700"
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <button type="button" disabled
                                class="h-8 w-full rounded-lg px-3 text-[13px] font-semibold inline-flex items-center justify-center gap-2
                               bg-emerald-600 text-white opacity-40 cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            </button>
                        @endif

                        {{-- Imagen --}}
                        @if ($hasImg)
                            <button type="button" wire:click="verFotoMovimiento({{ $m['id'] }})"
                                class="h-8 w-full rounded-lg inline-flex items-center justify-center border
                               border-gray-200 bg-white text-gray-700 hover:bg-gray-50
                               dark:border-neutral-700 dark:bg-neutral-900/40 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                title="Ver imagen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        @else
                            <button type="button" disabled
                                class="h-8 w-full rounded-lg inline-flex items-center justify-center border
                               border-gray-200 bg-white text-gray-700 opacity-40 cursor-not-allowed
                               dark:border-neutral-700 dark:bg-neutral-900/40 dark:text-neutral-200"
                                title="Sin imagen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        @endif

                        {{-- Eliminar --}}
                        @if ($isCapitalInicial)
                            <button type="button"
                                class="h-8 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                               border-red-300 text-red-700 bg-white hover:bg-red-50
                               dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                title="Eliminar inversión completa" wire:click="abrirEliminarTodoModal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M6 6l1 16h10l1-16" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                        @elseif ($canDelete)
                            @if ($isPag)
                                <button type="button" wire:click="abrirEliminarFilaModal({{ (int) $m['id'] }})"
                                    class="h-8 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                   border-red-300 text-red-700 bg-white hover:bg-red-50
                                   dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                    title="Eliminar (requiere contraseña)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="M6 6l1 16h10l1-16" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                    </svg>
                                </button>
                            @else
                                <div x-data class="w-full">
                                    <button type="button"
                                        class="h-8 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                       border-red-300 text-red-700 bg-white hover:bg-red-50
                                       dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                        title="Eliminar"
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
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
                        @else
                            <button type="button" disabled
                                class="h-8 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                               border-red-200 text-red-700 bg-white opacity-40 cursor-not-allowed
                               dark:border-red-900/40 dark:bg-neutral-900/40 dark:text-red-300"
                                title="{{ $deleteBlocked ? 'Solo se permite eliminar si es el último registro.' : 'No disponible' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M6 6l1 16h10l1-16" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                        @endif
                    </div>

                </div>
            @empty
                <div class="p-6 text-center text-gray-500 dark:text-neutral-400">
                    No hay movimientos registrados.
                </div>
            @endforelse
        </div>

        {{-- ===================== FOOTER TOTALES (MOBILE) ===================== --}}
        <div class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900">
            <div class="px-3 py-2  space-y-2">

                {{-- Pagados --}}
                <div
                    class="rounded-2xl border border-sky-200 bg-white p-3 dark:border-sky-800/40 dark:bg-neutral-900/40">
                    <div class="flex items-center justify-between">
                        <div
                            class="inline-flex items-center gap-2 text-[11px] font-bold tracking-wide uppercase text-sky-800 dark:text-sky-200">
                            <span class="inline-flex w-2 h-2 rounded-full bg-sky-500"></span>
                            Pagos realizados
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-2 text-[12px]">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Capital</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPag['sumCapitalFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Utilidad</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPag['sumUtilidadFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                %</div>
                            <div class="mt-1 font-semibold tabular-nums text-sky-900 dark:text-sky-200">
                                {{ $tPag['lastPctFmt'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pendientes --}}
                <div
                    class="rounded-2xl border border-amber-200 bg-white p-3 dark:border-amber-800/40 dark:bg-neutral-900/40">
                    <div class="flex items-center justify-between">
                        <div
                            class="inline-flex items-center gap-2 text-[11px] font-bold tracking-wide uppercase text-amber-800 dark:text-amber-200">
                            <span class="inline-flex w-2 h-2 rounded-full bg-amber-500"></span>
                            Pagos pendientes
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-2 text-[12px]">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Capital</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPen['sumCapitalFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Utilidad</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPen['sumUtilidadFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Último
                                %</div>
                            <div class="mt-1 font-semibold tabular-nums text-amber-900 dark:text-amber-200">
                                {{ $tPen['lastPctFmt'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

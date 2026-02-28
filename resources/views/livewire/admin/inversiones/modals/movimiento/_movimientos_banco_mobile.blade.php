{{-- resources/views/livewire/admin/inversiones/modals/_movimientos_banco_mobile.blade.php --}}

@php
    $tPag = $totales['pagado'] ?? [];
    $tPen = $totales['pendiente'] ?? [];
@endphp

<div class="space-y-3">

    {{-- ===================== HEADER BANCO (MOBILE) ===================== --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/30">

        {{-- TOP: TITULAR + BOTÓN (MISMA FILA) --}}
        <div class="px-3 py-2 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center justify-between gap-3">

                {{-- Titular --}}
                <div class="min-w-0">
                    <div class="text-[12px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        Titular: {{ $inversionNombre }}
                    </div>
                </div>

                {{-- Botón Pagar --}}
                @can('inversiones.register_pay')
                <div class="shrink-0">
                    <button type="button" wire:click="$dispatch('openPagarBanco', { inversionId: {{ $inversionId }} })"
                        @disabled($bloqueado || !$inversionId)
                        class="h-6 px-2 rounded-lg text-[11px] font-semibold inline-flex items-center gap-2
                           bg-indigo-600 text-white hover:opacity-90
                           disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Registrar pago banco">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-lg bg-white/15">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 10h18" />
                                <path d="M5 10V20" />
                                <path d="M19 10V20" />
                                <path d="M9 10V20" />
                                <path d="M15 10V20" />
                                <path d="M2 20h20" />
                                <path d="M12 2 2 7h20L12 2z" />
                            </svg>
                        </span>
                        <span>Pagar</span>
                    </button>
                </div>
                @endcan

            </div>
        </div>

        {{-- RESUMEN --}}
        <div class="px-1.5 py-2 space-y-2">
            <div class="flex flex-wrap items-center gap-2 text-[10px] text-gray-700 dark:text-neutral-200">

                {{-- Código --}}
                <span
                    class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg
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
                {{-- Saldo deuda --}}
                <span
                    class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg
                         bg-indigo-50 border border-indigo-200 text-indigo-800
                         dark:bg-indigo-900/20 dark:border-indigo-800/40 dark:text-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10h18" />
                        <path d="M5 10V20" />
                        <path d="M19 10V20" />
                        <path d="M2 20h20" />
                        <path d="M12 2 2 7h20L12 2z" />
                    </svg>
                    <span class="font-semibold tabular-nums">{{ $saldoDeudaFmt }}</span>
                </span>

                {{-- Plazo --}}
                <span
                    class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg
                         bg-gray-50 border border-gray-200 text-gray-700
                         dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 3" />
                    </svg>
                    <span><span class="font-semibold tabular-nums">{{ $plazoMeses }}</span> meses</span>
                </span>

                {{-- Día pago --}}
                <span
                    class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg
                         bg-gray-50 border border-gray-200 text-gray-700
                         dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4" />
                        <path d="M8 2v4" />
                        <path d="M3 10h18" />
                        <path d="M8 14h.01" />
                        <path d="M12 14h.01" />
                        <path d="M16 14h.01" />
                    </svg>
                    <span>Día: <span class="font-semibold tabular-nums">{{ $diaPago }}</span></span>
                </span>

                {{-- Fechas --}}
                <span
                    class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg
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
                    if ($tipo === 'CAPITAL_INICIAL' || !empty($m['es_capital_inicial'])) {
                        $badgeText = 'INICIAL';
                    } else {
                        $badgeText = $isPend ? 'PENDIENTE' : 'PAGADO';
                    }

                    // Color badge
                    $badgeBase = 'inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold';
                    $badge = $badgeBase . ' bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-200';
                    if ($tipo !== 'CAPITAL_INICIAL' && empty($m['es_capital_inicial'])) {
                        $badge =
                            $badgeBase .
                            ($isPend
                                ? ' bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                : ' bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300');
                    }

                    // Datos
                    $comp = $m['comprobante'] ?? '—';
                    $capital = $m['capital'] ?? '—';
                    $interes = $m['interes'] ?? '—';
                    $pct = $m['pct_interes'] ?? ($m['porcentaje_interes'] ?? '—');
                    $fecha = $m['fecha'] ?? ($m['fecha_inicio'] ?? '—');
                    $fechaPago = $m['fecha_pago'] ?? '—';

                    // Acciones
                    $canConfirm = !empty($m['puede_confirmar_banco']);
                    $hasImg = !empty($m['tiene_imagen']);
                    $canDelete = !empty($m['puede_eliminar_fila']);
                    $isCapitalInicial = $tipo === 'CAPITAL_INICIAL' || !empty($m['es_capital_inicial']);

                    // ===== SOLO FONDO (degradado tenue) según estado/tipo =====
                    $bgGrad =
                        'bg-gradient-to-b from-white to-gray-50/50 dark:from-neutral-900/40 dark:to-neutral-900/10';

                    if ($isCapitalInicial) {
                        $bgGrad =
                            'bg-gradient-to-b from-gray-50/80 to-white dark:from-neutral-900/55 dark:to-neutral-900/15';
                    } else {
                        if ($isPend) {
                            $bgGrad =
                                'bg-gradient-to-b from-amber-50/80 to-white ' .
                                'dark:from-amber-900/15 dark:to-neutral-900/10';
                        } else {
                            $bgGrad =
                                'bg-gradient-to-b from-sky-50/80 to-white ' .
                                'dark:from-sky-900/15 dark:to-neutral-900/10';
                        }
                    }
                @endphp

                <div class="rounded-2xl border border-gray-200 p-3 dark:border-neutral-700 {{ $bgGrad }}">

                    {{-- HEADER (MOBILE CARD) --}}
                    <div class="flex items-start justify-between gap-3">
                        {{-- IZQUIERDA: título --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-[12px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                #{{ $m['idx'] }} {{ $m['descripcion'] ?? 'Movimiento' }}
                            </div>

                            @if (!empty($m['banco_linea']))
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400 truncate">
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
                        class="mt-3 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/60">

                        {{-- FILA 1: Comprobante | Capital | Interés --}}
                        <div class="grid grid-cols-3 border-b border-gray-200 dark:border-neutral-700">
                            <div class="p-1.5 min-w-0">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Comprobante
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $comp }}
                                </div>
                            </div>

                            <div
                                class="p-1.5 text-center min-w-0 border-l border-gray-200 dark:border-neutral-700 text-center">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Capital
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $capital }}
                                </div>
                            </div>

                            <div class="p-1.5 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-right">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Interés
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-extrabold tabular-nums
                                    {{ $isCapitalInicial
                                        ? 'text-gray-900 dark:text-neutral-100'
                                        : ($isPend
                                            ? 'text-amber-700 dark:text-amber-300'
                                            : 'text-sky-700 dark:text-sky-300') }} truncate">
                                    {{ $interes }}
                                </div>
                            </div>
                        </div>

                        {{-- FILA 2: Fecha | Fecha pago | % --}}
                        <div class="grid grid-cols-3">
                            <div class="p-1.5 min-w-0">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Fecha
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $fecha }}
                                </div>
                            </div>

                            <div class="p-1.5 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-center">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    Fecha pago
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $fechaPago }}
                                </div>
                            </div>

                            <div class="p-1.5 min-w-0 border-l border-gray-200 dark:border-neutral-700 text-right">
                                <div
                                    class="text-[9px] font-bold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                    %
                                </div>
                                <div
                                    class="mt-1 text-[11px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $pct }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACCIONES (siempre visibles, bloqueadas si no aplica) --}}
                    @canany(['inversiones.confirm_pay', 'inversiones.delete'])
                    <div class="mt-3 grid grid-cols-3 gap-2">

                        @can('inversiones.confirm_pay')
                        {{-- Confirmar --}}
                            @if ($canConfirm)
                                <button type="button"
                                    class="h-6 w-full rounded-lg px-3 text-[13px] font-semibold inline-flex items-center justify-center gap-2
                                        bg-emerald-600 text-white hover:bg-emerald-700"
                                    title="Abrir para confirmar / editar"
                                    wire:click="openConfirmarBanco({{ (int) $m['id'] }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </button>
                            @else
                                <button type="button" disabled
                                    class="h-6 w-full rounded-lg px-3 text-[13px] font-semibold inline-flex items-center justify-center gap-2
                                        bg-emerald-600 text-white opacity-40 cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </button>
                            @endif
                        @endcan
                     
                        {{-- Imagen --}}
                        @if ($hasImg)
                            <button type="button" wire:click="verFotoMovimiento({{ (int) $m['id'] }})"
                                class="h-6 w-full rounded-lg inline-flex items-center justify-center border
                                       border-gray-200 bg-white text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:bg-neutral-900/40 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                title="Ver imagen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        @else
                            <button type="button" disabled
                                class="h-6 w-full rounded-lg inline-flex items-center justify-center border
                                       border-gray-200 bg-white text-gray-700 opacity-40 cursor-not-allowed
                                       dark:border-neutral-700 dark:bg-neutral-900/40 dark:text-neutral-200"
                                title="Sin imagen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        @endif
                        
                        @can('inversiones.delete')
                            {{-- Eliminar --}}
                            @if ($isCapitalInicial)
                                <button type="button"
                                    class="h-6 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                        border-red-300 text-red-700 bg-white hover:bg-red-50
                                        dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                    title="Eliminar inversión completa" wire:click="abrirEliminarTodoModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
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
                                    <button type="button"
                                        class="h-6 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                            border-red-300 text-red-700 bg-white hover:bg-red-50
                                            dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                        title="Eliminar (requiere contraseña)"
                                        wire:click="abrirEliminarFilaModal({{ (int) $m['id'] }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
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
                                            class="h-6 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                                border-red-300 text-red-700 bg-white hover:bg-red-50
                                                dark:border-red-700 dark:bg-neutral-900/40 dark:text-red-300 dark:hover:bg-red-500/15"
                                            title="Eliminar"
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
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
                                    class="h-6 w-full rounded-lg inline-flex items-center justify-center gap-2 border
                                        border-red-200 text-red-700 bg-white opacity-40 cursor-not-allowed
                                        dark:border-red-900/40 dark:bg-neutral-900/40 dark:text-red-300"
                                    title="No disponible">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
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
                        @endcan
                    </div>
                    @endcanany

                </div>
            @empty
                <div class="p-6 text-center text-gray-500 dark:text-neutral-400">
                    No hay movimientos registrados.
                </div>
            @endforelse
        </div>

        {{-- ===================== FOOTER TOTALES (MOBILE) ===================== --}}
        <div class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900">
            <div class="px-3 py-2 space-y-2">

                {{-- Pagados --}}
                <div
                    class="rounded-2xl border border-sky-200 bg-white p-3 dark:border-sky-800/40 dark:bg-neutral-900/40">
                    <div class="flex items-center justify-between">
                        <div
                            class="inline-flex items-center gap-2 text-[9px] font-bold tracking-wide uppercase text-sky-800 dark:text-sky-200">
                            <span class="inline-flex w-2 h-2 rounded-full bg-sky-500"></span>
                            Pagos realizados
                        </div>
                        <div class="text-[11px] font-bold tabular-nums text-sky-900 dark:text-sky-200">
                            {{ $tPag['sumTotalFmt'] ?? '0' }}
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Capital
                            </div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPag['sumCapitalFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Interés
                            </div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPag['sumInteresFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Último %
                            </div>
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
                            class="inline-flex items-center gap-2 text-[9px] font-bold tracking-wide uppercase text-amber-800 dark:text-amber-200">
                            <span class="inline-flex w-2 h-2 rounded-full bg-amber-500"></span>
                            Pagos pendientes
                        </div>
                        <div class="text-[11px] font-bold tabular-nums text-amber-900 dark:text-amber-200">
                            {{ $tPen['sumTotalFmt'] ?? '0' }}
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Capital
                            </div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPen['sumCapitalFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Interés
                            </div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPen['sumInteresFmt'] ?? '0' }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-neutral-900/60">
                            <div class="text-[9px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Último %
                            </div>
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

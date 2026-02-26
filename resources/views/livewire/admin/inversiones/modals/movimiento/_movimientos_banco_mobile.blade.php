{{-- resources/views/livewire/admin/inversiones/modals/_movimientos_banco_mobile.blade.php --}}

@php
    $tPag = $totales['pagado'] ?? [];
    $tPen = $totales['pendiente'] ?? [];
@endphp

<div class="space-y-3">

    {{-- ===================== HEADER BANCO (MOBILE) ===================== --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/30">

        <div class="p-4 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        {{ $inversionNombre }}
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[12px] text-gray-600 dark:text-neutral-300">

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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 3v18" />
                                <path d="M3 12h18" />
                            </svg>
                            <span class="font-semibold">{{ $inversionTipo }}</span>
                        </span>

                        {{-- Saldo deuda --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                            bg-indigo-50 border border-indigo-200 text-indigo-800
                            dark:bg-indigo-900/20 dark:border-indigo-800/40 dark:text-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M3 10h18" />
                                <path d="M5 10V20" />
                                <path d="M19 10V20" />
                                <path d="M2 20h20" />
                                <path d="M12 2 2 7h20L12 2z" />
                            </svg>
                            <span class="font-semibold tabular-nums">{{ $saldoDeudaFmt }}</span>
                        </span>

                        {{-- % mensual --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                            bg-gray-50 border border-gray-200 text-gray-700
                            dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <circle cx="9.5" cy="9.5" r="1" />
                                <circle cx="14.5" cy="14.5" r="1" />
                            </svg>
                            <span>%: <span class="font-semibold tabular-nums">{{ $tasaAmortizacionFmt }}</span></span>
                        </span>

                        {{-- Plazo --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                            bg-gray-50 border border-gray-200 text-gray-700
                            dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 3" />
                            </svg>
                            <span><span class="font-semibold tabular-nums">{{ $plazoMeses }}</span> meses</span>
                        </span>

                        {{-- Día pago --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                            bg-gray-50 border border-gray-200 text-gray-700
                            dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
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
                            <span>Día: <span class="font-semibold tabular-nums">{{ $diaPago }}</span></span>
                        </span>

                        {{-- Fechas --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg
                            bg-gray-50 border border-gray-200 text-gray-700
                            dark:bg-neutral-900/60 dark:border-neutral-700 dark:text-neutral-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4" />
                                <path d="M8 2v4" />
                                <path d="M3 10h18" />
                            </svg>
                            <span class="tabular-nums">{{ $fechaInicioFmt }} - {{ $fechaVencFmt }}</span>
                        </span>

                    </div>
                </div>

                {{-- CTA --}}
                <div class="shrink-0">
                    <button type="button"
                        wire:click="$dispatch('openPagarBanco', { inversionId: {{ $inversionId }} })"
                        @disabled($bloqueado)
                        class="h-10 px-3 rounded-xl text-[13px] font-semibold inline-flex items-center gap-2
                            bg-indigo-600 text-white hover:opacity-90
                            disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Registrar pago banco">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-lg bg-white/15">
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
                        <span>Pagar</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="px-4 py-3 bg-gray-50 dark:bg-neutral-900/60">
            <div class="text-[12px] text-gray-600 dark:text-neutral-300">
                Tip: si el pago está PENDIENTE puedes eliminarlo directo; si está PAGADO pedirá contraseña.
            </div>
        </div>
    </div>


    {{-- ===================== MOVIMIENTOS (MOBILE = CARDS) ===================== --}}
    <div
        class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-neutral-700 dark:bg-neutral-900/30">

        {{-- Header listado --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div class="text-[13px] font-semibold text-gray-800 dark:text-neutral-100">Movimientos</div>
                <div class="text-[11px] text-gray-500 dark:text-neutral-400">{{ count($movimientos) }} registros</div>
            </div>
        </div>

        {{-- Listado --}}
        <div class="p-3 space-y-2">
            @forelse($movimientos as $m)
                @php
                    $tipo = $m['tipo'] ?? '';
                    $estado = $m['estado'] ?? '';
                    $isPend = $estado === 'PENDIENTE';
                    $isPag = $estado === 'PAGADO';

                    $badgeBase = 'inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold';
                    $badgeGray = $badgeBase . ' bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-200';
                    $badgeAmber = $badgeBase . ' bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                    $badgeSky = $badgeBase . ' bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300';

                    $badge = $tipo === 'CAPITAL_INICIAL' ? $badgeGray : ($isPend ? $badgeAmber : $badgeSky);

                    $amountClass =
                        $tipo !== 'CAPITAL_INICIAL'
                            ? ($isPend
                                ? 'text-amber-700 dark:text-amber-300'
                                : 'text-sky-700 dark:text-sky-300')
                            : 'text-gray-900 dark:text-neutral-100';
                @endphp

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900/40">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[12px] font-semibold text-gray-900 dark:text-neutral-100">#{{ $m['idx'] }}</span>
                                <span class="{{ $badge }}">
                                    @if (($m['tipo'] ?? '') === 'CAPITAL_INICIAL')
                                        INICIAL
                                    @elseif (($m['estado'] ?? '') === 'PENDIENTE')
                                        PENDIENTE
                                    @else
                                        PAGADO
                                    @endif
                                </span>
                            </div>

                            <div class="mt-1 text-[13px] font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $m['descripcion'] }}
                            </div>

                            @if (!empty($m['banco_linea']))
                                <div class="mt-1 text-[12px] text-gray-500 dark:text-neutral-400">
                                    {{ $m['banco_linea'] }}
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-right">
                            <div class="text-[11px] text-gray-500 dark:text-neutral-400">Total</div>
                            <div class="mt-0.5 text-[14px] font-bold tabular-nums {{ $amountClass }}">
                                {{ $m['total'] ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Fecha
                            </div>
                            <div
                                class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $m['fecha'] ?? '—' }}
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900/60">
                            <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">Fecha
                                pago</div>
                            <div
                                class="mt-1 text-[12px] font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $m['fecha_pago'] ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[12px]
                            bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                            <span class="font-semibold">Capital:</span>
                            <span
                                class="font-bold tabular-nums text-slate-900 dark:text-slate-100">{{ $m['capital'] ?? '—' }}</span>
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[12px]
                            bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200">
                            <span class="font-semibold">Interés:</span>
                            <span
                                class="font-bold tabular-nums text-gray-900 dark:text-neutral-100">{{ $m['interes'] ?? '—' }}</span>
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[12px]
                            bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200">
                            <span class="font-semibold">%:</span>
                            <span
                                class="font-bold tabular-nums text-gray-900 dark:text-neutral-100">{{ $m['pct_interes'] ?? '—' }}</span>
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[12px]
                            bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200">
                            <span class="font-semibold">Comp:</span>
                            <span
                                class="font-bold tabular-nums text-gray-900 dark:text-neutral-100">{{ $m['comprobante'] ?? '—' }}</span>
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-2">

                        {{-- Confirmar --}}
                        @if (!empty($m['puede_confirmar_banco']))
                            <button type="button"
                                class="h-9 px-3 rounded-xl text-[12px] font-semibold inline-flex items-center gap-2
                                       bg-green-600 text-white hover:bg-green-700"
                                title="Abrir para confirmar / editar"
                                wire:click="openConfirmarBanco({{ (int) $m['id'] }})">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                Confirmar
                            </button>
                        @endif

                        {{-- Ver imagen --}}
                        @if (!empty($m['tiene_imagen']))
                            <button type="button" wire:click="verFotoMovimiento({{ $m['id'] }})"
                                class="h-9 w-9 inline-flex items-center justify-center rounded-xl border
                                       border-gray-200 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                title="Ver imagen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        @endif

                        {{-- Eliminar --}}
                        @if (!empty($m['puede_eliminar_fila']))
                            @if (($m['estado'] ?? '') === 'PAGADO')
                                <button type="button"
                                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl border
                                           border-red-300 text-red-700 hover:bg-red-50
                                           dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
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
                                <div x-data>
                                    <button type="button"
                                        class="h-9 w-9 inline-flex items-center justify-center rounded-xl border
                                               border-red-300 text-red-700 hover:bg-red-50
                                               dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500/15"
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
                        @endif

                        {{-- Eliminar TODO --}}
                        @if (!empty($m['es_capital_inicial']))
                            <button type="button"
                                class="h-9 w-9 inline-flex items-center justify-center rounded-xl border
                                       border-red-400 text-red-800 hover:bg-red-50
                                       dark:border-red-700 dark:text-red-300 dark:hover:bg-red-500/20"
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
                        @endif

                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500 dark:text-neutral-400">
                    No hay movimientos registrados.
                </div>
            @endforelse
        </div>

        {{-- FOOTER TOTALES (MOBILE) --}}
        <div class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900">
            <div class="p-3 space-y-2">

                {{-- Pagados --}}
                <div
                    class="rounded-2xl border border-sky-200 bg-white p-3 dark:border-sky-800/40 dark:bg-neutral-900/40">
                    <div class="flex items-center justify-between">
                        <div
                            class="inline-flex items-center gap-2 text-[11px] font-bold tracking-wide uppercase text-sky-800 dark:text-sky-200">
                            <span class="inline-flex w-2 h-2 rounded-full bg-sky-500"></span>
                            Pagos realizados
                        </div>
                        <div class="text-[12px] font-bold tabular-nums text-sky-900 dark:text-sky-200">
                            {{ $tPag['sumTotalFmt'] ?? '0' }}
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
                                Interés</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPag['sumInteresFmt'] ?? '0' }}
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
                        <div class="text-[12px] font-bold tabular-nums text-amber-900 dark:text-amber-200">
                            {{ $tPen['sumTotalFmt'] ?? '0' }}
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
                                Interés</div>
                            <div class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $tPen['sumInteresFmt'] ?? '0' }}
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

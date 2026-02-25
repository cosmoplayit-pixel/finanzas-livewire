{{-- MOBILE: TABLA INVERSIONES (<= md) - PRO UI --}}
<div class="md:hidden space-y-3">
    @forelse ($inversiones as $inv)

        @php
            $isPrivado = $inv->tipo === 'PRIVADO';

            // Acentos por tipo
            $accentBar = $isPrivado ? 'bg-blue-500 dark:bg-green-400' : 'bg-teal-500 dark:bg-teal-400';

            $cardBg = $isPrivado
                ? 'bg-gradient-to-b from-blue-50/60 to-white dark:from-blue-900/10 dark:to-neutral-900/30'
                : 'bg-gradient-to-b from-teal-50/60 to-white dark:from-teal-900/10 dark:to-neutral-900/30';

            $typeBadge = $isPrivado
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'
                : 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-200';

            // Estado (utilidad pendiente solo aplica a PRIVADO)
            $utilPendiente = $isPrivado && ($inv->resumen['estado_utilidad'] ?? null) === 'PENDIENTE';
        @endphp

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden {{ $cardBg }}">

            {{-- Accent bar --}}
            <div class="h-1.5 {{ $accentBar }}"></div>

            {{-- TOP: Código + estado --}}
            <div class="px-4 pt-3 pb-2">
                <div class="flex items-start justify-between gap-3">

                    {{-- Left: Codigo + meta --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $inv->codigo }}
                            </div>

                            {{-- Tipo badge --}}
                            <span
                                class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $typeBadge }}">
                                {{-- icon tag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M20.59 13.41 11 3.83V2h-2v2.59l9.59 9.58a2 2 0 0 1 0 2.83l-2.34 2.34a2 2 0 0 1-2.83 0L3.83 13.41a2 2 0 0 1 0-2.83l2.34-2.34" />
                                    <path d="M7 7h.01" />
                                </svg>
                                {{ $inv->tipo }}
                            </span>
                        </div>

                        <div class="mt-1 text-sm text-gray-500 dark:text-neutral-400 inline-flex items-center gap-2">
                            <span class="truncate">
                                Titular: {{ $inv->nombre_completo }}
                            </span>
                        </div>
                    </div>

                    {{-- Right: Estado --}}
                    <div class="shrink-0">
                        @if ($utilPendiente)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg
                                         bg-amber-100 text-amber-700
                                         dark:bg-amber-900/30 dark:text-amber-300 text-[12px] font-semibold">
                                {{-- icon clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 3" />
                                </svg>
                                PENDIENTE
                            </span>
                        @else
                            @if ($inv->estado === 'ACTIVA')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg
                                             bg-emerald-100 text-emerald-700
                                             dark:bg-emerald-900/30 dark:text-emerald-300 text-[12px] font-semibold">
                                    {{-- icon check --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                    ACTIVA
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg
                                             bg-gray-200 text-gray-700
                                             dark:bg-neutral-700 dark:text-neutral-200 text-[12px] font-semibold">
                                    {{-- icon lock --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2"
                                            ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                    CERRADA
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="px-3 pb-3 space-y-3">

                {{-- Banco box --}}
                <div
                    class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white/70 dark:bg-neutral-900/40 px-3 py-2.5">
                    <div class="flex items-start gap-2">
                        {{-- Contenido --}}
                        <div class="min-w-0 flex-1">
                            {{-- Nombre banco --}}
                            <div
                                class="flex items-center text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{-- Icono banco --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mt-0.5 shrink-0 text-gray-700 dark:text-neutral-200"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 10h18" />
                                    <path d="M5 10V20" />
                                    <path d="M19 10V20" />
                                    <path d="M9 10V20" />
                                    <path d="M15 10V20" />
                                    <path d="M2 20h20" />
                                    <path d="M12 2 2 7h20L12 2z" />
                                </svg>
                                <span class="mx-1  shrink-0 text-gray-300 dark:text-neutral-600"></span>
                                {{ $inv->banco->nombre ?? '—' }}
                            </div>

                            {{-- Cuenta + Moneda (compacto, sin “TAB”) --}}
                            <div class="mt-0.5  min-w-0 text-xs text-gray-500 dark:text-neutral-400 leading-none">

                                {{-- Cuenta --}}
                                <span class="inline-flex items-center min-w-0 truncate">
                                    {{-- icon # --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 mr-1"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="4" y1="9" x2="20" y2="9" />
                                        <line x1="4" y1="15" x2="20" y2="15" />
                                        <line x1="10" y1="3" x2="8" y2="21" />
                                        <line x1="16" y1="3" x2="14" y2="21" />
                                    </svg>

                                    <span class="truncate">{{ $inv->banco->numero_cuenta ?? '—' }}</span>
                                    {{-- Separador --}}
                                    <span class="mx-1 shrink-0 text-gray-300 dark:text-neutral-600">•</span>

                                    {{-- Moneda --}}
                                    <span class="inline-flex items-center shrink-0">

                                        <span class="font-medium">{{ $inv->moneda }}</span>
                                    </span>
                                </span>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resumen chips (todos con color de Capital) --}}
                <div class="space-y-2">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                        Resumen
                    </div>

                    @php
                        $chipBase = 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200';
                        $chipValue = 'text-slate-900 dark:text-slate-100';
                    @endphp

                    @if ($isPrivado)
                        <div class="flex flex-wrap items-center gap-2 text-[13px]">

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Capital:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['capital'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">%:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['pct_utilidad_actual'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Util. Pagada:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['utilidad_pagada'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Util. por Pagar:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['utilidad_por_pagar'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Hasta:</span>
                                <span class="font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['hasta_fecha'] ?? '—' }}
                                </span>
                            </span>

                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-2 text-[13px]">

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Capital:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['deuda_cuotas'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">%:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['interes'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Total a pagar:</span>
                                <span class="tabular-nums font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['total_a_pagar'] ?? '—' }}
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-lg px-2 py-1 {{ $chipBase }}">
                                <span class="font-semibold">Hasta:</span>
                                <span class="font-semibold {{ $chipValue }}">
                                    {{ $inv->resumen['hasta_fecha'] ?? '—' }}
                                </span>
                            </span>

                        </div>
                    @endif
                </div>

                {{-- Fechas + acciones --}}
                <div
                    class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white/70 dark:bg-neutral-900/30 px-3 py-2.5">
                    <div class="flex items-center justify-between ">

                        {{-- Fechas (izquierda) --}}
                        <div class="min-w-0 space-y-1">
                            <div class="inline-flex items-center gap-1.5 text-gray-800 dark:text-neutral-100">
                                {{-- icon calendar --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <span class="text-gray-500 dark:text-neutral-400 text-xs">Ini:</span>
                                <span class="tabular-nums text-sm">
                                    {{ optional($inv->fecha_inicio)->format('d/m/Y') }}
                                </span>
                            </div>

                            <span class="mx-2 shrink-0 text-gray-300 dark:text-neutral-600"></span>

                            <div class="inline-flex items-center gap-1.5 text-gray-800 dark:text-neutral-100">
                                {{-- icon flag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M4 22V4" />
                                    <path d="M4 4h14l-2 5 2 5H4" />
                                </svg>
                                <span class="text-gray-500 dark:text-neutral-400 text-xs">Venc:</span>
                                <span class="tabular-nums text-sm">
                                    {{ $inv->fecha_vencimiento ? $inv->fecha_vencimiento->format('d/m/Y') : '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Acciones (derecha) --}}
                        <div class="shrink-0 inline-flex items-center gap-1.5">
                            @can('inversiones.movimiento')
                                <button type="button"
                                    wire:click="$dispatch('openMovimientosInversion', [{{ $inv->id }}])"
                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg border
                                           border-gray-300 text-gray-700 hover:bg-gray-100
                                           dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                    title="Ver movimientos">
                                    {{-- icon list --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="8" y1="6" x2="21" y2="6" />
                                        <line x1="8" y1="12" x2="21" y2="12" />
                                        <line x1="8" y1="18" x2="21" y2="18" />
                                        <line x1="3" y1="6" x2="3.01" y2="6" />
                                        <line x1="3" y1="12" x2="3.01" y2="12" />
                                        <line x1="3" y1="18" x2="3.01" y2="18" />
                                    </svg>
                                </button>
                            @endcan

                            <button type="button"
                                wire:click="$dispatch('openFotoComprobanteInversion',[{{ $inv->id }}])"
                                @disabled(!$inv->comprobante)
                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg border
                                {{ $inv->comprobante
                                    ? 'border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700'
                                    : 'border-gray-200 text-gray-300 cursor-not-allowed dark:border-neutral-700 dark:text-neutral-600' }}"
                                title="Ver imagen">
                                {{-- icon image --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="M21 15l-5-5L5 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    @empty
        <div class="p-6 text-center text-gray-500 dark:text-neutral-400">
            No hay inversiones registradas.
        </div>
    @endforelse
</div>

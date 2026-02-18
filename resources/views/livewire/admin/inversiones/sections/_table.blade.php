{{-- DESKTOP: TABLA INVERSIONES --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm font-sans min-w-[1350px] lg:min-w-0">

            <thead
                class="sticky top-0 z-10
                       bg-gray-50 text-gray-700
                       dark:bg-neutral-900 dark:text-neutral-200
                       border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left  tracking-wide">
                    <th class="p-3 w-[90px] text-center">Código</th>
                    <th class="p-3 w-[200px]">Titular</th>
                    <th class="p-3 w-[200px]">Banco</th>
                    <th class="p-3 w-[520px]">Resumen</th>
                    <th class="p-3 w-[100px] text-center">Fecha</th>
                    <th class="p-3 w-[100px] text-center">Estado</th>
                    <th class="p-3 w-[100px] text-center">Acc.</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($inversiones as $inv)
                    <tr class="text-left   hover:bg-gray-50 dark:hover:bg-neutral-900/50">

                        {{-- CODIGO --}}
                        <td class="p-3 text-center text-gray-900 dark:text-neutral-100">
                            {{ $inv->codigo }}
                        </td>

                        {{-- TITULAR --}}
                        <td class="p-3">
                            <div class="text-gray-900 dark:text-neutral-100">
                                {{ $inv->nombre_completo }}
                            </div>
                            <div class="text-gray-500 dark:text-neutral-400 inline-flex items-center gap-1">
                                {{-- icon tag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M20.59 13.41 11 3.83V2h-2v2.59l9.59 9.58a2 2 0 0 1 0 2.83l-2.34 2.34a2 2 0 0 1-2.83 0L3.83 13.41a2 2 0 0 1 0-2.83l2.34-2.34" />
                                    <path d="M7 7h.01" />
                                </svg>
                                <span>{{ $inv->tipo }}</span>
                                <span class="text-gray-300 dark:text-neutral-600">•</span>
                                <span>{{ $inv->moneda }}</span>
                            </div>
                        </td>

                        {{-- BANCO --}}
                        <td class="p-3 text-left">
                            <div class="flex items-start gap-2 text-gray-900 dark:text-neutral-100">
                                {{-- icon bank --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mt-0.5 text-gray-600 dark:text-neutral-300" viewBox="0 0 24 24"
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

                                <div class="min-w-0">
                                    <div class="truncate">
                                        {{ $inv->banco->nombre }}
                                    </div>

                                    {{-- aqui: icono # NO debe empujar el texto --}}
                                    <div class="mt-0.5 flex items-center text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 -ml-6 mr-1"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="4" y1="9" x2="20" y2="9" />
                                            <line x1="4" y1="15" x2="20" y2="15" />
                                            <line x1="10" y1="3" x2="8" y2="21" />
                                            <line x1="16" y1="3" x2="14" y2="21" />
                                        </svg>

                                        <span class="truncate">{{ $inv->banco->numero_cuenta ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- RESUMEN --}}
                        <td class="p-3">
                            @if ($inv->tipo === 'PRIVADO')
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[13px]">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                                        <span class="font-semibold">Capital:</span>
                                        <span class="tabular-nums font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $inv->resumen['capital'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                        <span class="font-semibold">% Utilidad:</span>
                                        <span class="tabular-nums font-semibold text-indigo-900 dark:text-indigo-100">
                                            {{ $inv->resumen['pct_utilidad_actual'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                                        <span class="font-semibold">Utilidad pagada:</span>
                                        <span class="tabular-nums font-semibold text-emerald-900 dark:text-emerald-100">
                                            {{ $inv->resumen['utilidad_pagada'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                                        <span class="font-semibold">Utilidad por pagar:</span>
                                        <span class="tabular-nums font-semibold text-amber-900 dark:text-amber-100">
                                            {{ $inv->resumen['utilidad_por_pagar'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200">
                                        <span class="font-semibold">Hasta:</span>
                                        <span class="font-semibold text-gray-900 dark:text-neutral-100">
                                            {{ $inv->resumen['hasta_fecha'] ?? '—' }}
                                        </span>
                                    </span>
                                </div>
                            @else
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[13px]">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                                        <span class="font-semibold">Capital:</span>
                                        <span class="tabular-nums font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $inv->resumen['deuda_cuotas'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200">
                                        <span class="font-semibold">Interés:</span>
                                        <span class="tabular-nums font-semibold text-rose-900 dark:text-rose-100">
                                            {{ $inv->resumen['interes'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-200">
                                        <span class="font-semibold">Total a pagar:</span>
                                        <span class="tabular-nums font-semibold text-violet-900 dark:text-violet-100">
                                            {{ $inv->resumen['total_a_pagar'] ?? '—' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200">
                                        <span class="font-semibold">Hasta:</span>
                                        <span class="font-semibold text-gray-900 dark:text-neutral-100">
                                            {{ $inv->resumen['hasta_fecha'] ?? '—' }}
                                        </span>
                                    </span>
                                </div>
                            @endif
                        </td>



                        {{-- FECHAS --}}
                        <td class="p-3 text-center">
                            <div class="inline-flex flex-col items-start gap-1">
                                <div class="inline-flex items-center gap-1.5 text-gray-800 dark:text-neutral-100">
                                    {{-- icon calendar --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2"
                                            ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <span class="text-gray-500 dark:text-neutral-400">Ini:</span>
                                    <span
                                        class="tabular-nums">{{ optional($inv->fecha_inicio)->format('d/m/Y') }}</span>
                                </div>

                                <div class="inline-flex items-center gap-1.5 text-gray-800 dark:text-neutral-100">
                                    {{-- icon flag --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 22V4" />
                                        <path d="M4 4h14l-2 5 2 5H4" />
                                    </svg>
                                    <span class="text-gray-500 dark:text-neutral-400">Venc:</span>
                                    <span class="tabular-nums">
                                        {{ $inv->fecha_vencimiento ? $inv->fecha_vencimiento->format('d/m/Y') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- ESTADO --}}
                        <td class="p-3 text-center">
                            @if ($inv->tipo === 'PRIVADO' && ($inv->resumen['estado_utilidad'] ?? null) === 'PENDIENTE')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded
                                    bg-amber-100 text-amber-700
                                    dark:bg-amber-900/30 dark:text-amber-300">
                                    {{-- icon clock/alert --}}
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
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded
                                        bg-emerald-100 text-emerald-700
                                        dark:bg-emerald-900/30 dark:text-emerald-300">
                                        {{-- icon check --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                        ACTIVA
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded
                                        bg-gray-200 text-gray-700
                                        dark:bg-neutral-700 dark:text-neutral-200">
                                        {{-- icon lock --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2"
                                                ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        CERRADA
                                    </span>
                                @endif
                            @endif
                        </td>


                        {{-- ACCIONES --}}
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1">
                                @can('inversiones.movimiento')
                                    <button type="button"
                                        wire:click="$dispatch('openMovimientosInversion', [{{ $inv->id }}])"
                                        class="w-8 h-8 cursor-pointer inline-flex items-center justify-center
                                               rounded-lg border border-gray-300 text-gray-700
                                               hover:bg-gray-100
                                               dark:border-neutral-700 dark:text-neutral-200
                                               dark:hover:bg-neutral-700"
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
                                    class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                    {{ $inv->comprobante
                                        ? 'border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700'
                                        : 'border-gray-200 text-gray-300 cursor-not-allowed dark:border-neutral-700 dark:text-neutral-600' }}"
                                    title="Ver imagen">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2"
                                            ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <path d="M21 15l-5-5L5 21" />
                                    </svg>
                                </button>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500 dark:text-neutral-400">
                            No hay inversiones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>

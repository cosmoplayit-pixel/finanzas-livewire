{{-- DESKTOP: TABLA INVERSIONES --}}
<div class="hidden md:block border border-gray-100 rounded bg-white dark:bg-neutral-800 overflow-hidden shadow-sm"
    @if (isset($highlight_inversion_id) && $highlight_inversion_id) x-data
    x-init="setTimeout(() => {
        const el = document.getElementById('inversion-row-target-{{ (int) $highlight_inversion_id }}');
        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        @if (isset($highlight_movimiento_id) && $highlight_movimiento_id)
            $wire.dispatch('openMovimientosInversionWithHighlight', { inversionId: {{ (int) $highlight_inversion_id }}, movimientoId: {{ (int) $highlight_movimiento_id }} }); @endif
    }, 700)" @endif>
    <div class="overflow-x-auto">
        <table class="w-full text-[14px] min-w-[1350px] lg:min-w-0">

            <thead
                class="sticky top-0 z-10
                       bg-slate-50/50 text-slate-600
                       dark:bg-neutral-900/50 dark:text-neutral-400
                       border-b border-gray-100 dark:border-neutral-800">
                <tr class="text-left text-[12px] uppercase tracking-wider font-semibold">
                    <th class="p-2 w-[7%] text-center">Código</th>
                    <th class="p-2 w-[15%]">Titular</th>
                    <th class="p-2 w-[15%]">Banco</th>
                    <th class="p-2 w-[38%]">Resumen</th>
                    <th class="p-2 w-[9%] text-center">Fecha</th>
                    <th class="p-2 w-[8%] text-center">Estado</th>
                    <th class="p-2 w-[8%] text-center">Acc.</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                @forelse ($inversiones as $inv)
                    @php
                        $isTargetInv = isset($highlight_inversion_id) && $highlight_inversion_id == $inv->id;
                    @endphp
                    <tr @if ($isTargetInv) id="inversion-row-target-{{ $inv->id }}" @endif
                        class="text-left text-gray-700 dark:text-neutral-200 border-t border-gray-100 dark:border-neutral-800 transition-colors
                        {{ $isTargetInv ? 'bg-indigo-50/60 dark:bg-indigo-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-neutral-900/60' }}">

                        {{-- CODIGO --}}
                        <td
                            class="p-2 text-center text-[13px] {{ $isTargetInv ? 'border-l-4 border-indigo-400' : 'border-l-4 border-transparent' }}">
                            <span
                                class="inline-flex px-1.5 py-0.5 rounded tabular-nums
                                {{ $inv->tipo === 'BANCO'
                                    ? 'bg-slate-100 text-slate-700 font-bold dark:bg-neutral-800 dark:text-neutral-300'
                                    : 'bg-blue-100 text-blue-700 font-normal dark:bg-blue-900/30 dark:text-blue-300' }}">
                                {{ $inv->codigo }}
                            </span>
                        </td>

                        {{-- TITULAR --}}
                        <td class="p-2">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $inv->nombre_completo }}
                            </div>
                            <div class="text-[12px] text-gray-500 dark:text-neutral-400 inline-flex items-center gap-1">
                                {{-- icon tag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M20.59 13.41 11 3.83V2h-2v2.59l9.59 9.58a2 2 0 0 1 0 2.83l-2.34 2.34a2 2 0 0 1-2.83 0L3.83 13.41a2 2 0 0 1 0-2.83l2.34-2.34" />
                                    <path d="M7 7h.01" />
                                </svg>
                                <span class="font-normal text-gray-500 dark:text-neutral-400">
                                    INVERSION {{ $inv->tipo === 'PRIVADO' ? 'PRIVADA' : 'BANCO' }}
                                </span>
                            </div>
                        </td>

                        {{-- BANCO --}}
                        <td class="p-2 text-left">
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
                                    <div class="truncate font-semibold">
                                        {{ $inv->banco->nombre }}
                                    </div>

                                    {{-- aqui: icono # NO debe empujar el texto --}}
                                    <div
                                        class="mt-0.5 flex items-center text-[12px] text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 -ml-6 mr-1"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>

                                        <span class="truncate">{{ $inv->banco->titular ?? '—' }}</span>
                                        <span class="mx-1 text-gray-300 dark:text-neutral-600">•</span>
                                        <span class="font-medium">{{ $inv->moneda }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="p-2">
                            @php
                                $r = $inv->resumen ?? [];

                                // Helpers (sin funciones globales)
                                $has = function ($key) use ($r) {
                                    if (!array_key_exists($key, $r)) {
                                        return false;
                                    }
                                    $v = $r[$key];
                                    if ($v === null) {
                                        return false;
                                    }
                                    $v = is_string($v) ? trim($v) : $v;
                                    return $v !== '' && $v !== '—';
                                };

                                // Estilos unificados (mismo color en ambos tipos)
                                $pillBase = 'inline-flex items-center gap-2 rounded-md px-2 py-1 text-[13px]';
                                $pillGray =
                                    $pillBase .
                                    ' bg-gray-100 text-gray-700 dark:bg-neutral-900/40 dark:text-neutral-200';
                                $pillSlate =
                                    $pillBase . ' bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200';

                                // ✅ TODO AL MISMO COLOR (Slate)
                                $pillPrimary = $pillSlate;
                                $pillSuccess = $pillSlate;
                                $pillWarn = $pillSlate;

                                $valBase = 'tabular-nums text-slate-900 dark:text-slate-100';
                                $valStrong = $valBase;
                                $valPrimary = $valBase;
                                $valSuccess = $valBase;
                                $valWarn = $valBase;
                                $valSlate = $valBase;
                            @endphp

                            @if ($inv->tipo === 'PRIVADO')
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">

                                    {{-- Capital --}}
                                    @if ($has('capital'))
                                        <span class="{{ $pillSlate }}">
                                            <span class="font-semibold">Capital:</span>
                                            <span class="{{ $valSlate }}">{{ $r['capital'] }}</span>
                                        </span>
                                    @endif

                                    {{-- % Utilidad (mismo color que Interés en BANCO) --}}
                                    @if ($has('pct_utilidad_actual'))
                                        <span class="{{ $pillPrimary }}">
                                            <span class="font-semibold">% Interés:</span>
                                            <span class="{{ $valPrimary }}">{{ $r['pct_utilidad_actual'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Utilidad pagada (mismo color que Ult. Pago en BANCO) --}}
                                    @if ($has('utilidad_pagada'))
                                        <span class="{{ $pillSuccess }}">
                                            <span class="font-semibold">Interés pagado:</span>
                                            <span class="{{ $valSuccess }}">{{ $r['utilidad_pagada'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Utilidad por pagar (mismo color en ambos) --}}
                                    @if ($has('utilidad_por_pagar'))
                                        <span class="{{ $pillWarn }}">
                                            <span class="font-semibold">Interés por pagar:</span>
                                            <span class="{{ $valWarn }}">{{ $r['utilidad_por_pagar'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Hasta --}}
                                    @if ($has('hasta_fecha'))
                                        <span class="{{ $pillGray }}">
                                            <span class="font-semibold">Hasta:</span>
                                            <span class="{{ $valStrong }}">{{ $r['hasta_fecha'] }}</span>
                                        </span>
                                    @endif

                                </div>
                            @else
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">

                                    {{-- Capital / deuda --}}
                                    @if ($has('deuda_cuotas'))
                                        <span class="{{ $pillSlate }}">
                                            <span class="font-semibold">Capital:</span>
                                            <span class="{{ $valSlate }}">{{ $r['deuda_cuotas'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Ult. Interés (mismo color que % Utilidad en PRIVADO) --}}
                                    @if ($has('interes'))
                                        <span class="{{ $pillPrimary }}">
                                            <span class="font-semibold">Ult. Interés:</span>
                                            <span class="{{ $valPrimary }}">{{ $r['interes'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Ult. Pago (mismo color que Utilidad pagada en PRIVADO) --}}
                                    @if ($has('total_a_pagar'))
                                        <span class="{{ $pillSuccess }}">
                                            <span class="font-semibold">Ult. Pago:</span>
                                            <span class="{{ $valSuccess }}">{{ $r['total_a_pagar'] }}</span>
                                        </span>
                                    @endif

                                    {{-- Hasta --}}
                                    @if ($has('hasta_fecha'))
                                        <span class="{{ $pillGray }}">
                                            <span class="font-semibold">Hasta:</span>
                                            <span class="{{ $valStrong }}">{{ $r['hasta_fecha'] }}</span>
                                        </span>
                                    @endif

                                </div>
                            @endif
                        </td>

                        {{-- FECHAS --}}
                        <td class="p-2 text-center">
                            <div class="inline-flex flex-col items-start gap-1 text-[13px]">
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
                        <td class="p-2 text-center">
                            @if (($inv->resumen['estado_utilidad'] ?? null) === 'PENDIENTE')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded text-[13px]
                                    bg-amber-100 text-amber-700
                                    dark:bg-amber-900/30 dark:text-amber-300">
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
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded text-[13px]
                                        bg-emerald-100 text-emerald-700
                                        dark:bg-emerald-900/30 dark:text-emerald-300">
                                        ACTIVO
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded text-[13px]
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
                        <td class="p-2 text-center">
                            <div class="inline-flex items-center gap-1">
                                @can('inversiones.view')
                                    <button type="button"
                                        wire:click="$dispatch('openMovimientosInversion', [{{ $inv->id }}])"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 shadow-sm dark:bg-blue-500 dark:border-blue-500 dark:hover:bg-blue-400"
                                        title="Ver movimientos">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                                            <path d="M21 12H17a2 2 0 0 0 0 4h4v-4Z" />
                                        </svg>
                                    </button>
                                @endcan

                                @php
                                    $archivo = $inv->comprobante ?? null;
                                    $esPdf = $archivo && strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf';
                                @endphp

                                @if ($archivo)
                                    @if ($esPdf)
                                        <a href="{{ asset('storage/' . $archivo) }}" target="_blank"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                            title="Ver PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <polyline points="14 2 14 8 20 8" />
                                                <line x1="9" y1="13" x2="15" y2="13" />
                                                <line x1="9" y1="17" x2="15" y2="17" />
                                                <line x1="9" y1="9" x2="11" y2="9" />
                                            </svg>
                                        </a>
                                    @else
                                        <button type="button"
                                            wire:click="$dispatch('openFotoComprobanteInversion',[{{ $inv->id }}])"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                            title="Ver imagen">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                        </button>
                                    @endif
                                @else
                                    <span
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                        title="Sin comprobante">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2"
                                                ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </span>
                                @endif

                                {{-- ELIMINAR TODO --}}
                                <button type="button"
                                    @click="$dispatch('openEliminarInversionModal', [{{ $inv->id }}])"
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                    title="Eliminar inversión completa">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                </button>
                            </div>
                        </td>

                    </tr>
                @empty

                    <tr>
                        <td class="p-8 text-center" colspan="6">
                            <div class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <span class="text-sm">Sin resultados.</span>
                            </div>
                        </td>
                    </tr>

                @endforelse
            </tbody>

        </table>
    </div>
</div>

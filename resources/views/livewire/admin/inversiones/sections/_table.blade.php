{{-- DESKTOP: TABLA INVERSIONES --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[1350px] lg:min-w-0">

            <thead
                class="sticky top-0 z-10
                       bg-gray-50 text-gray-700
                       dark:bg-neutral-900 dark:text-neutral-200
                       border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left font-semibold  tracking-wide">

                    <th class="p-3 w-[50px] text-center">Código</th>
                    <th class="p-3 w-[110px]">Titular</th>
                    <th class="p-3 w-[110px]">Banco</th>
                    <th class="p-3 w-[110px]">Capital</th>
                    <th class="p-3 w-[110px] text-center">% Utilidad</th>
                    <th class="p-3 w-[110px] text-center">Fechas</th>
                    <th class="p-3 w-[110px] text-center">Estado</th>
                    <th class="p-3 w-[110px] text-center">Acc.</th>

                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($inversiones as $inv)
                    <tr class="text-left hover:bg-gray-50 dark:hover:bg-neutral-900/50">

                        {{-- CODIGO --}}
                        <td class="p-3 text-center font-mono text-gray-900 dark:text-neutral-100">
                            {{ $inv->codigo }}
                        </td>

                        {{-- TITULAR --}}
                        <td class="p-3">
                            <div class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ $inv->nombre_completo }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400 inline-flex items-center gap-1">
                                {{-- icon tag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M20.59 13.41 11 3.83V2h-2v2.59l9.59 9.58a2 2 0 0 1 0 2.83l-2.34 2.34a2 2 0 0 1-2.83 0L3.83 13.41a2 2 0 0 1 0-2.83l2.34-2.34" />
                                    <path d="M7 7h.01" />
                                </svg>
                                <span>{{ $inv->tipo }}</span>
                            </div>
                        </td>

                        {{-- BANCO  --}}
                        <td class="p-3">
                            @if ($inv->banco)
                                <div class="inline-flex items-start gap-2 text-gray-900 dark:text-neutral-100">
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
                                        <div class="text-sm font-medium truncate">
                                            {{ $inv->banco->nombre }}
                                        </div>

                                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                            {{ $inv->banco->numero_cuenta ?? '—' }} | {{ $inv->moneda }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-gray-400 dark:text-neutral-500">
                                    — <span class="mx-1">•</span> {{ $inv->moneda }}
                                </div>
                            @endif
                        </td>

                        {{-- CAPITAL --}}
                        <td class="p-3  font-semibold tabular-nums">
                            @if ($inv->moneda === 'USD')
                                $ {{ number_format($inv->capital_actual, 2, ',', '.') }}
                            @else
                                Bs {{ number_format($inv->capital_actual, 2, ',', '.') }}
                            @endif
                        </td>

                        {{-- % UTILIDAD --}}
                        <td class="p-3 text-center tabular-nums">
                            {{ number_format($inv->porcentaje_utilidad, 2, ',', '.') }}%
                        </td>

                        {{-- FECHAS (más grande y compacto) --}}
                        <td class="p-3 text-center">
                            <div class="inline-flex flex-col items-start gap-1 text-sm">
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
                                    <span class="text-xs text-gray-500 dark:text-neutral-400">Ini:</span>
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
                                    <span class="text-xs text-gray-500 dark:text-neutral-400">Venc:</span>
                                    <span class="tabular-nums">
                                        {{ $inv->fecha_vencimiento ? $inv->fecha_vencimiento->format('d/m/Y') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- ESTADO --}}
                        <td class="p-3 text-center">
                            @if ($inv->estado === 'ACTIVA')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs
                                           bg-emerald-100 text-emerald-700
                                           dark:bg-emerald-900/30 dark:text-emerald-300">
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
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs
                                           bg-gray-200 text-gray-700
                                           dark:bg-neutral-700 dark:text-neutral-200">
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
                        </td>

                        {{-- ACCIONES (Ver imagen + Ver movimientos) --}}
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1">

                                {{-- Ver Movimientos --}}
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

                                {{-- Ver Imagen (si existe) --}}
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
                        <td colspan="8" class="p-6 text-center text-gray-500 dark:text-neutral-400">
                            No hay inversiones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>

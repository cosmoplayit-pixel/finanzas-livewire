{{-- PANEL (DESPLEGABLE) --}}
@if ($open)
    <tr class="bg-slate-50/40 dark:bg-neutral-900/40 border-t border-gray-300 dark:border-neutral-800">
        <td colspan="6" class="p-4">
            @php
                $meta = $panelAgenteMeta[$rowKey] ?? [
                    'nombre' => $row->agente_nombre,
                    'ci' => $row->agente_ci ?? '—',
                ];

                // ya no hay filtro de moneda dentro del panel; el panel es de esa fila/moneda
                $e = $panelEstado[$rowKey] ?? 'ALL';

                $totalFalta = (float) ($panelTotalFalta[$rowKey] ?? 0);
                $presupuestos = $panelData[$rowKey] ?? [];
            @endphp

            {{-- TABLA DEL PANEL --}}
            <div
                class="border border-gray-200 rounded-lg bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-[13px]">
                        <thead
                            class="bg-white text-gray-900 font-semibold dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                            <tr class="text-left text-xs">
                                <th class="p-3.5 text-center w-[50px] font-bold">#</th>
                                <th class="p-3.5 w-[240px] font-bold">Banco</th>
                                <th class="p-3.5 w-[160px] font-bold">Nro Transacción</th>
                                <th class="p-3.5 w-[170px] font-bold">Fecha</th>
                                <th class="p-3.5 text-right w-[140px] font-bold">Presupuesto</th>
                                <th class="p-3.5 text-right w-[130px] font-bold">Rendido</th>
                                <th class="p-3.5 text-right w-[140px] font-bold">Saldo por rendir</th>
                                @can('agente_presupuestos.view_detail')
                                    <th class="p-3.5 text-center w-[90px] font-bold">Acciones.</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                            @forelse($presupuestos as $p)
                                @php
                                    $saldo = (float) ($p->saldo_por_rendir ?? 0);
                                    $rendido = (float) ($p->rendido_total ?? 0);
                                    $monto = (float) ($p->monto ?? 0);
                                    $fechaTxt = $p->fecha_presupuesto
                                        ? \Carbon\Carbon::parse($p->fecha_presupuesto)->format('Y-m-d H:i')
                                        : '—';
                                @endphp

                                <tr wire:key="panel-{{ $rowKey }}-pres-{{ $p->id }}"
                                    class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors">
                                    <td
                                        class="p-3.5 whitespace-nowrap text-gray-700 dark:text-neutral-200 text-center font-medium">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="p-3.5 align-middle">
                                        <div class="min-w-0 space-y-1 leading-snug">

                                            {{-- Banco --}}
                                            <div class="truncate text-[13px] font-medium text-gray-900 dark:text-neutral-100"
                                                title="{{ $p->banco?->nombre ?? '—' }}">
                                                {{ $p->banco?->nombre ?? '—' }}
                                            </div>

                                            {{-- Cuenta + moneda --}}
                                            <div
                                                class="flex items-center gap-1.5 truncate text-[11px] font-medium text-gray-500 dark:text-neutral-400">
                                                {{-- Icono tarjeta --}}
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                                    <line x1="2" y1="10" x2="22" y2="10" />
                                                </svg>

                                                <span class="tracking-wide">
                                                    {{ $p->banco?->numero_cuenta ?? '—' }}
                                                    @if ($p->banco?->moneda)
                                                        <span class="mx-0.5 text-gray-300 dark:text-gray-600">|</span>
                                                        <span
                                                            class="text-gray-600 dark:text-gray-300">{{ $p->banco->moneda }}</span>
                                                    @endif
                                                </span>
                                            </div>

                                        </div>
                                    </td>

                                    <td class="p-3.5 whitespace-nowrap text-gray-600 dark:text-neutral-300 font-medium">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $p->nro_transaccion ?? '—' }}</span>
                                            @if ($p->foto_comprobante)
                                                <a href="{{ asset('storage/' . $p->foto_comprobante) }}" target="_blank"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                                    title="Ver Respaldo">
                                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-3.5 whitespace-nowrap text-gray-600 dark:text-neutral-300 font-medium">
                                        {{ $fechaTxt }}
                                    </td>

                                    <td
                                        class="p-3.5 text-right tabular-nums text-gray-900 dark:text-neutral-100 font-medium tracking-tight">
                                        {{ number_format($monto, 2, ',', '.') }}
                                    </td>

                                    <td
                                        class="p-3.5 text-right tabular-nums text-gray-900 dark:text-neutral-100 font-medium tracking-tight">
                                        {{ number_format($rendido, 2, ',', '.') }}
                                    </td>

                                    <td
                                        class="p-3.5 text-right tabular-nums font-bold tracking-tight
                                        {{ $saldo <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($saldo, 2, ',', '.') }}
                                    </td>

                                    @can('agente_presupuestos.view_detail')
                                        <td class="p-3 whitespace-nowrap align-middle">
                                            <div class="flex items-center justify-center gap-2">

                                                {{-- CREAR RENDICIÓN --}}
                                                @if (empty($p->rendicion_id))
                                                    <button type="button" wire:click="crearRendicion({{ $p->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="crearRendicion({{ $p->id }})"
                                                        wire:loading.class="cursor-not-allowed opacity-60"
                                                        wire:loading.class.remove="cursor-pointer hover:bg-emerald-600 hover:border-emerald-600 shadow-sm"
                                                        class="px-3 py-1.5 rounded-md border transition-all cursor-pointer text-xs font-semibold
                                                    bg-[#00b06b] text-white border-[#00b06b]
                                                    hover:bg-emerald-600 hover:border-emerald-600 hover:shadow-sm shadow-sm
                                                    dark:bg-emerald-500 dark:border-emerald-500 dark:hover:bg-emerald-400 dark:hover:border-emerald-400
                                                    inline-flex items-center justify-center gap-2">

                                                        {{-- Texto normal --}}
                                                        <span wire:loading.remove
                                                            wire:target="crearRendicion({{ $p->id }})">
                                                            Crear
                                                        </span>

                                                        {{-- Spinner --}}
                                                        <span wire:loading
                                                            wire:target="crearRendicion({{ $p->id }})"
                                                            class="inline-flex items-center">
                                                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24"
                                                                fill="none">
                                                                <circle class="opacity-25" cx="12" cy="12"
                                                                    r="10" stroke="currentColor" stroke-width="4" />
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                                            </svg>
                                                        </span>
                                                    </button>


                                                    {{-- VER RENDICIÓN --}}
                                                @else
                                                    <button type="button"
                                                        wire:click="openRendicionEditor({{ $p->rendicion_id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="openRendicionEditor({{ $p->rendicion_id }})"
                                                        wire:loading.class="cursor-not-allowed opacity-60"
                                                        wire:loading.class.remove="cursor-pointer hover:bg-gray-100 hover:border-gray-200 shadow-sm"
                                                        class="px-3.5 py-1.5 rounded-md border transition-all cursor-pointer text-xs font-semibold
                                                    bg-gray-50 text-gray-700 border-gray-200 shadow-sm
                                                    hover:bg-gray-100 hover:border-gray-300 hover:text-gray-900
                                                    dark:bg-neutral-800 dark:text-neutral-200 dark:border-neutral-700
                                                    dark:hover:bg-neutral-700 dark:hover:border-neutral-600
                                                    inline-flex items-center justify-center gap-2">

                                                        {{-- Texto normal --}}
                                                        <span wire:loading.remove
                                                            wire:target="openRendicionEditor({{ $p->rendicion_id }})">
                                                            Ver
                                                        </span>

                                                        {{-- Spinner --}}
                                                        <span wire:loading
                                                            wire:target="openRendicionEditor({{ $p->rendicion_id }})"
                                                            class="inline-flex items-center">
                                                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24"
                                                                fill="none">
                                                                <circle class="opacity-25" cx="12" cy="12"
                                                                    r="10" stroke="currentColor" stroke-width="4" />
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                                            </svg>
                                                        </span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center bg-white dark:bg-neutral-900/10">
                                        <div
                                            class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                            <svg class="w-10 h-10 mb-3 opacity-20" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                                </path>
                                            </svg>
                                            <span class="text-sm font-medium">No hay presupuestos para este
                                                agente/moneda con los filtros actuales.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
@endif

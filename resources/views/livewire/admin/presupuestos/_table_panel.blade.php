{{-- PANEL (DESPLEGABLE) --}}
@if ($open)
    <tr class="bg-gray-50/60 dark:bg-neutral-900/40">
        <td colspan="7" class="p-2">
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
            <div class="mt-4 border rounded bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-sm">
                        <thead
                            class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                            <tr class="text-left">
                                <th class="p-3 text-center w-[50px]">#</th>
                                <th class="p-3 w-[240px]">Banco</th>
                                <th class="p-3 w-[160px]">Nro Transacción</th>
                                <th class="p-3 w-[170px]">Fecha</th>
                                <th class="p-3 text-right w-[140px]">Presupuesto</th>
                                <th class="p-3 text-right w-[130px]">Rendido</th>
                                <th class="p-3 text-right w-[140px]">Saldo por rendir</th>
                                <th class="p-3 text-center w-[75px]">Acciones.</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
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
                                    class="hover:bg-gray-50 dark:hover:bg-neutral-900/40">
                                    <td class="p-3 whitespace-nowrap text-gray-700 dark:text-neutral-200 text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="p-3 align-middle">
                                        <div class="min-w-0 space-y-0.5 leading-snug">

                                            {{-- Banco --}}
                                            <div class="truncate text-sm font-medium text-gray-900 dark:text-neutral-100"
                                                title="{{ $p->banco?->nombre ?? '—' }}">
                                                {{ $p->banco?->nombre ?? '—' }}
                                            </div>

                                            {{-- Cuenta + moneda --}}
                                            <div
                                                class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                                {{-- Icono tarjeta --}}
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                                    <line x1="2" y1="10" x2="22" y2="10" />
                                                </svg>

                                                <span>
                                                    {{ $p->banco?->numero_cuenta ?? '—' }}
                                                    @if ($p->banco?->moneda)
                                                        <span class="ml-1">| {{ $p->banco->moneda }}</span>
                                                    @endif
                                                </span>
                                            </div>

                                        </div>
                                    </td>

                                    <td class="p-3 whitespace-nowrap text-gray-700 dark:text-neutral-200">
                                        {{ $p->nro_transaccion ?? '—' }}
                                    </td>
                                    <td class="p-3 whitespace-nowrap text-gray-700 dark:text-neutral-200">
                                        {{ $fechaTxt }}
                                    </td>

                                    <td class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ number_format($monto, 2, ',', '.') }}
                                    </td>
                                    <td class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ number_format($rendido, 2, ',', '.') }}
                                    </td>

                                    <td
                                        class="p-3 text-right tabular-nums font-semibold
                                        {{ $saldo <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">
                                        {{ number_format($saldo, 2, ',', '.') }}
                                    </td>

                                    <td class="p-2 whitespace-nowrap align-middle">
                                        <div class="flex items-center justify-center gap-2">

                                            {{-- CREAR RENDICIÓN --}}
                                            @if (empty($p->rendicion_id))
                                                <button type="button" wire:click="crearRendicion({{ $p->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="crearRendicion({{ $p->id }})"
                                                    wire:loading.class="cursor-not-allowed opacity-60"
                                                    wire:loading.class.remove="cursor-pointer hover:bg-green-700 hover:border-green-700"
                                                    class="px-3 py-1 rounded border transition cursor-pointer text-sm
                                                    bg-green-600 text-white border-green-600
                                                    hover:bg-green-700 hover:border-green-700
                                                    dark:bg-green-500 dark:border-green-500 dark:hover:bg-green-400 dark:hover:border-green-400
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
                                                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24"
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
                                                    wire:loading.class.remove="cursor-pointer hover:bg-gray-200 hover:border-gray-300"
                                                    class="px-3 py-1 rounded border transition cursor-pointer text-sm
                                                    bg-gray-100 text-gray-700 border-gray-300
                                                    hover:bg-gray-200 hover:border-gray-300
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
                                                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24"
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-500 dark:text-neutral-400">
                                        No hay presupuestos para este agente/moneda con los
                                        filtros actuales.
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

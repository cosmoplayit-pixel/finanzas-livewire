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
                <table class="w-full table-fixed text-sm">
                    <thead
                        class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-200 dark:border-neutral-700">
                        <tr class="text-left text-[11px] uppercase tracking-wider font-semibold">
                            <th class="p-3 text-center w-[3%]">#</th>
                            <th class="p-3 w-[25%]">Banco</th>
                            <th class="p-3 w-[16%]">Fecha</th>
                            <th class="p-3 text-center w-[10%]">Estado</th>
                            <th class="p-3 text-right w-[12%]">Presupuesto</th>
                            <th class="p-3 text-right w-[10%]">Rendido</th>
                            <th class="p-3 text-right w-[11%]">Saldo p/rendir</th>
                            @can('agente_presupuestos.view_detail')
                                <th class="p-3 text-center w-[10%]">Acc.</th>
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
                                    ? \Carbon\Carbon::parse($p->fecha_presupuesto)->format('d/m/Y H:i')
                                    : '—';
                                $isHighlighted =
                                    isset($highlight_presupuesto_id) &&
                                    $highlight_presupuesto_id &&
                                    (int) $p->id === (int) $highlight_presupuesto_id;
                            @endphp

                            <tr wire:key="panel-{{ $rowKey }}-pres-{{ $p->id }}"
                                @if ($isHighlighted) id="presupuesto-panel-target-{{ $p->id }}" @endif
                                class="transition-colors text-sm
                                    {{ $isHighlighted ? 'bg-amber-50 dark:bg-amber-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-neutral-900/40' }}">

                                {{-- # --}}
                                <td
                                    class="p-3 whitespace-nowrap text-gray-500 dark:text-neutral-400 text-center text-xs
                                        {{ $isHighlighted ? 'border-l-4 border-amber-400' : 'border-l-4 border-transparent' }}">
                                    @if ($isHighlighted)
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-white font-bold text-[10px] animate-pulse"
                                            title="Este es el presupuesto de la transacción">{{ $loop->iteration }}</span>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>

                                {{-- BANCO --}}
                                <td class="p-3 align-middle">
                                    <div class="min-w-0 space-y-0.5 leading-snug">
                                        {{-- Nombre banco --}}
                                        <div class="truncate text-sm font-semibold text-gray-900 dark:text-neutral-100"
                                            title="{{ $p->banco?->nombre ?? '—' }}">
                                            {{ $p->banco?->nombre ?? '—' }}
                                        </div>
                                        {{-- Cuenta + moneda + Nro Tx --}}
                                        <div
                                            class="flex items-center gap-1.5 flex-wrap text-[11px] text-gray-500 dark:text-neutral-400">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-3 h-3 shrink-0 text-gray-400 dark:text-neutral-500"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                                <line x1="2" y1="10" x2="22" y2="10" />
                                            </svg>
                                            <span>{{ $p->banco?->numero_cuenta ?? '—' }}</span>
                                            @if ($p->banco?->moneda)
                                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                                <span
                                                    class="font-medium text-gray-600 dark:text-gray-300">{{ $p->banco->moneda }}</span>
                                            @endif
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <span>Tx: {{ $p->nro_transaccion ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- FECHA --}}
                                <td class="p-3 whitespace-nowrap text-sm text-gray-600 dark:text-neutral-300">
                                    {{ $fechaTxt }}
                                </td>

                                {{-- ESTADO --}}
                                <td class="p-3 text-center whitespace-nowrap">
                                    @php
                                        $estadoStr = strtoupper($p->estado ?? '');
                                        $bgEstado = match ($estadoStr) {
                                            'ABIERTO'
                                                => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                            'CERRADO'
                                                => 'bg-gray-100 text-gray-600 dark:bg-neutral-800 dark:text-neutral-400 border border-gray-200 dark:border-neutral-700',
                                            'ANULADO'
                                                => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                            '' => 'bg-gray-50 text-gray-400 dark:bg-neutral-800 dark:text-neutral-500',
                                            default
                                                => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide rounded {{ $bgEstado }}">
                                        {{ $estadoStr ?: '—' }}
                                    </span>
                                </td>

                                {{-- PRESUPUESTO --}}
                                <td
                                    class="p-3 text-right tabular-nums text-sm text-gray-900 dark:text-neutral-100 font-medium">
                                    {{ number_format($monto, 2, ',', '.') }}
                                </td>

                                {{-- RENDIDO --}}
                                <td
                                    class="p-3 text-right tabular-nums text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                                    {{ number_format($rendido, 2, ',', '.') }}
                                </td>

                                {{-- SALDO --}}
                                <td
                                    class="p-3 text-right tabular-nums text-sm font-bold
                                        {{ $saldo <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($saldo, 2, ',', '.') }}
                                </td>

                                {{-- ACCIONES --}}
                                @can('agente_presupuestos.view_detail')
                                    <td class="p-3 whitespace-nowrap align-middle">
                                        <div class="flex items-center justify-center gap-1.5">

                                            {{-- VER MOVIMIENTOS --}}
                                            <button type="button" wire:click="openRendicionEditor({{ $p->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="openRendicionEditor({{ $p->id }})"
                                                wire:loading.class="cursor-not-allowed opacity-60"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 shadow-sm dark:bg-blue-500 dark:border-blue-500 dark:hover:bg-blue-400"
                                                title="Ver movimientos">
                                                <span wire:loading.remove
                                                    wire:target="openRendicionEditor({{ $p->id }})"
                                                    class="inline-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                                                        <path d="M21 12H17a2 2 0 0 0 0 4h4v-4Z" />
                                                    </svg>
                                                </span>
                                                <span wire:loading wire:target="openRendicionEditor({{ $p->id }})"
                                                    class="inline-flex">
                                                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 12a9 9 0 1 1-3-6.7" />
                                                    </svg>
                                                </span>
                                            </button>

                                            {{-- VER COMPROBANTE (imagen o PDF) --}}
                                            @php
                                                $archivo = $p->foto_comprobante ?? null;
                                                $esPdf =
                                                    $archivo &&
                                                    strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf';
                                            @endphp
                                            @if ($archivo)
                                                <a href="{{ asset('storage/' . $archivo) }}" target="_blank"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer
                                                        {{ $esPdf
                                                            ? 'bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm'
                                                            : 'bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm' }}"
                                                    title="{{ $esPdf ? 'Ver PDF' : 'Ver imagen' }}">
                                                    @if ($esPdf)
                                                        {{-- Icono PDF --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                            <polyline points="14 2 14 8 20 8" />
                                                            <line x1="9" y1="13" x2="15"
                                                                y2="13" />
                                                            <line x1="9" y1="17" x2="15"
                                                                y2="17" />
                                                            <line x1="9" y1="9" x2="11"
                                                                y2="9" />
                                                        </svg>
                                                    @else
                                                        {{-- Icono imagen --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18"
                                                                rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <polyline points="21 15 16 10 5 21" />
                                                        </svg>
                                                    @endif
                                                </a>
                                            @else
                                                {{-- Sin comprobante: bloqueado --}}
                                                <span
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                    title="Sin comprobante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"
                                                            ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <polyline points="21 15 16 10 5 21" />
                                                    </svg>
                                                </span>
                                            @endif

                                            {{-- ELIMINAR --}}
                                            @php
                                                $hasMovimientos = ($p->movimientos_count ?? 0) > 0 || $rendido > 0;
                                            @endphp
                                            <button type="button" {{ $hasMovimientos ? 'disabled' : '' }}
                                                @if (!$hasMovimientos) wire:click="abrirEliminarRendicionModal({{ $p->id }})" @endif
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all
                                                           {{ $hasMovimientos
                                                               ? 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                                               : 'bg-white text-red-600 border-red-300 cursor-pointer hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm' }}"
                                                title="{{ $hasMovimientos ? 'Tiene movimientos: no se puede eliminar' : 'Eliminar presupuesto' }}">
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
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center bg-white dark:bg-neutral-900/10">
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                        <svg class="w-10 h-10 mb-3 opacity-20" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
        </td>
    </tr>
@endif

@php
    $presupuestos = $panelData[$rowKey] ?? [];
@endphp

<div class="space-y-3">

    @forelse($presupuestos as $p)
        @php
            $saldo = (float) ($p->saldo_por_rendir ?? 0);
            $rendido = (float) ($p->rendido_total ?? 0);
            $monto = (float) ($p->monto ?? 0);

            $fechaTxt = $p->fecha_presupuesto ? \Carbon\Carbon::parse($p->fecha_presupuesto)->format('d/m/Y H:i') : '—';

            $bancoNombre = $p->banco?->nombre ?? '—';
            $cuenta = $p->banco?->numero_cuenta ?? '—';
            $monedaBanco = $p->banco?->moneda ?? null;
            $nroTx = $p->nro_transaccion ?? '—';
        @endphp

        @php
            $isHighlighted =
                isset($highlight_presupuesto_id) &&
                $highlight_presupuesto_id &&
                (int) $p->id === (int) $highlight_presupuesto_id;
        @endphp
        <div wire:key="panel-mobile-{{ $rowKey }}-pres-{{ $p->id }}"
            @if ($isHighlighted) id="presupuesto-mob-panel-target-{{ $p->id }}" @endif
            class="rounded-xl border {{ $isHighlighted ? 'border-amber-400' : 'border-gray-200 dark:border-neutral-700' }}
            {{ $isHighlighted ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-white dark:bg-neutral-900/30' }} px-3 py-3">

            <div class="flex gap-3">



                <div class="flex-1 min-w-0">

                    {{-- HEADER (sin truncate del banco) --}}
                    <div class="flex items-start gap-2">
                        <div class="font-semibold text-gray-900 dark:text-neutral-100 leading-snug">
                            #{{ $loop->iteration }} {{ $bancoNombre }}
                        </div>

                        @can('agente_presupuestos.view_detail')
                            <div class="ml-auto shrink-0">
                                {{-- Un solo botón: presupuesto ya es rendición --}}
                                <button type="button" wire:click="openRendicionEditor({{ $p->id }})"
                                    wire:loading.attr="disabled" wire:target="openRendicionEditor({{ $p->id }})"
                                    wire:loading.class="opacity-60 cursor-not-allowed"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-md
                                    text-xs font-semibold transition
                                    bg-emerald-600 text-white hover:bg-emerald-700
                                    dark:bg-emerald-500 dark:hover:bg-emerald-400"
                                    title="Ver movimientos">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    Movimientos
                                </button>
                            </div>
                        @endcan
                    </div>

                    {{-- meta --}}
                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400 leading-snug">
                        <div>
                            {{ $cuenta }}@if ($monedaBanco)
                                <span class="mx-1">|</span>{{ $monedaBanco }}
                            @endif
                        </div>
                        <div class="flex items-center mt-0.5">
                            <span>Tx: {{ $nroTx }} <span class="mx-1">·</span> {{ $fechaTxt }}</span>
                            @if ($p->foto_comprobante)
                                @php
                                    $ext = strtolower(pathinfo($p->foto_comprobante, PATHINFO_EXTENSION));
                                    $esPdfMobile = $ext === 'pdf';
                                @endphp
                                @if ($esPdfMobile)
                                    <a href="{{ asset('storage/' . $p->foto_comprobante) }}" target="_blank"
                                        class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-2 transition-colors"
                                        title="Ver PDF">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                        </svg>
                                    </a>
                                @else
                                    <button type="button"
                                        wire:click="openFotoComprobante('{{ asset('storage/' . $p->foto_comprobante) }}')"
                                        class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-2 transition-colors"
                                        title="Ver Imagen">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6.75v10.5a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </button>
                                @endif
                            @endif
                        </div>
                        <div class="mt-1.5">
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
                                class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide rounded {{ $bgEstado }}">
                                {{ $estadoStr ?: '—' }}
                            </span>
                        </div>
                    </div>

                    {{-- KPI SECTION (pro) --}}
                    <div class="mt-3 pt-3 border-t border-gray-200/70 dark:border-neutral-700/70 space-y-2">

                        <div class="flex items-baseline justify-between">
                            <span class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Presup.
                            </span>
                            <span class="text-sm font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ number_format($monto, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-baseline justify-between">
                            <span class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Rendido
                            </span>
                            <span class="text-sm font-extrabold tabular-nums text-emerald-600 dark:text-emerald-300">
                                {{ number_format($rendido, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-baseline justify-between">
                            <span class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Saldo
                            </span>
                            <span
                                class="text-sm font-extrabold tabular-nums
                        {{ $saldo <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                {{ number_format($saldo, 2, ',', '.') }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        @empty
            <div
                class="rounded-xl border border-gray-200 dark:border-neutral-700
                bg-white dark:bg-neutral-900/30 p-4 text-center text-sm text-gray-500 dark:text-neutral-400">
                No hay presupuestos para este agente/moneda con los filtros actuales.
            </div>
        @endforelse

    </div>

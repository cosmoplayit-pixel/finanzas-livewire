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

            $tieneRendicion = !empty($p->rendicion_id);
        @endphp

        <div wire:key="panel-mobile-{{ $rowKey }}-pres-{{ $p->id }}"
            class="rounded-xl border border-gray-200 dark:border-neutral-700
            bg-white dark:bg-neutral-900/30 px-3 py-3">

            <div class="flex gap-3">



                <div class="flex-1 min-w-0">

                    {{-- HEADER (sin truncate del banco) --}}
                    <div class="flex items-start gap-2">
                        <div class="font-semibold text-gray-900 dark:text-neutral-100 leading-snug">
                            #{{ $loop->iteration }} {{ $bancoNombre }}
                        </div>

                        <div class="ml-auto shrink-0">
                            @if (empty($p->rendicion_id))
                                @can('agente_rendicion.create')
                                <button type="button" wire:click="crearRendicion({{ $p->id }})"
                                    wire:loading.attr="disabled" wire:target="crearRendicion({{ $p->id }})"
                                    wire:loading.class="opacity-60 cursor-not-allowed"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-md
                                    text-xs font-semibold transition
                                    bg-emerald-600 text-white hover:bg-emerald-700
                                    dark:bg-emerald-500 dark:hover:bg-emerald-400"
                                    title="Crear rendición">

                                    {{-- ICONO CREAR --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>

                                </button>
                                @endcan
                            @else
                                @can('agente_rendicion.view')
                                <button type="button" wire:click="openRendicionEditor({{ $p->rendicion_id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="openRendicionEditor({{ $p->rendicion_id }})"
                                    wire:loading.class="opacity-60 cursor-not-allowed"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-md
                                    text-xs font-semibold transition
                                    bg-gray-100 text-gray-700 hover:bg-gray-200
                                    dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                    title="Ver rendición">

                                    {{-- ICONO VER --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.062 12.348a1 1 0 010-.696C3.423 7.51 7.36 4.5 12 4.5
                                        c4.638 0 8.576 3.01 9.938 7.152a1 1 0 010 .696
                                        C20.576 16.49 16.638 19.5 12 19.5
                                        c-4.64 0-8.577-3.01-9.938-7.152z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>


                                </button>
                                @endcan
                            @endif

                        </div>
                    </div>

                    {{-- meta --}}
                    <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400 leading-snug">
                        <div>
                            {{ $cuenta }}@if ($monedaBanco)
                                <span class="mx-1">|</span>{{ $monedaBanco }}
                            @endif
                        </div>
                        <div>
                            Tx: {{ $nroTx }} <span class="mx-1">·</span> {{ $fechaTxt }}
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

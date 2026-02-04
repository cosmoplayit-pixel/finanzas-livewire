{{-- MOBILE --}}
<div class="md:hidden space-y-3">
    @forelse($boletas as $bg)
        @php
            $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
            $rest = max(0, (float) $bg->retencion - $totalDev);
            $devuelta = $totalDev >= (float) $bg->retencion;
        @endphp

        <div x-data="{ open: false }"
            class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

            {{-- HEADER --}}
            <button type="button" @click="open = !open"
                class="w-full px-4 py-3 flex items-start justify-between gap-3 text-left
                       hover:bg-gray-50 dark:hover:bg-neutral-900 transition">

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-extrabold text-gray-900 dark:text-neutral-100">
                            #{{ $bg->id }}
                        </div>

                        @if ($devuelta)
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                Devuelta
                            </span>
                        @else
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">
                                Abierta
                            </span>
                        @endif
                    </div>

                    <div class="mt-1 truncate text-sm font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $bg->proyecto?->nombre ?? '—' }}
                    </div>

                    <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-neutral-400">
                        Nro: {{ $bg->nro_boleta ?? '—' }} • {{ $bg->tipo ?? '—' }}
                    </div>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Devuelto</div>
                    <div class="text-sm font-bold tabular-nums text-gray-900 dark:text-neutral-100">
                        {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                        {{ number_format((float) $totalDev, 2, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                        Rest: {{ number_format((float) $rest, 2, ',', '.') }}
                    </div>
                </div>
            </button>

            {{-- BODY --}}
            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="px-4 pb-4 pt-2 space-y-3">

                {{-- PROYECTO --}}
                <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                    <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                        Proyecto
                    </div>

                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $bg->proyecto?->nombre ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                        Entidad: {{ $bg->entidad?->nombre ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Agente: {{ $bg->agenteServicio?->nombre ?? '—' }}
                    </div>
                </div>

                {{-- BANCO --}}
                <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                    <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                        Banco egreso
                    </div>

                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $bg->bancoEgreso?->nombre ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                        Cuenta: {{ $bg->bancoEgreso?->numero_cuenta ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Moneda: {{ $bg->bancoEgreso?->moneda ?? ($bg->moneda ?? '—') }}
                    </div>
                </div>

                {{-- BOLETA --}}
                <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                    <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                        Boleta
                    </div>

                    <div class="space-y-2 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Nro</div>
                            <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $bg->nro_boleta ?? '—' }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Emisión</div>
                                <div class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ $bg->fecha_emision?->format('Y-m-d') ?? '—' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Vencimiento</div>
                                <div class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ $bg->fecha_vencimiento?->format('Y-m-d') ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            Total egreso:
                            <span class="font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                {{ number_format((float) $bg->total, 2, ',', '.') }}
                            </span>
                        </div>

                        @if (!empty($bg->observacion))
                            <div class="text-xs text-gray-500 dark:text-neutral-400 break-words">
                                {{ $bg->observacion }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- DEVOLUCIONES --}}
                <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-neutral-800">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                            Devoluciones ({{ $bg->devoluciones?->count() ?? 0 }})
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            Total: {{ number_format((float) $totalDev, 2, ',', '.') }} •
                            Restante: {{ number_format((float) $rest, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="p-3 space-y-2">
                        @forelse(($bg->devoluciones ?? collect()) as $dv)
                            <div
                                class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold truncate text-gray-900 dark:text-neutral-100">
                                            {{ $dv->banco?->nombre ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                            {{ $dv->banco?->numero_cuenta ?? '—' }}
                                            ({{ $dv->banco?->moneda ?? '—' }})
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                                            {{ $dv->fecha_devolucion?->format('Y-m-d H:i') ?? '—' }}
                                            • Op: {{ $dv->nro_transaccion ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Monto</div>
                                        <div
                                            class="text-sm font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                            {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                        </div>

                                        {{-- IMPORTANTE: stop para que no haga toggle del card --}}
                                        <button type="button" @click.stop
                                            class="mt-2 inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-red-300 text-red-700
                                                   hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition"
                                            wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }})">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-sm text-gray-500 dark:text-neutral-400 py-3">
                                No hay devoluciones registradas.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ACCIONES --}}
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click.stop wire:click="openDevolucion({{ $bg->id }})"
                        @disabled($rest <= 0)
                        class="col-span-2 px-4 py-2 rounded-lg border text-sm font-semibold transition
                            {{ $rest <= 0
                                ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}">
                        Devolver
                    </button>
                </div>

            </div>
        </div>

    @empty
        <div class="p-4 text-center text-gray-500 dark:text-neutral-400">
            Sin resultados.
        </div>
    @endforelse
</div>

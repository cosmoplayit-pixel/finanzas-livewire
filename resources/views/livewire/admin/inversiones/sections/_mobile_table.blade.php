{{-- MOBILE (<= md): cards --}}
<div class="md:hidden space-y-3">
    @forelse ($inversiones as $inv)
        <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $inv->codigo }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                            {{ $inv->nombre_completo }}
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-neutral-300">
                            <span class="px-2 py-1 rounded-lg border border-gray-200 dark:border-neutral-700">
                                {{ $inv->tipo }}
                            </span>
                            <span class="px-2 py-1 rounded-lg border border-gray-200 dark:border-neutral-700">
                                {{ $inv->moneda }}
                            </span>

                            @if ($inv->estado === 'ACTIVA')
                                <span
                                    class="px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700
                                             dark:bg-emerald-900/30 dark:text-emerald-300">
                                    ACTIVA
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded-lg bg-gray-200 text-gray-700
                                             dark:bg-neutral-700 dark:text-neutral-200">
                                    CERRADA
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Capital
                        </div>
                        <div class="text-base font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) $inv->capital_actual, 2, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-neutral-300">
                            {{ number_format((float) $inv->porcentaje_utilidad, 4, ',', '.') }}%
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">

                    @can('inversiones.movimiento')
                        <button type="button" wire:click="$dispatch('openMovimiento', {{ $inv->id }})"
                            class="px-3 py-2 rounded-lg border text-xs
                                   border-gray-300 text-gray-700 hover:bg-gray-50
                                   dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                            Movimiento
                        </button>
                    @endcan

                    @can('inversiones.pagar')
                        <button type="button" wire:click="$dispatch('openPagarUtilidad', {{ $inv->id }})"
                            class="px-3 py-2 rounded-lg text-xs bg-emerald-600 text-white hover:opacity-90">
                            Pagar utilidad
                        </button>
                    @endcan

                </div>
            </div>
        </div>

    @empty
        <div class="p-6 text-center text-gray-500 dark:text-neutral-400">
            No hay inversiones registradas.
        </div>
    @endforelse
</div>

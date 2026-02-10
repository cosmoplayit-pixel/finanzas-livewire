{{-- resources/views/livewire/admin/inversiones/modals/_modal_movimiento.blade.php --}}

<div>
    <x-ui.modal wire:key="inversion-movimientos-{{ $openMovimientosModal ? 'open' : 'closed' }}"
        model="openMovimientosModal" title="Movimientos de inversión" maxWidth="sm:max-w-2xl md:max-w-6xl"
        onClose="closeMovimientos">
        <div class="space-y-3">

            {{-- HEADER / RESUMEN --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                        {{-- IZQUIERDA --}}
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $inversion?->nombre_completo ?? '—' }}
                            </div>

                            <div
                                class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
                                <span class="font-mono">{{ $inversion?->codigo ?? '—' }}</span>
                                <span class="text-gray-300 dark:text-neutral-600">•</span>
                                <span>{{ $inversion?->tipo ?? '—' }}</span>
                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 1v22" />
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    <span class="font-semibold">{{ $inversion?->moneda ?? '—' }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
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
                                    <span class="truncate max-w-[280px]">
                                        {{ $inversion?->banco?->nombre ?? 'Sin banco' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- DERECHA: BOTONES (arriba) --}}
                        <div class="shrink-0 w-full sm:w-auto">
                            <div class="w-full sm:w-auto flex flex-wrap justify-end gap-2">

                                {{-- Pagar utilidad (abre componente pagar-utilidad-modal) --}}
                                <button type="button" @disabled(!$inversion)
                                    wire:click="$dispatch('openPagarUtilidad', { inversionId: {{ $inversion?->id ?? 0 }} })"
                                    class="h-9 px-3  rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                           bg-emerald-600 text-white hover:opacity-90
                                           disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Pagar utilidad">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 1v22" />
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    Pagar utilidad
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- CARDS (aquí va Capital actual, como pediste) --}}
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Capital actual</div>
                            <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                @if (($inversion?->moneda ?? 'BOB') === 'USD')
                                    $ {{ number_format((float) ($inversion?->capital_actual ?? 0), 2, ',', '.') }}
                                @else
                                    {{ number_format((float) ($inversion?->capital_actual ?? 0), 2, ',', '.') }} Bs
                                @endif
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Fecha inicio</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $inversion?->fecha_inicio?->format('d/m/Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Vencimiento</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $inversion?->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">% utilidad</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 tabular-nums">
                                {{ number_format((float) ($inversion?->porcentaje_utilidad ?? 0), 4, ',', '.') }}%
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- TABLA MOVIMIENTOS --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[1000px]">
                        <thead class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                            <tr
                                class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                <th class="p-3 w-[50px]">Nro</th>
                                <th class="p-3 w-[50px]">Fecha</th>
                                <th class="p-3 w-[50px]">Descripción</th>
                                <th class="p-3 w-[100px]">Capital</th>
                                <th class="p-3 w-[100px]">Utilidad</th>
                                <th class="p-3 w-[100px]">% Util.</th>
                                <th class="p-3 w-[100px]">Banco</th>
                                <th class="p-3 w-[100px]">Fecha pago</th>
                                <th class="p-3 w-[100px]">Imagen</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($movimientos as $m)
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/50">
                                    <td class="p-3  font-mono text-gray-900 dark:text-neutral-100">
                                        {{ $m->nro }}
                                    </td>

                                    <td class="p-3 text-gray-900 dark:text-neutral-100">
                                        {{ $m->fecha?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="p-3">
                                        <div class="font-medium text-gray-900 dark:text-neutral-100">
                                            {{ $m->descripcion ?? $m->tipo }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                                            {{ $m->tipo }}
                                        </div>
                                    </td>

                                    {{-- Capital (+ / -) --}}
                                    <td class="p-3 font-semibold">
                                        @php $cap = (float)($m->monto_capital ?? 0); @endphp
                                        <span
                                            class="{{ $cap < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-neutral-100' }}">
                                            @if (($inversion?->moneda ?? 'BOB') === 'USD')
                                                {{ $cap < 0 ? '-' : '' }}$ {{ number_format(abs($cap), 2, ',', '.') }}
                                            @else
                                                {{ number_format($cap, 2, ',', '.') }} Bs
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Utilidad --}}
                                    <td class="p-3">
                                        @php $ut = (float)($m->monto_utilidad ?? 0); @endphp
                                        @if (($inversion?->moneda ?? 'BOB') === 'USD')
                                            $ {{ number_format($ut, 2, ',', '.') }}
                                        @else
                                            {{ number_format($ut, 2, ',', '.') }} Bs
                                        @endif
                                    </td>

                                    <td class="p-3">
                                        {{ $m->porcentaje_utilidad !== null ? number_format((float) $m->porcentaje_utilidad, 4, ',', '.') . '%' : '—' }}
                                    </td>

                                    <td class="p-3">
                                        <div class="text-gray-900 dark:text-neutral-100 truncate">
                                            {{ $m->banco?->nombre ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="p-3 text-gray-900 dark:text-neutral-100">
                                        {{ $m->fecha_pago?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    {{-- Imagen del movimiento --}}
                                    <td class="p-3">
                                        @php $imgPath = $m->imagen ?? null; @endphp

                                        @if ($imgPath)
                                            <button type="button" wire:click="verFotoMovimiento({{ $m->id }})"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border
                                                       border-gray-300 text-gray-700 hover:bg-gray-100
                                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                                title="Ver imagen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2" />
                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                    <path d="M21 15l-5-5L5 21" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-neutral-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-6 text-gray-500 dark:text-neutral-400">
                                        No hay movimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeMovimientos"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                           border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                           hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- VISOR FOTO --}}
    <div wire:key="foto-inv-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>
</div>

    {{-- ===================== MODAL HISTORIAL DE BAJAS GENERAL ===================== --}}
    <x-ui.modal wire:key="bajas-historial-modal" model="bajasModal"
        title="Historial General de Herramientas Dadas de Baja {{ $historialBajas ? '(' . $historialBajas->total() . ')' : '' }}"
        maxWidth="sm:max-w-7xl" onClose="closeBajasHistorial">
        <div class="px-2" wire:loading.class="opacity-50" wire:target="previousPage, nextPage, gotoPage">
            @if ($historialBajas && $historialBajas->count() > 0)
                {{-- MOBILE CARDS --}}
                <div class="md:hidden space-y-2.5 mb-4">
                    @foreach ($historialBajas as $bh)
                        <div
                            class="p-3 rounded-xl border border-gray-100 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 shadow-sm relative overflow-hidden">
                            {{-- Cantidad Badge (Float) --}}
                            <div class="absolute top-0 right-0 p-2.5">
                                <span
                                    class="inline-flex items-center justify-center h-6 px-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-black text-[10px] border border-red-100 dark:border-red-900/30">
                                    -{{ $bh->cantidad }}
                                </span>
                            </div>

                            <div class="flex gap-3 items-start mb-2.5">
                                <div class="shrink-0 flex flex-col gap-1.5">
                                    @if ($bh->herramienta && $bh->herramienta->imagen)
                                        <img src="{{ Storage::url($bh->herramienta->imagen) }}"
                                            class="size-11 object-cover rounded-lg border border-gray-100 dark:border-neutral-800 shadow-sm"
                                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($bh->herramienta->imagen) }}' }))">
                                    @else
                                        <div
                                            class="size-11 rounded-lg bg-gray-50 dark:bg-neutral-800 flex items-center justify-center text-gray-300">
                                            <svg class="size-5 opacity-30" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    @if ($bh->imagen)
                                        <div class="relative w-max">
                                            <img src="{{ Storage::url($bh->imagen) }}"
                                                class="size-11 object-cover rounded-lg border-2 border-red-200 dark:border-red-900/40 ring-4 ring-red-500/5 shadow-lg"
                                                onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($bh->imagen) }}' }))">
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <div
                                        class="font-black text-gray-900 dark:text-neutral-100 text-[13px] leading-tight mb-0.5">
                                        {{ $bh->herramienta?->nombre ?? 'Herramienta Eliminada' }}
                                    </div>
                                    @if ($bh->herramienta)
                                        <div
                                            class="text-[10px] text-gray-400 font-mono uppercase tracking-widest leading-none">
                                            {{ $bh->herramienta->codigo }}</div>

                                        {{-- Serie en mobile --}}
                                        @if ($bh->detalles_series && $bh->detalles_series->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-1 leading-none text-left">
                                                @foreach ($bh->detalles_series as $s)
                                                    <span
                                                        class="inline-flex items-center px-1 py-0.5 rounded text-[8px] font-mono bg-gray-50 dark:bg-neutral-800 text-gray-500 border border-gray-100 dark:border-neutral-700">
                                                        S/N: {{ $s->serie }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif

                                    <div class="mt-2 text-left">
                                        @if ($bh->prestamo_id && $bh->prestamo)
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[9px] font-black uppercase border border-indigo-100 dark:border-indigo-800/50">
                                                PRÉSTAM: {{ $bh->prestamo->nro_prestamo }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-gray-50 dark:bg-neutral-800 text-gray-500 dark:text-neutral-400 text-[9px] font-black uppercase border border-gray-100 dark:border-neutral-700">
                                                DEL ALMACÉN
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($bh->observaciones)
                                <div
                                    class="bg-gray-50/50 dark:bg-neutral-800/30 px-2.5 py-2 rounded-lg mb-2.5 border border-gray-100 dark:border-neutral-800/50">
                                    <p class="text-[11px] text-gray-600 dark:text-neutral-300 italic leading-snug">
                                        {{ $bh->observaciones }}</p>
                                </div>
                            @endif

                            <div
                                class="flex items-center justify-between pt-2.5 border-t border-gray-50 dark:border-neutral-800/60">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="size-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-[9px] font-black">
                                        {{ substr($bh->user?->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-bold text-gray-800 dark:text-neutral-200 leading-none">{{ $bh->user?->name ?? 'Sistema' }}</span>
                                        <span
                                            class="text-[9px] text-gray-400 mt-0.5 uppercase tracking-tighter">{{ $bh->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                @if ($bh->prestamo_id && $bh->prestamo)
                                    <a href="{{ route('prestamos_herramientas') . '?ver=' . $bh->prestamo->nro_prestamo . '&destacar=' . $bh->herramienta_id }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center size-8 rounded-lg bg-indigo-600 text-white shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition active:scale-95 cursor-pointer">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div
                    class="hidden md:block rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-neutral-400">
                            <thead
                                class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-300 font-medium">
                                <tr>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-16 text-center">Ficha</th>
                                    <th
                                        class="px-4 py-3 border-b dark:border-neutral-700 w-16 text-center text-red-500">
                                        Baja</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-1/4">Herramienta</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 text-center w-20">Cantidad
                                    </th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-1/4">Motivo / Observación
                                    </th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 text-center">Origen</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700">Responsable</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($historialBajas as $bh)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition">
                                        {{-- Imagen Herramienta --}}
                                        <td class="px-4 py-1.5 text-center">
                                            @if ($bh->herramienta && $bh->herramienta->imagen)
                                                <img src="{{ Storage::url($bh->herramienta->imagen) }}"
                                                    class="w-10 h-10 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700 inline-block cursor-pointer"
                                                    alt="Img"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($bh->herramienta->imagen) }}' }))">
                                            @else
                                                <div
                                                    class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-neutral-800 inline-flex items-center justify-center text-gray-300 dark:text-neutral-700 border border-gray-100 dark:border-neutral-800">
                                                    <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </td>
                                        {{-- Imagen Evidencia de la Baja --}}
                                        <td class="px-4 py-1.5 text-center">
                                            @if ($bh->imagen)
                                                <img src="{{ Storage::url($bh->imagen) }}"
                                                    class="w-10 h-10 object-cover rounded-lg shadow-sm border border-red-200 dark:border-red-900/50 inline-block cursor-pointer ring-2 ring-red-500/10"
                                                    alt="Baja"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($bh->imagen) }}' }))">
                                            @else
                                                <span class="text-[10px] text-gray-300 italic">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-1.5">
                                            @if ($bh->herramienta)
                                                <div class="font-bold text-gray-900 dark:text-neutral-100">
                                                    {{ $bh->herramienta->nombre }}</div>
                                                <div class="text-[10px] text-gray-500 font-mono">
                                                    {{ $bh->herramienta->codigo }}</div>

                                                {{-- Números de Serie --}}
                                                @if ($bh->detalles_series && $bh->detalles_series->count() > 0)
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach ($bh->detalles_series as $s)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-neutral-400 border border-gray-200 dark:border-neutral-700">
                                                                S/N: {{ $s->serie }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @elseif($bh->series)
                                                    {{-- Fallback si solo existe el string en la tabla --}}
                                                    <div class="mt-1">
                                                        <span
                                                            class="text-[9px] font-mono text-gray-500 bg-gray-50 dark:bg-neutral-800/50 px-1.5 py-0.5 rounded border border-gray-100 dark:border-neutral-800">
                                                            S/N: {{ $bh->series }}
                                                        </span>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-gray-400 italic">Herramienta Eliminada</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-1.5 text-center whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-black text-xs">
                                                -{{ $bh->cantidad }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-1.5">
                                            <p class="text-xs text-gray-700 dark:text-neutral-300">
                                                {{ $bh->observaciones ?? '-' }}</p>
                                        </td>
                                        {{-- Origen --}}
                                        <td class="px-4 py-1.5 text-center">
                                            @if ($bh->prestamo_id && $bh->prestamo)
                                                <div class="inline-flex flex-col items-center gap-0.5">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-[9px] font-black uppercase ring-1 ring-indigo-200 dark:ring-indigo-800/50">
                                                        Préstamo
                                                    </span>
                                                    <span
                                                        class="font-mono text-[9px] text-indigo-500 dark:text-indigo-400 font-bold">{{ $bh->prestamo->nro_prestamo }}</span>
                                                </div>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-neutral-800 text-gray-500 dark:text-neutral-400 text-[9px] font-black uppercase ring-1 ring-gray-200 dark:ring-neutral-700">
                                                    Herramientas
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-1.5">
                                            <div class="text-xs text-gray-800 dark:text-neutral-200 font-bold">
                                                {{ $bh->user?->name ?? 'Sistema' }}</div>
                                            <div class="text-[10px] text-gray-500">
                                                {{ $bh->created_at->format('d/m/Y H:i') }}</div>
                                        </td>

                                        {{-- Acción: ver préstamo asociado --}}
                                        <td class="px-2 py-1.5 text-center">
                                            @if ($bh->prestamo_id && $bh->prestamo)
                                                <a href="{{ route('prestamos_herramientas') . '?ver=' . $bh->prestamo->nro_prestamo . '&destacar=' . $bh->herramienta_id }}"
                                                    target="_blank"
                                                    title="Ver préstamo {{ $bh->prestamo->nro_prestamo }}"
                                                    class="inline-flex items-center justify-center size-7 rounded-lg border border-indigo-200 dark:border-indigo-800/50 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition cursor-pointer">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            @else
                                                <span title="Baja directa desde Herramientas — no aplica"
                                                    class="inline-flex items-center justify-center size-7 rounded-lg border border-gray-100 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900 text-gray-300 dark:text-neutral-700 cursor-not-allowed">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Paginación Historial --}}
                @if ($historialBajas && $historialBajas->hasPages())
                    <div class="mt-1 pt-1">
                        {{ $historialBajas->links() }}
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                    <div
                        class="w-16 h-16 bg-gray-50 dark:bg-neutral-800 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-neutral-100">Sin registros</h3>
                    <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1 max-w-sm">Todavía no se ha dado de baja
                        ninguna herramienta del almacén en esta cuenta.</p>
                </div>
            @endif
        </div>

        @slot('footer')
            <div class="flex justify-end">
                <button type="button" wire:click="closeBajasHistorial"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

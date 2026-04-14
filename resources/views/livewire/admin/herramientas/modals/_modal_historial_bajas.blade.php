    {{-- ===================== MODAL HISTORIAL DE BAJAS GENERAL ===================== --}}
    <x-ui.modal wire:key="bajas-historial-modal" model="bajasModal"
        title="Historial General de Herramientas Dadas de Baja {{ $historialBajas ? '(' . $historialBajas->total() . ')' : '' }}"
        maxWidth="sm:max-w-7xl" onClose="closeBajasHistorial">
        <div class="px-2" wire:loading.class="opacity-50" wire:target="previousPage, nextPage, gotoPage">
            @if ($historialBajas && $historialBajas->count() > 0)
                <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-neutral-400">
                            <thead
                                class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-300 font-medium">
                                <tr>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-16 text-center">Ficha</th>
                                    <th
                                        class="px-4 py-3 border-b dark:border-neutral-700 w-16 text-center text-red-500">
                                        Baja</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-1/3">Herramienta</th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 text-center w-20">Cantidad
                                    </th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700 w-1/3">Motivo / Observación
                                    </th>
                                    <th class="px-4 py-3 border-b dark:border-neutral-700">Responsable</th>
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
                                                    <svg class="w-5 h-5 opacity-40" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
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
                                        <td class="px-4 py-1.5">
                                            <div class="text-xs text-gray-800 dark:text-neutral-200 font-bold">
                                                {{ $bh->user?->name ?? 'Sistema' }}</div>
                                            <div class="text-[10px] text-gray-500">
                                                {{ $bh->created_at->format('d/m/Y H:i') }}</div>
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
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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


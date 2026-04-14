{{-- ===================== MODAL DAR DE BAJA ===================== --}}
<x-ui.modal wire:key="prestamo-baja-{{ $openModalBaja ? 'open' : 'closed' }}" model="openModalBaja"
    title="Registrar Baja de Inventario" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="$set('openModalBaja', false)">

    <div class="space-y-4">

        {{-- ── Aviso de acción destructiva ── --}}
        <div
            class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/40 rounded-xl">
            <svg class="size-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            <div>
                <p class="text-xs font-black text-red-800 dark:text-red-300 uppercase tracking-tight">Acción
                    irreversible</p>
                <p class="text-xs text-red-700/80 dark:text-red-400/80 mt-0.5 leading-relaxed">
                    Los equipos dados de baja se descontarán <strong>permanentemente</strong> del inventario.
                    Úselo solo para equipos <strong>perdidos, robados o destruidos</strong> que no regresarán.
                </p>
            </div>
        </div>

        {{-- ── Contexto del préstamo ── --}}
        @if (!empty($prestamoNroParaBaja))
            <div
                class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-neutral-800/60 border border-gray-200 dark:border-neutral-700 rounded-xl">
                <svg class="size-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-xs text-gray-500 dark:text-neutral-400">Préstamo:</span>
                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase">{{ $prestamoNroParaBaja }}</span>
            </div>
        @endif

        {{-- ── Lista de equipos pendientes ── --}}
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

            {{-- Cabecera --}}
            <div
                class="hidden sm:grid grid-cols-[1fr_60px_80px] gap-x-3 items-center px-3 py-2 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-200 dark:border-neutral-700">
                <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider">Equipo</span>
                <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider text-center">Pend.</span>
                <span class="text-[9px] font-black uppercase text-red-500 tracking-wider text-center">Cant. Baja</span>
            </div>

            {{-- Filas --}}
            <div class="max-h-[42vh] overflow-y-auto divide-y divide-gray-100 dark:divide-neutral-800">
                @foreach ($items_baja as $id => $item)
                    <div
                        x-data="{ qty: @entangle('items_baja.' . $id . '.cantidad_baja').live }"
                        :class="parseInt(qty || 0) > 0
                            ? 'bg-red-50/40 dark:bg-red-900/5'
                            : 'bg-white dark:bg-neutral-900'">

                        {{-- Fila principal --}}
                        <div class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_60px_80px] gap-x-3 items-center px-3 py-2.5">

                            {{-- Thumbnail + nombre + código --}}
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if (!empty($item['imagen']))
                                    <img src="{{ asset('storage/' . $item['imagen']) }}"
                                        class="size-9 rounded-lg object-cover border border-gray-200 dark:border-neutral-700 shrink-0 shadow-sm"
                                        :class="parseInt(qty || 0) > 0 ? 'border-red-200 dark:border-red-800/40 ring-1 ring-red-300/40' : ''">
                                @else
                                    <div
                                        class="size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 flex items-center justify-center shrink-0"
                                        :class="parseInt(qty || 0) > 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40' : ''">
                                        <svg class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs font-bold truncate leading-tight"
                                        :class="parseInt(qty || 0) > 0
                                            ? 'text-red-700 dark:text-red-300'
                                            : 'text-gray-900 dark:text-white'"
                                        title="{{ $item['herramienta_nombre'] }}">{{ $item['herramienta_nombre'] }}</div>
                                    <div class="text-[9px] font-mono text-gray-400 dark:text-neutral-500 leading-none mt-0.5">
                                        {{ $item['codigo'] ?? '—' }}</div>
                                </div>
                            </div>

                            {{-- Pendiente --}}
                            <div class="text-center">
                                <span
                                    class="inline-block text-sm font-black text-gray-700 dark:text-neutral-300 bg-gray-100 dark:bg-neutral-800 rounded-md px-2 py-0.5 min-w-[36px] text-center">
                                    {{ $item['cantidad_pendiente'] }}
                                </span>
                            </div>

                            {{-- Cantidad baja --}}
                            <div>
                                <input type="number"
                                    wire:model.live="items_baja.{{ $id }}.cantidad_baja"
                                    min="0" max="{{ $item['cantidad_pendiente'] }}"
                                    class="w-full rounded-lg border px-1 py-1.5 text-sm font-black text-center transition focus:ring-2"
                                    :class="parseInt(qty || 0) > 0
                                        ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 focus:ring-red-500/30 focus:border-red-400'
                                        : 'bg-gray-50 dark:bg-neutral-800 border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-300 focus:ring-gray-400/20'">
                                @error("items_baja.{$id}.cantidad_baja")
                                    <span class="text-[9px] text-red-500 italic block mt-0.5 text-center">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Sub-fila: motivo (aparece cuando qty > 0) --}}
                        <div x-show="parseInt(qty || 0) > 0" x-collapse>
                            <div class="px-3 pb-3">
                                <label
                                    class="block text-[9px] font-black uppercase text-red-500 dark:text-red-400 mb-1 tracking-wide">
                                    Motivo de baja <span class="text-red-600">*</span>
                                </label>
                                <input type="text" wire:model="items_baja.{{ $id }}.motivo_baja"
                                    placeholder="Ej: equipo destruido en obra, pérdida por robo, caída en altura..."
                                    class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-neutral-900 border-red-200 dark:border-red-800/50 focus:ring-2 focus:ring-red-500/25 focus:border-red-400 text-gray-700 dark:text-neutral-200 placeholder:text-red-300 dark:placeholder:text-red-900/50">
                                @error("items_baja.{$id}.motivo_baja")
                                    <span class="text-[10px] text-red-500 italic block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        @error('items_baja')
            <div
                class="px-3 py-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/40 rounded-xl">
                <span class="text-[11px] italic font-bold text-red-500">{{ $message }}</span>
            </div>
        @enderror

    </div>

    @slot('footer')
        <div class="w-full flex items-center justify-between gap-3">
            <p class="text-[10px] text-gray-400 dark:text-neutral-500 italic hidden sm:block">
                Solo los equipos con cantidad > 0 serán procesados.
            </p>
            <div class="flex gap-3 ml-auto">
                <button type="button" @click="openModalBaja = false"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="saveBaja" wire:loading.attr="disabled"
                    class="px-8 py-2 rounded-lg cursor-pointer bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-red-600/20 uppercase tracking-wide">
                    <span wire:loading.remove wire:target="saveBaja">Confirmar Baja</span>
                    <span wire:loading wire:target="saveBaja">Procesando...</span>
                </button>
            </div>
        </div>
    @endslot
</x-ui.modal>

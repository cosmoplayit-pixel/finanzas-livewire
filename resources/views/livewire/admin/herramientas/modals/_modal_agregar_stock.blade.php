    {{-- ===================== MODAL AGREGAR STOCK ===================== --}}
    <x-ui.modal wire:key="add-stock-modal" model="openAddStockModal" title="Agregar Stock" maxWidth="sm:max-w-xl"
        onClose="closeAddStockModal">
        <div class="space-y-4">
            {{-- Info herramienta --}}
            <div
                class="flex items-center gap-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 p-3">
                @if ($addStockImagen)
                    @php
                        $addStockUrl = Storage::url($addStockImagen);
                        $isAddStockPdf = \Illuminate\Support\Str::contains(strtolower($addStockUrl), '.pdf');
                    @endphp
                    @if ($isAddStockPdf)
                        <div class="w-11 h-11 rounded-lg bg-red-50 dark:bg-red-900/20 flex flex-col items-center justify-center border border-red-100 dark:border-red-800 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $addStockUrl }}' }))">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @else
                        <img src="{{ $addStockUrl }}" alt="{{ $addStockNombre }}"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $addStockUrl }}' }))"
                            class="w-11 h-11 rounded-lg object-cover border border-indigo-200 dark:border-indigo-800 shrink-0 cursor-pointer hover:opacity-80 transition shadow-sm"
                            title="Clic para ampliar">
                    @endif
                @else
                    <div
                        class="w-20 h-20 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-gray-900 dark:text-neutral-100 text-sm leading-tight truncate">
                        {{ $addStockNombre }}</div>
                    @if ($addStockCodigo)
                        <div class="font-mono text-[10px] text-indigo-500 dark:text-indigo-400 mt-0.5">
                            {{ $addStockCodigo }}</div>
                    @endif
                </div>
            </div>

            {{-- Stock actual --}}
            <div class="grid grid-cols-2 gap-2">
                <div
                    class="rounded-xl border border-gray-100 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 p-3 text-center">
                    <div class="text-[10px] uppercase font-bold text-gray-400 dark:text-neutral-500 mb-1">Stock actual
                    </div>
                    <div class="text-2xl font-black text-gray-700 dark:text-neutral-200">{{ $addStockActual }}</div>
                </div>
                <div
                    class="rounded-xl border border-emerald-100 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                    <div class="text-[10px] uppercase font-bold text-emerald-500 dark:text-emerald-400 mb-1">Después de
                        agregar</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                        {{ $addStockActual + max(0, (int) $addStockCantidad) }}</div>
                </div>
            </div>

            {{-- Cantidad a agregar --}}
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-neutral-300">
                    Cantidad a agregar <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="decrementAddStock"
                        class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-600 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-800 font-bold text-lg cursor-pointer transition">
                        −
                    </button>
                    <input type="number" wire:model.live="addStockCantidad" min="1" max="9999"
                        autocomplete="off"
                        class="flex-1 rounded-lg border px-3 py-2.5 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-center text-2xl font-black tabular-nums">
                    <button type="button" wire:click="incrementAddStock"
                        class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-600 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-800 font-bold text-lg cursor-pointer transition">
                        +
                    </button>
                </div>
                @error('addStockCantidad')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dinámico: Números de Serie para Activos Fijos --}}
            @if ($addStockTipo === 'activo')
                <div class="mt-4 border-t border-gray-100 dark:border-neutral-800 pt-4 space-y-3">
                    <p class="text-sm font-bold text-gray-800 dark:text-neutral-200">
                        Ingresar Números de Serie / Placas
                    </p>
                    <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-1">
                        @foreach ($addStockSeries as $index => $serie)
                            <div class="relative" wire:key="serie-{{ $index }}">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span
                                        class="text-gray-500 dark:text-gray-400 font-mono text-xs">#{{ $index + 1 }}</span>
                                </div>
                                <input type="text" wire:model="addStockSeries.{{ $index }}"
                                    placeholder="Ej: SN-00{{ $index + 1 }}..." autocomplete="off"
                                    class="w-full rounded-lg border pl-10 pr-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm font-mono uppercase">
                                @error('addStockSeries.' . $index)
                                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2">
                <button type="button" wire:click="closeAddStockModal"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="saveAddStock" wire:loading.attr="disabled" @disabled(!$addStockCantidad || $addStockCantidad <= 0)
                    class="px-5 py-2 rounded-lg cursor-pointer bg-emerald-600 text-white hover:bg-emerald-700 transition text-sm font-black shadow-lg shadow-emerald-600/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveAddStock">+ Agregar Stock</span>
                    <span wire:loading wire:target="saveAddStock">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>

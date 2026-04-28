    {{-- ===================== MODAL BAJA STOCK ===================== --}}
    <x-ui.modal wire:key="baja-stock-modal" model="openBajaStockModal" title="Baja de Stock" maxWidth="sm:max-w-xl"
        onClose="closeBajaStockModal">
        <div class="space-y-4">
            {{-- Info herramienta --}}
            <div
                class="flex items-center gap-3 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-800/40 p-3">
                @if ($bajaStockImagen)
                    @php
                        $bajaStockUrl = Storage::url($bajaStockImagen);
                        $isBajaStockPdf = \Illuminate\Support\Str::contains(strtolower($bajaStockUrl), '.pdf');
                    @endphp
                    @if ($isBajaStockPdf)
                        <div class="w-11 h-11 rounded-lg bg-red-50 dark:bg-red-900/20 flex flex-col items-center justify-center border border-red-100 dark:border-red-800 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $bajaStockUrl }}' }))">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @else
                        <img src="{{ $bajaStockUrl }}" alt="{{ $bajaStockNombre }}"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $bajaStockUrl }}' }))"
                            class="w-11 h-11 rounded-lg object-cover border border-red-200 dark:border-red-800 shrink-0 cursor-pointer hover:opacity-80 transition shadow-sm"
                            title="Clic para ampliar">
                    @endif
                @else
                    <div
                        class="w-11 h-11 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-gray-900 dark:text-neutral-100 text-sm leading-tight truncate">
                        {{ $bajaStockNombre }}</div>
                    @if ($bajaStockCodigo)
                        <div class="font-mono text-[10px] text-red-600 dark:text-red-400 mt-0.5 font-bold">
                            {{ $bajaStockCodigo }}</div>
                    @endif
                </div>
            </div>

            {{-- Stock actual vs Resultante --}}
            @php
                $cantidadBaja = in_array($bajaStockTipo, ['activo', 'equipo'])
                    ? count($bajaStockSeriesSeleccionadas)
                    : (int) $bajaStockCantidad;
            @endphp
            <div class="grid grid-cols-2 gap-2">
                <div
                    class="rounded-xl border border-gray-100 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 p-3 text-center">
                    <div class="text-[10px] uppercase font-bold text-gray-400 dark:text-neutral-500 mb-1">Stock actual
                    </div>
                    <div class="text-2xl font-black text-gray-700 dark:text-neutral-200">{{ $bajaStockActual }}</div>
                </div>
                <div
                    class="rounded-xl border border-red-100 dark:border-red-800/40 bg-red-50 dark:bg-red-900/20 p-3 text-center">
                    <div class="text-[10px] uppercase font-bold text-red-500 dark:text-red-400 mb-1">Stock Restante
                    </div>
                    <div class="text-2xl font-black text-red-600 dark:text-red-400 font-mono">
                        {{ max(0, $bajaStockActual - $cantidadBaja) }}</div>
                </div>
            </div>

            @if (in_array($bajaStockTipo, ['activo', 'equipo']))
                {{-- Selector de series para activos --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-neutral-300">
                            Seleccioná los números de serie a dar de baja <span class="text-red-500">*</span>
                        </label>
                        @if (count($bajaStockSeriesSeleccionadas) > 0)
                            <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                {{ count($bajaStockSeriesSeleccionadas) }} seleccionado(s)
                            </span>
                        @endif
                    </div>
                    @if (empty($bajaStockSeriesDisponibles))
                        <div
                            class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 px-4 py-3 text-xs text-gray-500 dark:text-neutral-400 text-center">
                            Sin series disponibles para dar de baja.
                        </div>
                    @else
                        <div
                            class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden divide-y divide-gray-100 dark:divide-neutral-800 max-h-48 overflow-y-auto">
                            @foreach ($bajaStockSeriesDisponibles as $s)
                                <label
                                    class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/10 transition {{ in_array($s['id'], $bajaStockSeriesSeleccionadas) ? 'bg-red-50 dark:bg-red-900/10' : 'bg-white dark:bg-neutral-900' }}">
                                    <input type="checkbox" wire:model.live="bajaStockSeriesSeleccionadas"
                                        value="{{ $s['id'] }}"
                                        class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                                    <span
                                        class="font-mono text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $s['serie'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('bajaStockSeriesSeleccionadas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @else
                {{-- Cantidad a retirar (herramientas/materiales) --}}
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-neutral-300">
                        Cantidad a dar de baja <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="decrementBajaStock"
                            class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-600 dark:text-neutral-300 hover:bg-red-50 dark:hover:bg-red-900/40 font-bold text-lg cursor-pointer transition">
                            −
                        </button>
                        <input type="number" wire:model.live="bajaStockCantidad" min="1"
                            max="{{ $bajaStockActual }}" autocomplete="off"
                            class="flex-1 rounded-lg border px-3 py-2.5 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-red-600 dark:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/40 text-center text-2xl font-black tabular-nums">
                        <button type="button" wire:click="incrementBajaStock"
                            class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-600 dark:text-neutral-300 hover:bg-red-50 dark:hover:bg-red-900/40 font-bold text-lg cursor-pointer transition">
                            +
                        </button>
                    </div>
                    @error('bajaStockCantidad')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Observaciones --}}
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-neutral-300">
                    Motivo / Observación <span class="text-red-500">*</span>
                </label>
                <textarea wire:model.live="bajaStockObservaciones" rows="3"
                    placeholder="Ej: Herramienta rota, extravío en obra, etc..."
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/40 text-sm resize-none"></textarea>
                @error('bajaStockObservaciones')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Evidencia --}}
            <div>
                <x-ui.scanner model="bajaStockEvidencia" label="Imagen de Evidencia (Opcional)" :file="$bajaStockEvidencia" />
            </div>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2">
                <button type="button" wire:click="closeBajaStockModal"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-100 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="saveBajaStock" wire:loading.attr="disabled" @disabled(
                    !$bajaStockObservaciones ||
                        (in_array($bajaStockTipo, ['activo', 'equipo'])
                            ? empty($bajaStockSeriesSeleccionadas)
                            : !$bajaStockCantidad || $bajaStockCantidad <= 0))
                    class="px-5 py-2 rounded-lg cursor-pointer bg-red-600 text-white hover:bg-red-700 transition text-sm font-black shadow-lg shadow-red-600/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveBajaStock">Confirmar Baja</span>
                    <span wire:loading wire:target="saveBajaStock">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>

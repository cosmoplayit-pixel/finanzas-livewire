{{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
<x-ui.modal wire:key="prestamo-return-{{ $openModalDevolucion ? 'open' : 'closed' }}" model="openModalDevolucion"
    title="Recepción de Herramientas" maxWidth="sm:max-w-2xl md:max-w-3xl" onClose="$set('openModalDevolucion', false)">

    <div class="space-y-4">

        {{-- ── Lista de herramientas ── --}}
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

            {{-- Cabecera de columnas --}}
            <div
                class="hidden sm:grid grid-cols-[1fr_64px_80px] gap-x-3 items-center px-3 py-2 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-200 dark:border-neutral-700">
                <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider">Herramienta</span>
                <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider text-center">Pend.</span>
                <span class="text-[9px] font-black uppercase text-emerald-600 tracking-wider">Devolver</span>
            </div>

            {{-- Filas --}}
            <div class="max-h-[46vh] overflow-y-auto divide-y divide-gray-100 dark:divide-neutral-800">
                @foreach ($items_devolucion as $id => $item)
                    <div wire:key="item-dev-{{ $id }}" x-data="{ qty: @entangle('items_devolucion.' . $id . '.cantidad_a_devolver').live }"
                        class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_64px_80px] gap-x-3 items-center px-3 py-2.5">

                        {{-- Herramienta: thumbnail + nombre + código --}}
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if (!empty($item['imagen'] ?? null))
                                <img src="{{ asset('storage/' . $item['imagen']) }}"
                                    class="size-9 rounded-lg object-cover border border-gray-200 dark:border-neutral-700 shrink-0 shadow-sm">
                            @else
                                <div
                                    class="size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 flex items-center justify-center shrink-0">
                                    <svg class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate leading-tight"
                                    title="{{ $item['herramienta_nombre'] ?? '—' }}">
                                    {{ $item['herramienta_nombre'] ?? '—' }}</div>
                                <div
                                    class="text-[9px] font-mono text-gray-400 dark:text-neutral-500 leading-none mt-0.5">
                                    {{ $item['codigo'] ?? '—' }}
                                </div>
                            </div>
                        </div>

                        {{-- Pendiente --}}
                        <div class="text-center">
                            <span
                                class="inline-block px-1 py-1.5 text-sm font-black text-gray-700 dark:text-neutral-300 bg-gray-100 dark:bg-neutral-800 rounded-md px-2 py-0.5 min-w-[36px] text-center">
                                {{ $item['cantidad_pendiente'] }}
                            </span>
                        </div>

                        {{-- Cantidad a devolver --}}
                        <div>
                            <input type="number"
                                wire:model.live="items_devolucion.{{ $id }}.cantidad_a_devolver"
                                min="0" max="{{ $item['cantidad_pendiente'] }}"
                                class=" rounded-lg border px-0.5 py-1.5 text-sm font-black text-center bg-emerald-50 dark:bg-emerald-900/15 border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition">
                            @error("items_devolucion.{$id}.cantidad_a_devolver")
                                <span
                                    class="text-[9px] text-red-500 italic block mt-0.5 text-center">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        @error('items_devolucion')
            <div class="px-3 py-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/40 rounded-xl">
                <span class="text-[11px] italic font-bold text-red-500">{{ $message }}</span>
            </div>
        @enderror

        {{-- ── Campos globales ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-100 dark:border-neutral-800 pt-4">
            <div>
                <label class="block text-[11px] mb-1 text-gray-500 font-bold uppercase">Fecha de Recepción
                    <span class="text-red-500">*</span></label>
                <input type="date" wire:model.live="fecha_devolucion"
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-sm focus:ring-2 focus:ring-emerald-500/40">
            </div>
            <div>
                <label class="block text-[11px] mb-1 text-gray-500 font-bold uppercase">Observaciones</label>
                <input type="text" wire:model="observaciones_devolucion"
                    placeholder="Estado general, notas de recepción..."
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-sm focus:ring-2 focus:ring-emerald-500/40">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold uppercase text-gray-500 mb-1">Evidencia fotográfica
                    (fotos/PDF) <span class="text-red-500">*</span></label>
                <input type="file" wire:model.live="temp_fotos_entrada" multiple accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4 file:cursor-pointer
                        file:rounded-lg file:border-0 file:text-sm file:font-semibold
                        file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100
                        border border-gray-200 dark:border-neutral-700 rounded-lg p-1 bg-white dark:bg-neutral-900" />
                <div wire:loading wire:target="temp_fotos_entrada" class="text-xs text-emerald-500 mt-1 font-bold">
                    Subiendo archivos...</div>
                @error('fotos_entrada')
                    <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                @enderror
                @if ($fotos_entrada && is_array($fotos_entrada) && count($fotos_entrada) > 0)
                    <div class="mt-2 flex gap-2 flex-wrap">
                        @foreach ($fotos_entrada as $idx => $f)
                            @if ($f)
                                <div class="relative group">
                                    @php $isPdf = strtolower($f->getClientOriginalExtension()) === 'pdf'; @endphp
                                    @if ($isPdf)
                                        <div class="size-16 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 flex flex-col items-center justify-center text-red-500"
                                            title="{{ $f->getClientOriginalName() }}">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-[8px] font-bold mt-1 uppercase">PDF</span>
                                        </div>
                                    @else
                                        <img src="{{ $f->temporaryUrl() }}"
                                            class="size-16 rounded-lg object-cover border border-gray-200 shadow-sm">
                                    @endif

                                    {{-- Botón Eliminar Foto --}}
                                    <button type="button" wire:click="removeFotoEntrada({{ $idx }})"
                                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition shadow-md z-10 cursor-pointer">
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
            <x-ui.signature-pad model="firma_entrada" label="Firma de quien devuelve" />
        </div>

    </div>

    @slot('footer')
        <div class="w-full flex justify-end gap-3">
            <button type="button" @click="$set('openModalDevolucion', false)"
                class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                Cerrar
            </button>
            <button type="button" wire:click="saveDevolucion" wire:loading.attr="disabled" @disabled(!$fecha_devolucion || empty($fotos_entrada) || !$firma_entrada)
                class="px-8 py-2 rounded-lg cursor-pointer bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-emerald-600/10 uppercase tracking-wide">
                <span wire:loading.remove wire:target="saveDevolucion">Devolver</span>
                <span wire:loading wire:target="saveDevolucion">Procesando...</span>
            </button>
        </div>
    @endslot
</x-ui.modal>

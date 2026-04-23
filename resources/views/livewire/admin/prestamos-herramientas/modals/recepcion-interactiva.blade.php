{{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
<x-ui.modal wire:key="prestamo-return-{{ $openModalDevolucion ? 'open' : 'closed' }}" model="openModalDevolucion"
    title="Gestión y Cierre de Préstamo" maxWidth="max-w-3xl lg:max-w-4xl" onClose="$set('openModalDevolucion', false)">

    <div class="space-y-4">

        {{-- ── Lista de herramientas ── --}}
        <div
            class="rounded-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden bg-white dark:bg-neutral-900 shadow-sm">

            {{-- Cabecera de columnas Desktop --}}
            <div
                class="hidden sm:grid grid-cols-[1fr_80px_110px_110px] gap-x-4 items-center px-4 py-3 bg-gray-50/80 dark:bg-neutral-800/60 border-b border-gray-200 dark:border-neutral-700">
                <span class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Equipos y Consumibles</span>
                <span class="text-[10px] font-black uppercase text-gray-500 tracking-wider text-center">Saldo</span>
                <div class="flex items-center justify-center gap-1.5 text-emerald-600 dark:text-emerald-500">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-wider">Retorna</span>
                </div>
                <div class="flex items-center justify-center gap-1.5 text-red-600 dark:text-red-500">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-wider">Consumo/Baja</span>
                </div>
            </div>

            {{-- Filas --}}
            <div class="max-h-[55vh] overflow-y-auto divide-y divide-gray-100 dark:divide-neutral-800">
                @foreach ($items_devolucion as $id => $item)
                    <div wire:key="item-dev-{{ $id }}"
                        class="flex flex-col sm:grid sm:grid-cols-[1fr_80px_110px_110px] gap-y-4 sm:gap-x-4 items-start px-4 py-4 hover:bg-gray-50/50 dark:hover:bg-neutral-800/30 transition-colors">

                        {{-- Columna 1: Herramienta y Detalles --}}
                        <div class="flex items-start gap-3 w-full">
                            @if (!empty($item['imagen'] ?? null))
                                <img src="{{ asset('storage/' . $item['imagen']) }}"
                                    class="size-11 rounded-xl object-cover border border-gray-200 dark:border-neutral-700 shadow-sm mt-0.5">
                            @else
                                <div
                                    class="size-11 rounded-xl bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 flex items-center justify-center shadow-sm mt-0.5">
                                    <svg class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0 pr-2">
                                <div class="text-sm font-black text-gray-900 dark:text-gray-100 truncate leading-tight"
                                    title="{{ $item['herramienta_nombre'] ?? '—' }}">
                                    {{ $item['herramienta_nombre'] ?? '—' }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="px-1.5 py-0.5 rounded font-mono text-[9px] border bg-gray-50 border-gray-200 text-gray-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400">
                                        {{ $item['codigo'] ?? '—' }}
                                    </span>
                                    @if (($item['tipo'] ?? '') === 'material')
                                        <span
                                            class="px-1.5 py-0.5 rounded font-bold text-[9px] bg-blue-50 text-blue-500 dark:bg-blue-900/20 dark:text-blue-400">MATERIAL</span>
                                    @endif
                                </div>

                                {{-- Sub-opciones Dinámicas (Solo se muestran si se requiere) --}}
                                <div class="mt-2.5 flex flex-col gap-2">
                                    {{-- Sector "Retorno" -> Estado Físico --}}
                                    @if (($item['cantidad_a_devolver'] ?? 0) > 0 && ($item['tipo'] ?? '') !== 'material')
                                        <div class="flex items-center gap-1.5 animate-in fade-in slide-in-from-top-1">
                                            <span class="text-[9px] text-gray-400 font-bold uppercase">Estado:</span>
                                            <div class="flex gap-1">
                                                @foreach ([
        'bueno' => ['Bueno', 'bg-emerald-500 text-white border-emerald-500', 'border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800'],
        'regular' => ['Regular', 'bg-amber-500 text-white border-amber-500', 'border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800'],
        'malo' => ['Malo', 'bg-red-500 text-white border-red-500', 'border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800'],
    ] as $ef => [$label, $active, $inactive])
                                                    <button type="button"
                                                        wire:click="$set('items_devolucion.{{ $id }}.estado_fisico', '{{ $ef }}')"
                                                        class="text-[9px] font-black px-2 py-0.5 rounded-full border transition-colors {{ ($item['estado_fisico'] ?? 'bueno') === $ef ? $active : $inactive }}">
                                                        {{ $label }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Sector "Baja/Consumo" -> Motivo --}}
                                    @if (($item['cantidad_baja'] ?? 0) > 0)
                                        <div class="flex items-center gap-1.5 animate-in fade-in slide-in-from-top-1">
                                            <span class="text-[9px] text-gray-400 font-bold uppercase">Razón:</span>
                                            @if (($item['tipo'] ?? '') === 'material')
                                                <span class="text-[10px] font-bold text-red-500 italic">Consumo
                                                    natural.</span>
                                            @else
                                                <select
                                                    wire:model.live="items_devolucion.{{ $id }}.motivo_baja"
                                                    class="w-[125px] rounded-lg border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2 py-0.5 text-[10px] font-bold text-gray-600 focus:ring-red-500 focus:border-red-500 shadow-sm cursor-pointer">
                                                    <option value="extraviado_roto">Roto / Perdido</option>
                                                    <option value="robo">Robado</option>
                                                    <option value="dado_de_baja">Cumplió vida útil</option>
                                                </select>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Columna 2: Pendiente (Badge Grande) --}}
                        <div class="hidden sm:flex flex-col items-center justify-center pt-2 w-full h-full">
                            <span
                                class="inline-flex items-center justify-center size-10 rounded-2xl bg-gray-100 dark:bg-neutral-800 text-lg font-black text-gray-700 dark:text-neutral-300 ring-1 ring-inset ring-gray-200 dark:ring-neutral-700 shadow-inner">
                                {{ $item['cantidad_pendiente'] }}
                            </span>
                        </div>

                        {{-- Contenedor Mobile para Inputs --}}
                        <div
                            class="w-full sm:col-span-2 sm:grid sm:grid-cols-[110px_110px] gap-4 sm:gap-x-4 mt-2 sm:mt-0 pt-3 sm:pt-0 border-t border-dashed border-gray-200 sm:border-0 dark:border-neutral-800">
                            {{-- Input Retorna --}}
                            <div class="flex items-center sm:block gap-3">
                                <label
                                    class="sm:hidden block w-1/3 text-[10px] font-black text-emerald-600 uppercase">Retorna
                                    a stock</label>
                                <div class="grow">
                                    <input type="number"
                                        wire:model.live="items_devolucion.{{ $id }}.cantidad_a_devolver"
                                        min="0" max="{{ $item['cantidad_pendiente'] }}"
                                        class="w-full rounded-xl border px-3 py-2 text-xl font-black text-center bg-emerald-50 dark:bg-emerald-900/15 border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-400 transition shadow-inner">
                                </div>
                            </div>

                            {{-- Input Baja/Consumo --}}
                            <div class="flex items-center sm:block gap-3 mt-3 sm:mt-0">
                                <label class="sm:hidden block w-1/3 text-[10px] font-black text-red-500 uppercase">Se
                                    perdió / consumió</label>
                                <div class="grow">
                                    <input type="number"
                                        wire:model.live="items_devolucion.{{ $id }}.cantidad_baja"
                                        min="0" max="{{ $item['cantidad_pendiente'] }}"
                                        class="w-full rounded-xl border px-3 py-2 text-xl font-black text-center bg-red-50 dark:bg-red-900/15 border-red-200 dark:border-red-800/50 text-red-800 dark:text-red-400 focus:ring-2 focus:ring-red-500/40 focus:border-red-400 transition shadow-inner">
                                </div>
                            </div>
                        </div>

                        {{-- Alertas Combinadas --}}
                        <div class="w-full sm:col-span-4 mt-1">
                            @error("items_devolucion.{$id}.cantidad_baja")
                                <span class="text-[10px] font-bold text-red-500 block text-right">⚠️
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @error('items_devolucion')
            <div
                class="px-4 py-3 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/40 rounded-xl flex gap-3 items-center">
                <svg class="size-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</span>
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
                <div x-data="fileDropZoneEntrada" @paste.window="handlePaste($event)" @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false; handleFiles(Array.from($event.dataTransfer.files))"
                    @click="triggerInput()"
                    :class="dragging ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 scale-[1.01]' :
                        'border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:border-emerald-400 hover:bg-emerald-50/30'"
                    class="relative flex flex-col items-center justify-center gap-2 w-full min-h-[80px] rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200 px-4 py-4 select-none">

                    {{-- Icono + texto guía --}}
                    <div class="flex items-center gap-3 pointer-events-none">
                        <svg class="w-7 h-7 text-emerald-400 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-700 dark:text-neutral-200">
                                Arrastrá, pegá o hacé clic
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-neutral-500 mt-0.5">
                                JPG, PNG, PDF · <kbd
                                    class="px-1 py-0.5 rounded bg-gray-100 dark:bg-neutral-800 font-mono text-[10px] border border-gray-200 dark:border-neutral-700">Ctrl</kbd>+<kbd
                                    class="px-1 py-0.5 rounded bg-gray-100 dark:bg-neutral-800 font-mono text-[10px] border border-gray-200 dark:border-neutral-700">V</kbd>
                                para pegar imagen
                            </p>
                        </div>
                    </div>

                    {{-- Input real oculto --}}
                    <input type="file" x-ref="fileInputEntrada" wire:model.live="temp_fotos_entrada" multiple
                        accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                        @click.stop />
                </div>
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
                                    @if (strtolower($f->getClientOriginalExtension()) === 'pdf')
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
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
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
            <button type="button" @click="close()"
                class="px-5 py-2.5 rounded-xl border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                Cerrar
            </button>
            <button type="button" wire:click="saveDevolucion" wire:loading.attr="disabled" @disabled(!$fecha_devolucion || empty($fotos_entrada) || !$firma_entrada)
                class="px-8 py-2.5 rounded-xl cursor-pointer bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-blue-600/20 uppercase tracking-wide flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="saveDevolucion" class="flex gap-2 items-center">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Procesar Gestión
                </span>
                <span wire:loading wire:target="saveDevolucion">Guardando...</span>
            </button>
        </div>
    @endslot
</x-ui.modal>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fileDropZoneEntrada', () => ({
                dragging: false,
                triggerInput() {
                    this.$refs.fileInputEntrada.click();
                },
                handleFiles(files) {
                    if (!files || files.length === 0) return;
                    const dt = new DataTransfer();
                    files.forEach(f => dt.items.add(f));
                    this.$refs.fileInputEntrada.files = dt.files;
                    this.$refs.fileInputEntrada.dispatchEvent(new Event('change'));
                },
                handlePaste(e) {
                    const items = e.clipboardData?.items;
                    if (!items) return;
                    const files = [];
                    for (const item of items) {
                        if (item.kind === 'file' && item.type.startsWith('image/')) {
                            const file = item.getAsFile();
                            if (file) files.push(new File([file], 'pegado-retorno-' + Date.now() +
                                '.png', {
                                    type: 'image/png'
                                }));
                        }
                    }
                    if (files.length) this.handleFiles(files);
                },
            }));
        });
    </script>
@endonce

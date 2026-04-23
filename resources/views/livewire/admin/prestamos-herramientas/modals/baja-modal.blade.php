{{-- ===================== MODAL DAR DE BAJA (REDiseñado) ===================== --}}
<x-ui.modal wire:key="prestamo-baja-{{ $openModalBaja ? 'open' : 'closed' }}" model="openModalBaja"
    title="Gestión de Bajas — {{ $prestamoNroParaBaja }}" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="$set('openModalBaja', false)">

    <div class="space-y-6">

        {{-- ── Banner de Alerta Premium ── --}}
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500/10 to-orange-500/5 p-4 border border-red-200/50 dark:border-red-500/10 shadow-sm">
            <div class="absolute -right-4 -top-4 size-24 bg-red-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-start gap-4">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-lg shadow-red-500/30 text-white">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13px] font-black uppercase tracking-tight text-red-700 dark:text-red-400">Acción
                        Crítica</h3>
                    <p class="mt-1 text-xs leading-relaxed text-red-600/80 dark:text-red-400/60">
                        Esta acción retirará los equipos del inventario de forma <span
                            class="font-bold underline">definitiva</span> sin retorno al almacén.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Lista de equipos con Layout Premium ── --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <span
                    class="text-[11px] font-black uppercase tracking-widest text-gray-400 dark:text-neutral-500">Herramientas
                    en Préstamo</span>
                <span class="text-[10px] text-gray-400 dark:text-neutral-600">{{ count($items_baja) }} ítems</span>
            </div>

            <div class="space-y-3">
                @foreach ($items_baja as $id => $item)
                    <div wire:key="baja-row-{{ $id }}"
                        class="group relative overflow-hidden rounded-2xl border transition-all duration-300 {{ (int) ($item['cantidad_baja'] ?? 0) > 0 ? 'border-red-500/30 bg-white dark:bg-neutral-800 shadow-md ring-1 ring-red-500/10' : 'border-gray-200 dark:border-neutral-700/50 bg-gray-50/30 dark:bg-neutral-900/40 opacity-70 hover:opacity-100 hover:border-gray-300 dark:hover:border-neutral-600' }}">

                        {{-- Indicador de actividad --}}
                        <div
                            class="absolute left-0 top-0 h-full w-1 transition-all {{ (int) ($item['cantidad_baja'] ?? 0) > 0 ? 'bg-red-500' : 'bg-transparent' }}">
                        </div>

                        {{-- Fila Principal --}}
                        <div class="p-3">
                            <div class="flex items-center gap-3">
                                {{-- Thumbnail --}}
                                <div
                                    class="relative size-10 shrink-0 overflow-hidden rounded-lg border border-gray-100 dark:border-neutral-700 shadow-sm">
                                    @if (!empty($item['imagen']))
                                        <img src="{{ asset('storage/' . $item['imagen']) }}"
                                            class="size-full object-cover">
                                    @else
                                        <div
                                            class="flex size-full items-center justify-center bg-gray-100 dark:bg-neutral-800 text-gray-400">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                            </svg>
                                        </div>
                                    @endif
                                    @if ((int) ($item['cantidad_baja'] ?? 0) > 0)
                                        <div class="absolute inset-0 bg-red-500/10 animate-pulse"></div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4
                                            class="text-sm font-black leading-tight {{ (int) ($item['cantidad_baja'] ?? 0) > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-900 dark:text-white' }}">
                                            {{ $item['herramienta_nombre'] }}
                                        </h4>
                                        <span
                                            class="text-[9px] font-mono text-indigo-500 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded uppercase">
                                            {{ $item['codigo'] ?? 'S/C' }}
                                        </span>
                                        @if (!empty($item['nro_serie']))
                                            <span
                                                class="text-[9px] font-mono font-bold text-gray-500 dark:text-neutral-400 bg-gray-100 dark:bg-neutral-700 px-1.5 py-0.5 rounded">
                                                S/N: {{ $item['nro_serie'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div
                                        class="mt-0.5 flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-neutral-400">
                                        <span>Pendiente:</span>
                                        <span
                                            class="font-black text-gray-700 dark:text-neutral-200 bg-gray-100 dark:bg-neutral-800 px-1.5 rounded">{{ $item['cantidad_pendiente'] }}
                                            u.</span>
                                    </div>
                                </div>

                                {{-- Input Cantidad --}}
                                <div class="shrink-0">
                                    <div class="relative">
                                        <input type="number"
                                            wire:model.live="items_baja.{{ $id }}.cantidad_baja"
                                            min="0" max="{{ $item['cantidad_pendiente'] }}"
                                            class="w-14 rounded-xl border-2 px-1 py-1 text-center text-sm font-black transition-all focus:ring-4 {{ (int) ($item['cantidad_baja'] ?? 0) > 0 ? 'border-red-500 bg-red-50 text-red-700 focus:ring-red-500/20' : 'border-gray-200 bg-white text-gray-600 focus:ring-gray-200/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300' }}">
                                        @if ((int) ($item['cantidad_baja'] ?? 0) > 0)
                                            <div
                                                class="absolute -right-1 -top-1 size-2 rounded-full bg-red-500 animate-ping">
                                            </div>
                                        @endif
                                    </div>
                                    <div
                                        class="mt-1 text-[9px] font-black uppercase text-center {{ (int) ($item['cantidad_baja'] ?? 0) > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                        Baja</div>
                                </div>
                            </div>

                            {{-- Sección Expandible: Motivo + Evidencia --}}
                            <div x-show="parseInt(qty || 0) > 0" x-collapse x-data="{ qty: @entangle('items_baja.' . $id . '.cantidad_baja').live }">
                                <div
                                    class="mt-4 grid grid-cols-1 gap-4 border-t border-gray-100 dark:border-neutral-700/50 pt-4">
                                    {{-- Motivo --}}
                                    <div>
                                        <label
                                            class="mb-1 block text-[10px] font-black uppercase text-red-500/80">Motivo
                                            Detallado <span class="text-red-600">*</span></label>
                                        <textarea wire:model="items_baja.{{ $id }}.motivo_baja" rows="2"
                                            placeholder="Describa el estado o razón de la baja..."
                                            class="w-full rounded-xl border border-red-100 bg-red-50/30 px-3 py-2 text-xs placeholder:text-red-200 focus:border-red-400 focus:ring-4 focus:ring-red-500/10 dark:border-red-900/30 dark:bg-red-900/5 dark:text-neutral-200 dark:placeholder:text-red-900/50 transition-all"></textarea>
                                        @error("items_baja.{$id}.motivo_baja")
                                            <span
                                                class="mt-1 block text-[10px] italic text-red-500 font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Evidencia Fotográfica --}}
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-red-500/80 mb-1">
                                            Evidencia Fotográfica <span class="text-red-600">*</span>
                                        </label>
                                        <div x-data="fileDropZoneBaja('fotoBaja{{ $id }}')" @paste.window="handlePaste($event)"
                                            @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                                            @drop.prevent="dragging = false; handleFiles(Array.from($event.dataTransfer.files))"
                                            @click="triggerInput()"
                                            :class="dragging ? 'border-red-400 bg-red-50 dark:bg-red-900/20 scale-[1.01]' :
                                                'border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:border-red-400 hover:bg-red-50/30'"
                                            class="relative flex flex-col items-center justify-center gap-2 w-full min-h-[72px] rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200 px-4 py-3 select-none">

                                            @if (!empty($fotos_baja[$id]))
                                                <div class="flex items-center gap-3 pointer-events-none">
                                                    <div
                                                        class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center shrink-0 overflow-hidden">
                                                        @if (strtolower($fotos_baja[$id]->getClientOriginalExtension()) === 'pdf')
                                                            <svg class="w-4 h-4 text-red-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                        @else
                                                            <img src="{{ $fotos_baja[$id]->temporaryUrl() }}"
                                                                class="w-8 h-8 object-cover">
                                                        @endif
                                                    </div>
                                                    <div class="text-left">
                                                        <p
                                                            class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 truncate max-w-[180px]">
                                                            ✓ {{ $fotos_baja[$id]->getClientOriginalName() }}
                                                        </p>
                                                        <p class="text-[10px] text-gray-400 dark:text-neutral-500">Clic
                                                            para cambiar</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-3 pointer-events-none">
                                                    <svg class="w-6 h-6 text-red-400 shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    <div class="text-left">
                                                        <p
                                                            class="text-sm font-semibold text-gray-700 dark:text-neutral-200">
                                                            Arrastrá, pegá o hacé clic</p>
                                                        <p
                                                            class="text-[11px] text-gray-400 dark:text-neutral-500 mt-0.5">
                                                            JPG, PNG, PDF &middot; <kbd
                                                                class="px-1 py-0.5 rounded bg-gray-100 dark:bg-neutral-800 font-mono text-[10px] border border-gray-200 dark:border-neutral-700">Ctrl</kbd>+<kbd
                                                                class="px-1 py-0.5 rounded bg-gray-100 dark:bg-neutral-800 font-mono text-[10px] border border-gray-200 dark:border-neutral-700">V</kbd>
                                                            para pegar
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            <input type="file" x-ref="fotoBaja{{ $id }}"
                                                wire:model="fotos_baja.{{ $id }}"
                                                accept=".jpg,.jpeg,.png,.pdf"
                                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                                                @click.stop />
                                        </div>
                                        <div wire:loading wire:target="fotos_baja.{{ $id }}"
                                            class="text-[11px] text-red-500 font-bold mt-1 animate-pulse">Subiendo…
                                        </div>
                                        @error("fotos_baja.{$id}")
                                            <span
                                                class="mt-1 block text-[10px] italic text-red-500 font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @error('items_baja')
            <div class="flex items-center gap-2 rounded-xl bg-red-100 p-3 text-red-700 shadow-inner">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold">{{ $message }}</span>
            </div>
        @enderror

    </div>

    @slot('footer')
        <div class="w-full flex items-center justify-between">
            <p class="text-[10px] text-gray-400 font-medium italic hidden sm:block">
                Solo los ítems con cantidad de baja serán procesados.
            </p>
            <div class="flex gap-3">
                <button type="button" @click="close()"
                    class="px-5 py-2 rounded-xl border border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-800 text-[13px] font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="saveBaja" wire:loading.attr="disabled"
                    class="relative overflow-hidden group px-8 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-[13px] font-black transition shadow-xl shadow-red-600/30 uppercase tracking-widest">
                    <span wire:loading.remove wire:target="saveBaja" class="flex items-center gap-2">
                        EJECUTAR BAJA
                        <svg class="size-4 group-hover:translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="saveBaja" class="flex items-center gap-2 animate-pulse">
                        PROCESANDO...
                    </span>
                </button>
            </div>
        </div>
    @endslot
</x-ui.modal>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            // Drop zone reutilizable por ítem de baja — recibe el x-ref del input
            Alpine.data('fileDropZoneBaja', (inputRef) => ({
                dragging: false,
                triggerInput() {
                    this.$refs[inputRef].click();
                },
                handleFiles(files) {
                    if (!files || files.length === 0) return;
                    const dt = new DataTransfer();
                    files.forEach(f => dt.items.add(f));
                    this.$refs[inputRef].files = dt.files;
                    this.$refs[inputRef].dispatchEvent(new Event('change'));
                },
                handlePaste(e) {
                    const items = e.clipboardData?.items;
                    if (!items) return;
                    const files = [];
                    for (const item of items) {
                        if (item.kind === 'file' && item.type.startsWith('image/')) {
                            const file = item.getAsFile();
                            if (file) files.push(new File([file], 'pegado-baja-' + Date.now() +
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

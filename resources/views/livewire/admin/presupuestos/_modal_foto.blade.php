{{-- FOTO / COMPROBANTE (PANEL + ZOOM TIPO AMAZON) --}}
@if ($openFotoModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

        {{-- Backdrop --}}
        <button type="button" wire:click="closeFoto"
            class="absolute inset-0 bg-black/40 backdrop-blur-[1px] cursor-pointer" aria-label="Cerrar">
        </button>

        {{-- Panel --}}
        <div
            class="relative w-full max-w-5xl rounded-2xl border border-gray-200 dark:border-neutral-700
                   bg-white dark:bg-neutral-900 shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                        Comprobante adjunto
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Pasa el cursor para ampliar y mover (tipo Amazon)
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($fotoUrl)
                        <a href="{{ $fotoUrl }}" target="_blank" rel="noopener"
                            class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg border
                                   border-gray-200 dark:border-neutral-700
                                   bg-white dark:bg-neutral-900
                                   text-xs font-medium text-gray-700 dark:text-neutral-200
                                   hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 3h7v7" />
                                <path d="M10 14 21 3" />
                                <path d="M21 14v7h-7" />
                                <path d="M3 10v11h11" />
                            </svg>
                            Abrir
                        </a>
                    @endif

                    <button type="button" wire:click="closeFoto"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border
                               border-gray-200 dark:border-neutral-700
                               text-gray-600 dark:text-neutral-300
                               hover:bg-gray-50 dark:hover:bg-neutral-800 transition"
                        aria-label="Cerrar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Body --}}
            <div class="p-5 bg-gray-50/60 dark:bg-neutral-900/40">
                @if ($fotoUrl)
                    <div x-data="{
                        zoom: 1,
                        minZoom: 1,
                        maxZoom: 4,
                        step: 0.2,
                        hovering: false,
                    
                        setOrigin(e) {
                            const rect = e.target.getBoundingClientRect();
                            const x = ((e.clientX - rect.left) / rect.width) * 100;
                            const y = ((e.clientY - rect.top) / rect.height) * 100;
                            e.target.style.transformOrigin = `${x}% ${y}%`;
                        },
                    
                        // ✅ zoom con rueda
                        wheelZoom(e) {
                            // evitar scroll del modal/página mientras haces zoom
                            e.preventDefault();
                    
                            // deltaY > 0 = baja (zoom out) | deltaY < 0 = sube (zoom in)
                            const dir = e.deltaY < 0 ? 1 : -1;
                            const next = this.zoom + (dir * this.step);
                    
                            this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, next));
                    
                            // si ya no hay zoom, apagamos hover
                            if (this.zoom <= 1) this.hovering = false;
                            else this.hovering = true;
                    
                            // mantener el foco en el punto del cursor
                            this.setOrigin(e);
                        }
                    }"
                        class="rounded-2xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">

                        {{-- Área imagen --}}
                        <div
                            class="relative aspect-[4/3] overflow-hidden cursor-crosshair bg-white dark:bg-neutral-900">

                            <img src="{{ $fotoUrl }}" alt="Comprobante"
                                class="absolute inset-0 w-full h-full object-contain select-none
                           transition-transform duration-150 ease-out will-change-transform"
                                :style="hovering
                                    ?
                                    `transform: scale(${zoom});` :
                                    'transform: scale(1); transform-origin:center center;'"
                                @mouseenter="hovering = true" @mouseleave="hovering = false"
                                @mousemove="setOrigin($event)" {{-- ✅ WHEEL ZOOM --}} @wheel.prevent="wheelZoom($event)"
                                draggable="false" />

                            {{-- Botones flotantes --}}
                            <div class="absolute top-4 right-4 flex flex-col gap-2">
                                <button type="button"
                                    class="w-10 h-10 rounded-full bg-white/90 dark:bg-neutral-900/90
                               border border-gray-200 dark:border-neutral-700
                               shadow hover:bg-white dark:hover:bg-neutral-800 transition
                               flex items-center justify-center"
                                    @click="zoom = Math.min(maxZoom, zoom + 0.5); hovering = (zoom > 1)"
                                    title="Acercar">
                                    +
                                </button>

                                <button type="button"
                                    class="w-10 h-10 rounded-full bg-white/90 dark:bg-neutral-900/90
                               border border-gray-200 dark:border-neutral-700
                               shadow hover:bg-white dark:hover:bg-neutral-800 transition
                               flex items-center justify-center"
                                    @click="zoom = Math.max(minZoom, zoom - 0.5); if(zoom<=1){ hovering=false }"
                                    title="Alejar">
                                    –
                                </button>
                            </div>
                        </div>

                        {{-- Barra inferior --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                        px-4 py-3 border-t border-gray-200 dark:border-neutral-700">

                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Hover para zoom • rueda del mouse para acercar/alejar • zoom actual:
                                <span class="font-semibold text-gray-800 dark:text-neutral-200"
                                    x-text="zoom.toFixed(1) + 'x'"></span>
                            </div>

                            <div class="flex items-center gap-2 justify-end">
                                <button type="button"
                                    class="px-3 py-2 rounded-lg border border-gray-200 dark:border-neutral-700
                               text-xs font-medium text-gray-700 dark:text-neutral-200
                               bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800 transition"
                                    @click="zoom = 1; hovering = false">
                                    Reset zoom
                                </button>

                                <button type="button"
                                    class="px-3 py-2 rounded-lg border border-gray-200 dark:border-neutral-700
                               text-xs font-medium text-gray-700 dark:text-neutral-200
                               bg-white dark:bg-neutral-900 hover:bg-gray-50 dark:hover:bg-neutral-800 transition"
                                    @click="zoom = 1; hovering = false">
                                    Normal
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

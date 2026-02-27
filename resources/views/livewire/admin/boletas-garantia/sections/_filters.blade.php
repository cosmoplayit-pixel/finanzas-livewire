{{-- ===================== BUSCADOR + FILTROS ===================== --}}
<div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-visible">

    {{-- MOBILE (<= md): FILTROS COLAPSABLES (MISMO TAMAÑO DE LETRA) --}}
    <div class="md:hidden" x-data="{ openFilters: false }">

        {{-- Header / botón MOBILE --}}
        <div class="px-4 h-11 flex items-center justify-between">
            {{-- Izquierda --}}
            <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200 flex gap-2 items-center">
                Filtros
                <span class="text-[11px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-400">
                    {{ count($f_tipo ?? []) + count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_banco_egreso) ? 1 : 0) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }}
                </span>
            </div>

            {{-- Derecha --}}
            <button type="button" @click="openFilters = !openFilters"
                class="inline-flex items-center gap-1.5
                   px-3 h-8
                   rounded-lg
                   text-[13px] font-semibold
                   border border-gray-200
                   bg-white text-gray-700
                   hover:bg-gray-50
                   dark:border-neutral-700
                   dark:bg-neutral-900
                   dark:text-neutral-100
                   dark:hover:bg-neutral-800/60
                   transition">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 4h-7" />
                    <path d="M10 4H3" />
                    <path d="M21 12h-9" />
                    <path d="M8 12H3" />
                    <path d="M21 20h-5" />
                    <path d="M12 20H3" />
                    <path d="M14 2v4" />
                    <path d="M12 10v4" />
                    <path d="M16 18v4" />
                </svg>

                <span x-text="openFilters ? 'Ocultar' : 'Mostrar'"></span>
            </button>
        </div>

        {{-- Contenido (oculto al inicio) --}}
        <div class="mt-2 space-y-3 px-4 pb-3 text-[13px]" x-show="openFilters" x-collapse x-cloak>

            <div>
                <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">
                    Búsqueda
                </label>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Nro de Boleta…" autocomplete="off"
                    class="w-full rounded-lg border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       text-[13px]
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">
                        Por página
                    </label>
                    <select wire:model.live="perPage"
                        class="w-full rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           text-[13px]
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                
                {{-- Floating Panel for complex filters triggers here in mobile --}}
                <div class="relative flex items-end" x-data="{ secTipo: true, secEstado: true, secDev: true, secFecha: true, secBanco: true, openPanel: false }">
                    <button type="button" @click="openPanel = !openPanel"
                        class="w-full flex items-center justify-center gap-2 h-[38px] rounded-lg border border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14" />
                            <line x1="4" y1="10" x2="4" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12" y2="3" />
                            <line x1="20" y1="21" x2="20" y2="16" />
                            <line x1="20" y1="12" x2="20" y2="3" />
                            <line x1="1" y1="14" x2="7" y2="14" />
                            <line x1="9" y1="8" x2="15" y2="8" />
                            <line x1="17" y1="16" x2="23" y2="16" />
                        </svg>
                        Más Filtros
                    </button>
                    
                    {{-- ====== PANEL FLOTANTE ====== --}}
                    <div x-show="openPanel" x-cloak x-transition.origin.top.right @click.outside="openPanel = false"
                        @keydown.escape.window="openPanel = false"
                        class="absolute right-0 top-full mt-2 w-[300px] z-[60]
                               rounded-xl border border-gray-200 bg-white shadow-xl
                               dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden text-left"
                        wire:ignore.self wire:key="boletas-panel-filtros-mobile">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                            <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
                        </div>
                        <div class="px-4 pb-4 space-y-4 max-h-[60vh] overflow-y-auto">
                            @include('livewire.admin.boletas-garantia.sections._filters_panel')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DESKTOP (>= md): tu layout original --}}
    <div class="hidden md:block p-4">
        <div class="grid grid-cols-1 md:grid-cols-20 md:items-end gap-3">
            <div class="md:col-span-7 lg:col-span-8">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Buscar
                </label>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Nro de Boleta…"
                    class="w-full rounded-lg border px-3 py-2.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                    autocomplete="off" />
            </div>
            
            <div class="md:col-span-2 lg:col-span-8"></div>

            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Por página
                </label>
                <select wire:model.live="perPage"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div class="md:col-span-3 lg:col-span-2 relative" x-data="{ openFiltersDesktop: false }">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200 whitespace-nowrap">
                    Por Filtros
                </label>
                <button type="button" @click="openFiltersDesktop = !openFiltersDesktop"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg
                           bg-black text-white hover:bg-gray-800 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14" />
                        <line x1="4" y1="10" x2="4" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12" y2="3" />
                        <line x1="20" y1="21" x2="20" y2="16" />
                        <line x1="20" y1="12" x2="20" y2="3" />
                        <line x1="1" y1="14" x2="7" y2="14" />
                        <line x1="9" y1="8" x2="15" y2="8" />
                        <line x1="17" y1="16" x2="23" y2="16" />
                    </svg>
                    <span>Filtros</span>
                    <span class="text-xs opacity-80">
                        ({{ count($f_tipo ?? []) + count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_banco_egreso) ? 1 : 0) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }})
                    </span>
                </button>

                {{-- ====== PANEL FLOTANTE DESKTOP ====== --}}
                <div x-show="openFiltersDesktop" x-cloak x-transition.origin.top.right @click.outside="openFiltersDesktop = false"
                    @keydown.escape.window="openFiltersDesktop = false"
                    class="absolute right-0 top-full mt-3 w-[360px] z-50
                           rounded-xl border border-gray-200 bg-white shadow-xl
                           dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
                    wire:ignore.self wire:key="boletas-panel-filtros-desktop">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                        <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>
                    </div>
                    <div class="px-4 pb-4 space-y-4" x-data="{ secTipo: true, secEstado: true, secDev: true, secFecha: true, secBanco: true }">
                        @include('livewire.admin.boletas-garantia.sections._filters_panel')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

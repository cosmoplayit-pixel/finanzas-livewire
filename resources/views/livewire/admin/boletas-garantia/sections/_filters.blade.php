{{-- FILTROS --}}
<div x-data="{ openFilters: false }" class="relative mb-4">

    <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
        {{-- MOBILE (<= md): FILTROS COLAPSABLES --}}
        <div class="md:hidden" x-data="{ openMobile: false }">
            <div class="px-4 h-11 flex items-center justify-between">
                <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200 flex items-center gap-2">
                    Filtros
                    <span
                        class="text-[11px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-400">
                        {{ count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }}
                    </span>
                </div>
                <button type="button" @click="openMobile = !openMobile"
                    class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg text-[13px] font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800/60 transition cursor-pointer">
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
                    <span x-text="openMobile ? 'Ocultar' : 'Mostrar'"></span>
                </button>
            </div>

            <div class="mt-2 space-y-3 px-4 pb-3 text-[13px]" x-show="openMobile" x-collapse x-cloak>
                <div>
                    <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Búsqueda</label>
                    <input type="search" wire:model.live.debounce.300ms="search" name="search_boletas_mobile"
                        id="search_boletas_mobile" placeholder="Nro de Boleta…" autocomplete="one-time-code"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-transparent select-none text-[13px]">&nbsp;</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Avanzados
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DESKTOP (>= md): Layout extendido --}}
        <div class="hidden md:block py-3 px-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6 lg:col-span-8">
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                    <input type="search" wire:model.live.debounce.300ms="search" name="search_boletas"
                        id="search_boletas" placeholder="Nro de Boleta…" autocomplete="one-time-code"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                </div>

                <div class="md:col-span-3 lg:col-span-2">
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Mostrar</label>
                    <select wire:model.live="perPage"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="md:col-span-3 lg:col-span-2">
                    <label
                        class="block text-xs mb-1 text-gray-600 dark:text-neutral-300 flex items-center justify-between">
                        Filtros
                    </label>
                    <button type="button" @click.stop="openFilters = !openFilters"
                        class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Opciones
                        <span
                            class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                            {{ count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ PANEL FLOTANTE --}}
    <div x-show="openFilters" x-cloak @click.outside="openFilters = false" @keydown.escape.window="openFilters = false"
        class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
        wire:ignore.self wire:key="boletas-panel-filtros-avanzados">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
            <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
        </div>

        <div class="px-4 pb-4 space-y-4 pt-3 max-h-[60vh] overflow-y-auto" x-data="{ secTipo: true, secEstado: true, secDev: true, secFecha: true, secBanco: true }">

            {{-- SECCIÓN MONEDA PARA EL SUMMARY --}}
            <div class="border-b border-gray-200 dark:border-neutral-700 pb-3" x-data="{ secMoneda: true }">
                <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                    @click="secMoneda = !secMoneda">
                    <span class="font-semibold text-gray-800 dark:text-neutral-100">Moneda (Resumen)</span>
                    <span class="text-gray-400" x-text="secMoneda ? '▾' : '▸'"></span>
                </button>

                <div x-show="secMoneda" class="mt-3 space-y-3">
                    <select wire:model.live="moneda"
                        class="cursor-pointer w-full rounded-lg border px-3 py-2
                            bg-white text-gray-900 border-gray-300
                            dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                            focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="all">Todas</option>
                        <option value="BOB">BOB</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>

            @include('livewire.admin.boletas-garantia.sections._filters_panel')
        </div>
    </div>
</div>
